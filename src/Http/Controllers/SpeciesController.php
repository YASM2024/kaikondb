<?php

namespace Kaikon2\Kaikondb\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

use Kaikon2\Kaikondb\Models\Record;
use Kaikon2\Kaikondb\Models\Species;
use Kaikon2\Kaikondb\Models\Family;
use Kaikon2\Kaikondb\Models\Order;

class SpeciesController extends Controller
{
    
    public function showMaster(Request $request)
    {
        
        $keyword = $request->keyword;
        if(isset($request->family_id)){
            $species = Species::where('family_id', '=', $request->family_id)
                ->orderBy('code', 'asc')
                ->get()
                ->map(function ($species) {
                    $species->exists_in_records = Record::where('species_id', $species->id)->exists();
                    return $species;
                });
        }
        if(isset($request->keyword)){
            $species = Species::where('species_ja', 'LIKE', "%{$keyword}%")
                ->orWhere('species', 'LIKE', "%{$keyword}%")
                ->orderBy('code', 'asc')
                ->get()
                ->map(function ($species) {
                    $species->exists_in_records = Record::where('species_id', $species->id)->exists();
                    return $species;
                });
        }
        if(isset($species)){ return $species;}
        return redirect()->route('top');
    }

    public function downloadMaster()
    {
            
        $speciess = Species::all();
        $stream = fopen('php://temp', 'w');
        $csvheader = '"id","species_ja","ginus","species","code","order_id","family_id","random_key","created_at","updated_at"'."\n";
        fwrite($stream, $csvheader);
        
        foreach ($speciess as $species) {
            $csvdata = array(
                $species->id,
                $species->species_ja,
                $species->ginus,
                $species->species,
                $species->code,
                $species->order_id,
                $species->family_id,
                $species->random_key,
                $species->created_at,
                $species->updated_at
            );
            fwrite($stream, "\"" . implode("\",\"", $csvdata) . "\"\n");
        }

        rewind($stream);                      //ファイルポインタを先頭に戻す
        $csv = stream_get_contents($stream);
        $csv = mb_convert_encoding($csv,'UTF-8');

        fclose($stream);

        $headers = array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=speciess.csv'
        );

        return Response::make($csv, 200, $headers);
    }

    private function normalizeStatusValue(string $value): ?int
    {
        $value = trim($value);

        return match ($value) {
            '1', '有効', 'active', 'ACTIVE' => 1,
            '0', '無効', 'inactive', 'INACTIVE' => 0,
            default => null,
        };
    }

    public function importMaster(Request $request): JsonResponse
    {
        $request->validate([
            'family_id' => ['required', 'integer', 'exists:families,id'],
            'csv_file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $familyId = (int) $request->input('family_id');
        $family = Family::findOrFail($familyId);

        // family から order_id を決定
        $orderId = $family->order_id;

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            throw ValidationException::withMessages([
                'csv_file' => 'CSVファイルを開けませんでした。'
            ]);
        }

        $header = fgetcsv($handle);

        if ($header === false) {
            fclose($handle);

            throw ValidationException::withMessages([
                'csv_file' => 'CSVヘッダが読み取れません。'
            ]);
        }

        $header = array_map(function ($value) {
            $value = (string) $value;
            $value = preg_replace('/^\xEF\xBB\xBF/', '', $value); // UTF-8 BOM除去
            return trim($value);
        }, $header);

        $expectedHeader = ['species', 'species_ja', 'code', 'status', 'delete_flg'];

        $normalizedHeader = array_map(function ($value) {
            $value = (string) $value;
            $value = preg_replace('/^\xEF\xBB\xBF/', '', $value);
            return trim(mb_strtolower($value));
        }, $header);

        $sortedHeader = $normalizedHeader;
        $sortedExpected = $expectedHeader;

        sort($sortedHeader);
        sort($sortedExpected);

        if ($sortedHeader !== $sortedExpected) {
            print_r($sortedHeader);
            print_r($sortedExpected);
            throw ValidationException::withMessages([
                'csv_file' => 'CSVヘッダが不正です。species,species_ja,code,status,delete_flg の5項目を使用してください。列順は任意です。'
            ]);
        }

        $rows = [];
        $lineNo = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $lineNo++;

            if ($row === [null] || count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            // $row = array_pad($row, count($expectedHeader), '');
            // $assoc = array_combine($expectedHeader, $row);
            // $assoc = array_map(fn ($v) => trim((string) $v), $assoc);
            $row = array_pad($row, count($normalizedHeader), '');
            $assoc = array_combine($normalizedHeader, $row);
            $assoc = array_map(fn ($v) => trim((string) $v), $assoc);

            $assoc['_line_no'] = $lineNo;

            $rows[] = $assoc;
        }

        fclose($handle);

        $createdCount = 0;
        $updatedCount = 0;
        $deletedCount = 0;
        $skippedCount = 0;

        DB::transaction(function () use (
            $rows,
            $familyId,
            $orderId,
            &$createdCount,
            &$updatedCount,
            &$deletedCount,
            &$skippedCount
        ) {
            foreach ($rows as $row) {
                $lineNo = $row['_line_no'];
                $code = $row['code'];
                $deleteFlg = trim((string)($row['delete_flg'] ?? ''));
                $deleteFlg = ($deleteFlg === '1') ? 1 : 0;

                if ($code === '') {
                    throw ValidationException::withMessages([
                        'csv_file' => "{$lineNo}行目: code は必須です。"
                    ]);
                }

                // code を全体ユニークとみなす場合
                $species = Species::where('code', $code)->first();

                if ($deleteFlg === 1) {
                    if ($species) {
                        if( Record::where('species_id', $species->id)->exists() ){
                            throw ValidationException::withMessages([
                                'csv_file' => "{$lineNo}行目: {$code}-{$species->species_ja}-{$species->species} は 文献で記録されているため削除できません。"
                            ]);
                        }else{
                            // 物理削除
                            $species->delete();
                            $deletedCount++;
                        }
                    } else {
                        $skippedCount++;
                    }
                    continue;
                }

                $status = self::normalizeStatusValue($row['status']);
                if ($status === null) {
                    throw ValidationException::withMessages([
                        'csv_file' => "{$lineNo}行目: status は 1/0 または 有効/無効 を指定してください。"
                    ]);
                }

                if ($row['species'] === '') {
                    throw ValidationException::withMessages([
                        'csv_file' => "{$lineNo}行目: species は必須です。"
                    ]);
                }

                if ($row['species_ja'] === '') {
                    throw ValidationException::withMessages([
                        'csv_file' => "{$lineNo}行目: species_ja は必須です。"
                    ]);
                }

                $payload = [
                    'order_id' => $orderId,
                    'family_id' => $familyId,
                    'ginus' => mb_strstr($row['species'], ' ', true) ?: $row['species'],
                    'species' => $row['species'],
                    'species_ja' => $row['species_ja'],
                    'code' => $code,
                    'status' => (int) $row['status'],
                    'random_key' => $species->random_key ?? hash('sha256', uniqid("", true)),
                ];

                if ($species) {
                    $species->fill($payload);
                    $species->save();
                    $updatedCount++;
                } else {
                    Species::create($payload);
                    $createdCount++;
                }
            }
        });

        return response()->json([
            'message' => 'CSV取込が完了しました。',
            'createdCount' => $createdCount,
            'updatedCount' => $updatedCount,
            'deletedCount' => $deletedCount,
            'skippedCount' => $skippedCount,
        ]);
    }

    public function showEditMaster()
    {
        return view('kaikon::masters.species');
    }

    public function edit( Request $request )
    {

        $inputs = $request->all();
        $rules = [
            'id' => 'nullable | integer', 
            'order_id' => 'required | integer | between:1,50', // 50目まで対応。
            'family_id' => 'required | integer | between:1,9999', 
            'species_ja' => 'required | string | max:255', 
            'species' => 'required | string | max:255', 
            'code' => 'required| string | max:20',
            'status' => 'required | boolean',
        ];
      
        $validation = Validator::make($inputs,$rules);
        
        if( $validation->fails()){ return ['result'=>'validation error']; }

        DB::beginTransaction();
        try {
            
            if( isset($request->id) ){

                $species = Species::find($inputs['id']);
                $species->order_id = $inputs['order_id'] ?? $species->order_id;
                $species->family_id = $inputs['family_id'] ?? $species->family_id;
                $species->ginus = mb_strstr( $inputs['species'], ' ', true);
                $species->species_ja = $inputs['species_ja'] ?? $species->species_ja;
                $species->species = $inputs['species'] ?? $species->species;
                $species->code = $inputs['code'] ?? $species->code;
                $species->status = $inputs['status'] ?? $species->status;
                $species->save();

            }else{

                //insert 非推奨
                Species::create([
                    'order_id' => $inputs['order_id'],
                    'family_id' => $inputs['family_id'],
                    'ginus' => mb_strstr( $inputs['species'], ' ', true),
                    'species_ja' => $inputs['species_ja'],
                    'species' => $inputs['species'],
                    'code' => $inputs['code'],
                    'random_key' => hash('sha256', uniqid("", true)), 
                ]);
            }
            DB::commit();
            
        } catch (\Exception $e) {

            DB::rollback();
            return ['result'=>'error'];
            // エラーハンドリング
        }

        return ['result'=>'success'];
  
    }

    public function editStatus( Request $request )
    {

        $inputs = $request->all();
        $rules = [
            'id' => 'required | integer', 
            'status' => 'required | boolean'
        ];
      
        $validation = Validator::make($inputs,$rules);
        
        if( $validation->fails()){ return ['result'=>'validation error']; }

        DB::beginTransaction();
        try {
            
            $species = Species::find($inputs['id']);
            $species->status = $inputs['status'];
            $species->save();

            DB::commit();
            
        } catch (\Exception $e) {

            DB::rollback();
            return ['result'=>'error'];
            // エラーハンドリング
        }

        return ['result'=>'success'];
  
    }

}
