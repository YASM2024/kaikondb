<?php

namespace Kaikon2\Kaikondb\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
//use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

use Kaikon2\Kaikondb\Models\Municipality;
use Kaikon2\Kaikondb\Models\Record;

class MunicipalityController extends Controller
{
    protected static $rules = [
        'municipality_code' => 'required | string | max:20', 
        'municipality_ja' => 'required | string | max:20', 
        'municipality_en' => 'required| string | max:20',
        'status' => 'required | integer | in:0,1',
    ];
    
    
    public function showMaster()
    {
        // 画面側がJSで一覧を取得する前提（Journalマスタと同じ）だが、
        // 互換のためにデータを渡しても問題ないよう残している。
        $municipalities = Municipality::orderBy('municipality_code')->get();
        return view('kaikon::masters.municipalities', ['municipalities' => $municipalities]);
    }

    public function all()
    {
        return Municipality::orderBy('municipality_code')->get();
    }

    public function show($id)
    {   
        $municipality = Municipality::where('id', '=', $id)->firstOrFail();
        return $municipality;
    }
    
    public function edit($id, Request $request)
    {   
        $inputs = $request->all();
        $validation = Validator::make($inputs, self::$rules);
        if ($validation->fails()) {
            return response()->json([
                'result' => 'validation_error',
                'errors' => $validation->errors(),
            ], 422);
        }

        $municipality = Municipality::where('id', '=', $id)->firstOrFail();
        $municipality->municipality_code = $inputs['municipality_code'];
        $municipality->municipality_ja = $inputs['municipality_ja'];
        $municipality->municipality_en = $inputs['municipality_en'];
        $municipality->status = (int) $inputs['status'];
        $municipality->save();

        return ['result' => 'ok'];
    }
    
    public function create(Request $request){   
        $inputs = $request->all();
        $validation = Validator::make($inputs, self::$rules);
        if ($validation->fails()) {
            return response()->json([
                'result' => 'validation_error',
                'errors' => $validation->errors(),
            ], 422);
        }

        //insert 非推奨
        Municipality::create([
            'municipality_code' => $inputs['municipality_code'],
            'municipality_ja' => $inputs['municipality_ja'],
            'municipality_en' => $inputs['municipality_en'],
            'status' => (int) $inputs['status'],
        ]);

        return ['result' => 'ok'];
    }

    public function editStatus(Request $request)
    {
        $inputs = $request->all();
        $rules = [
            'id' => 'required',
            'status' => 'required | integer | in:0,1',
        ];

        $validation = Validator::make($inputs, $rules);
        if ($validation->fails()) {
            return response()->json([
                'result' => 'validation_error',
                'errors' => $validation->errors(),
            ], 422);
        }

        $municipality = Municipality::find($inputs['id']);
        if (!$municipality) {
            return ['result' => 'error', 'message' => 'not found'];
        }

        $municipality->status = (int) $inputs['status'];
        $municipality->save();

        return ['result' => 'ok'];
    }

    
    public function delete($id)
    {
        if (!$this->isDeletable($id)) { return ['res' => 1]; }// 削除不可
        $municipality = Municipality::find($id);
        if (!$municipality) { return ['res' => 1]; }// 該当なし
        try {
            $municipality->delete();
            return ['res' => 0];
        } catch (\Exception $e) {
            return ['res' => 1];// その他のエラー
        }
    }

    
    public function isDeletable($id): bool
    {
        return !Record::where('municipality_id', $id)->exists();
    }
    
    public function screeningDelete($id)
    {
        return response()->json(['deletable' => $this->isDeletable($id)]);
    }
    

    public function downloadMaster(){
            
        $municipalities = Municipality::all();
        $stream = fopen('php://temp', 'w');
        $csvheader = '"id","municipality_code","municipality_ja","municipality_en","status"'."\n";
        fwrite($stream, $csvheader);
        
        foreach ($municipalities as $municipality) {
            $csvdata = array(
                $municipality->id,
                $municipality->municipality_code,
                $municipality->municipality_ja,
                $municipality->municipality_en,
                $municipality->status ?? 1
            );
            fwrite($stream, "\"" . implode("\",\"", $csvdata) . "\"\n");
        }

        rewind($stream);                      //ファイルポインタを先頭に戻す
        $csv = stream_get_contents($stream);
        $csv = mb_convert_encoding($csv,'UTF-8');

        fclose($stream);

        $headers = array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=municipalities.csv'
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
            'csv_file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

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

        $normalizedHeader = array_map(function ($value) {
            $value = (string) $value;
            $value = preg_replace('/^\xEF\xBB\xBF/', '', $value);
            $value = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}\x{00A0}]/u', '', $value);
            $value = trim($value);
            return mb_strtolower($value);
        }, $header);

        // 削除はしない運用のため delete_flg=1 は status=0 として扱う
        $expectedHeader = ['municipality_code', 'municipality_ja', 'municipality_en', 'status', 'delete_flg'];

        $sortedHeader = $normalizedHeader;
        $sortedExpected = $expectedHeader;
        sort($sortedHeader);
        sort($sortedExpected);

        if ($sortedHeader !== $sortedExpected) {
            fclose($handle);
            throw ValidationException::withMessages([
                'csv_file' => 'CSVヘッダが不正です。municipality_code,municipality_ja,municipality_en,status,delete_flg の5項目を使用してください。列順は任意です。 actual=' . implode(',', $normalizedHeader)
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
        $disabledCount = 0;
        $skippedCount = 0;

        DB::transaction(function () use (
            $rows,
            &$createdCount,
            &$updatedCount,
            &$disabledCount,
            &$skippedCount
        ) {
            foreach ($rows as $row) {
                $lineNo = $row['_line_no'];
                $code = trim((string) ($row['municipality_code'] ?? ''));

                if ($code === '') {
                    throw ValidationException::withMessages([
                        'csv_file' => "{$lineNo}行目: municipality_code は必須です。"
                    ]);
                }

                $deleteFlg = trim((string) ($row['delete_flg'] ?? ''));
                $deleteFlg = ($deleteFlg === '1') ? 1 : 0;

                $municipality = Municipality::where('municipality_code', $code)->first();

                $status = $this->normalizeStatusValue((string) ($row['status'] ?? ''));
                if ($status === null) {
                    throw ValidationException::withMessages([
                        'csv_file' => "{$lineNo}行目: status は 1/0 または 有効/無効 を指定してください。"
                    ]);
                }

                // delete_flg=1 は無効化（status=0）
                if ($deleteFlg === 1) {
                    $status = 0;
                }

                if (($row['municipality_ja'] ?? '') === '') {
                    throw ValidationException::withMessages([
                        'csv_file' => "{$lineNo}行目: municipality_ja は必須です。"
                    ]);
                }

                if (($row['municipality_en'] ?? '') === '') {
                    throw ValidationException::withMessages([
                        'csv_file' => "{$lineNo}行目: municipality_en は必須です。"
                    ]);
                }

                $payload = [
                    'municipality_code' => $code,
                    'municipality_ja' => $row['municipality_ja'] ?? '',
                    'municipality_en' => $row['municipality_en'] ?? '',
                    'status' => $status,
                ];

                if ($municipality) {
                    $municipality->fill($payload);
                    $municipality->save();
                    if ($status === 0) $disabledCount++;
                    else $updatedCount++;
                } else {
                    Municipality::create($payload);
                    if ($status === 0) $disabledCount++;
                    else $createdCount++;
                }
            }
        });

        return response()->json([
            'message' => 'CSV取込が完了しました。',
            'createdCount' => $createdCount,
            'updatedCount' => $updatedCount,
            'disabledCount' => $disabledCount,
            'skippedCount' => $skippedCount,
        ]);
    }
}
