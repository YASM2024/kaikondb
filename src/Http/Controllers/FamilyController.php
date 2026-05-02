<?php

namespace Kaikon2\Kaikondb\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

use Kaikon2\Kaikondb\Models\Family;
use Kaikon2\Kaikondb\Models\Species;

class FamilyController extends Controller
{
    //
    public function showMaster(Request $request){
        if(!isset($request->order_id)){ return redirect()->route('top'); }
        $famlies = Family::where('order_id', '=', $request->order_id )
            ->orderBy('code', 'asc')->get();
        return $famlies;
    
    }

    public function downloadMaster(){
            
        $families = Family::all();
        $stream = fopen('php://temp', 'w');
        $csvheader = '"id","code","family_ja","family","order_id"'."\n";
        fwrite($stream, $csvheader);
        
        foreach ($families as $family) {
            $csvdata = array(
                $family->id,
                $family->code,
                $family->family_ja,
                $family->family,
                $family->order_id
            );
            fwrite($stream, "\"" . implode("\",\"", $csvdata) . "\"\n");
        }

        rewind($stream);                      //ファイルポインタを先頭に戻す
        $csv = stream_get_contents($stream);
        $csv = mb_convert_encoding($csv,'UTF-8');

        fclose($stream);

        $headers = array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=families.csv'
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
    
    //
    public function importMaster(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'csv_file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $orderId = (int) $request->input('order_id');

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

        $expectedHeader = ['family', 'family_ja', 'code', 'status', 'delete_flg'];

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
            throw ValidationException::withMessages([
                'csv_file' => 'CSVヘッダが不正です。family,family_ja,code,status,delete_flg の5項目を使用してください。列順は任意です。'
            ]);
        }

        $rows = [];
        $lineNo = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $lineNo++;

            if ($row === [null] || count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

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
            $orderId,
            &$createdCount,
            &$updatedCount,
            &$deletedCount,
            &$skippedCount
        ) {
            foreach ($rows as $row) {
                $lineNo = $row['_line_no'];
                $code = $row['code'];

                $deleteFlg = trim((string) ($row['delete_flg'] ?? ''));
                $deleteFlg = ($deleteFlg === '1') ? 1 : 0;

                if ($code === '') {
                    throw ValidationException::withMessages([
                        'csv_file' => "{$lineNo}行目: code は必須です。"
                    ]);
                }

                // code を全体ユニークとみなす場合
                $family = Family::where('code', $code)->first();

                if ($deleteFlg === 1) {
                    if ($family) {
                        if (Species::where('family_id', $family->id)->exists()) {
                            throw ValidationException::withMessages([
                                'csv_file' => "{$lineNo}行目: {$code}-{$family->family_ja}-{$family->family} は speciesマスタで使用されているため削除できません。"
                            ]);
                        } else {
                            $family->delete();
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

                if ($row['family'] === '') {
                    throw ValidationException::withMessages([
                        'csv_file' => "{$lineNo}行目: family は必須です。"
                    ]);
                }

                if ($row['family_ja'] === '') {
                    throw ValidationException::withMessages([
                        'csv_file' => "{$lineNo}行目: family_ja は必須です。"
                    ]);
                }

                $payload = [
                    'order_id' => $orderId,
                    'family' => $row['family'],
                    'family_ja' => $row['family_ja'],
                    'code' => $code,
                    'status' => $status,
                ];

                if ($family) {
                    $family->fill($payload);
                    $family->save();
                    $updatedCount++;
                } else {
                    Family::create($payload);
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

    public function showEditMaster(){
        return view('kaikon::masters.family');
    }
    
    public function showEditMasterOld(){
        return view('kaikon::masters.familyold');
    }
    
    public function edit( Request $request ){

        $inputs = $request->all();
        $rules = [
            'id' => 'nullable | integer', 
            'family' => 'required | string | max:255', 
            'family_ja' => 'required | string | max:255', 
            'code' => 'required | string | max:6', 
            'order_id' => 'nullable | integer | between:1,50', // 50目まで対応
        ];
      
        $validation = Validator::make($inputs,$rules);
        
        if( $validation->fails()){ return ['result'=>'validation error' ]; }       

        DB::beginTransaction();
        try {
            
            if( isset($request->id) ){
                $family = Family::find($inputs['id']);
                $family->family = $inputs['family'] ?? $family->family;
                $family->family_ja = $inputs['family_ja'] ?? $family->family_ja;
                $family->code = $inputs['code'] ?? $family->code;
                $family->order_id = $inputs['order_id'] ?? $family->order_id;
                $family->status = $inputs['status'] ?? $family->status;
                $family->save();
            }else{

                //insert 非推奨
                Family::create([
                    'order_id' => $inputs['order_id'],
                    'family' => $inputs['family'],
                    'family_ja' => $inputs['family_ja'],
                    'code' => $inputs['code']
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

    public function editStatus( Request $request ){

        $inputs = $request->all();
        $rules = [
            'id' => 'required | integer', 
            'status' => 'required | integer | in:0,1'
        ];
      
        $validation = Validator::make($inputs,$rules);
        
        if( $validation->fails()){ return ['result'=>'validation error' ]; }       

        DB::beginTransaction();
        try {
            
            $family = Family::find($inputs['id']);
            $family->status = $inputs['status'];
            $family->save();
            
            DB::commit();
            
        } catch (\Exception $e) {

            DB::rollback();
            return ['result'=>'error', 'message' => $e->getMessage()];
            // エラーハンドリング
        }

        return ['result'=>'success'];
  
    }
}
