<?php

namespace Kaikon2\Kaikondb\Http\Controllers;

use Kaikon2\Kaikondb\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

use Kaikon2\Kaikondb\Models\Family;

class OrderController extends Controller
{
    //
    public function showMaster(){
        $orders = Order::orderBy('code', 'asc')->get();
        return $orders;
    }

    public function showMasterHaveSpecies(){
        //
        $orders = Order::has('species')->get();
        return $orders;
    }

    public function edit( Request $request ){

        $inputs = $request->all();
        $rules = [
            'id' => 'nullable | integer', 
            'order' => 'required | string | max:255', 
            'order_ja' => 'required | string | max:255', 
            'code' => 'required | string | max:3', 
            'status' => 'required | integer | in:0,1',
        ];
      
        $validation = Validator::make($inputs,$rules);
        
        if( $validation->fails()){ return ['result'=>'validation error' ]; }       

        DB::beginTransaction();
        try {
            
            if( isset($request->id) ){
                $order = Order::find($inputs['id']);
                $order->order = $inputs['order'];
                $order->order_ja = $inputs['order_ja'];
                $order->code = $inputs['code'];
                $order->status = $inputs['status'];
                $order->save();
            }else{

                //insert 非推奨
                Order::create([
                    'order' => $inputs['order'],
                    'order_ja' => $inputs['order_ja'],
                    'code' => $inputs['code'],
                    'status' => $inputs['status']
                ]);
            }
            DB::commit();
            
        } catch (\Exception $e) {

            DB::rollback();
            return ['result'=>'error', 'message' => $e->getMessage()];
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
            
            $order = Order::find($inputs['id']);
            $order->status = $inputs['status'];
            $order->save();
            
            DB::commit();
            
        } catch (\Exception $e) {

            DB::rollback();
            return ['result'=>'error', 'message' => $e->getMessage()];
            // エラーハンドリング
        }

        return ['result'=>'success'];
  
    }

    public function downloadMaster(){
            
        $orders = Order::all();
        $stream = fopen('php://temp', 'w');
        $csvheader = '"id","order_ja","order"'."\n";
        fwrite($stream, $csvheader);
        
        foreach ($orders as $order) {
            $csvdata = array(
                $order->id,
                $order->order_ja,
                $order->order
            );
            fwrite($stream, "\"" . implode("\",\"", $csvdata) . "\"\n");
        }

        rewind($stream);                      //ファイルポインタを先頭に戻す
        $csv = stream_get_contents($stream);
        $csv = mb_convert_encoding($csv,'UTF-8');

        fclose($stream);

        $headers = array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=orders.csv'
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

        $expectedHeader = ['order', 'order_ja', 'code', 'status', 'delete_flg'];

        $normalizedHeader = array_map(function ($value) {
            $value = (string) $value;
            $value = preg_replace('/^\xEF\xBB\xBF/', '', $value);
            $value = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}\x{00A0}]/u', '', $value);
            $value = trim($value);
            return mb_strtolower($value);
        }, $header);

        $sortedHeader = $normalizedHeader;
        $sortedExpected = $expectedHeader;

        sort($sortedHeader);
        sort($sortedExpected);

        if ($sortedHeader !== $sortedExpected) {
            fclose($handle);

            throw ValidationException::withMessages([
                'csv_file' => 'CSVヘッダが不正です。order,order_ja,code,status,delete_flg の5項目を使用してください。列順は任意です。 actual=' . implode(',', $normalizedHeader)
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
            &$createdCount,
            &$updatedCount,
            &$deletedCount,
            &$skippedCount
        ) {
            foreach ($rows as $row) {
                $lineNo = $row['_line_no'];
                $code = $row['code'] ?? '';

                $deleteFlg = trim((string) ($row['delete_flg'] ?? ''));
                $deleteFlg = ($deleteFlg === '1') ? 1 : 0;

                if ($code === '') {
                    throw ValidationException::withMessages([
                        'csv_file' => "{$lineNo}行目: code は必須です。"
                    ]);
                }

                $order = Order::where('code', $code)->first();

                if ($deleteFlg === 1) {
                    if ($order) {
                        if( Family::where('order_id', $order->id)->exists() ){
                            throw ValidationException::withMessages([
                                'csv_file' => "{$lineNo}行目: {$code}-{$order->order_ja}-{$order->order} は familyマスタで使用されているため削除できません。"
                            ]);
                        }else{
                            // 物理削除
                            $order->delete();
                            $deletedCount++;
                        }
                    } else {
                        $skippedCount++;
                    }
                    continue;
                }

                $status = self::normalizeStatusValue($row['status'] ?? null);
                if ($status === null) {
                    throw ValidationException::withMessages([
                        'csv_file' => "{$lineNo}行目: status は 1/0 または 有効/無効 を指定してください。"
                    ]);
                }

                if (($row['order'] ?? '') === '') {
                    throw ValidationException::withMessages([
                        'csv_file' => "{$lineNo}行目: order は必須です。"
                    ]);
                }

                if (($row['order_ja'] ?? '') === '') {
                    throw ValidationException::withMessages([
                        'csv_file' => "{$lineNo}行目: order_ja は必須です。"
                    ]);
                }

                $payload = [
                    'order' => $row['order'] ?? '',
                    'order_ja' => $row['order_ja'] ?? '',
                    'code' => $code,
                    'status' => $status,
                    'random_key' => $order->random_key ?? hash('sha256', uniqid('', true)),
                ];

                if ($order) {
                    $order->fill($payload);
                    $order->save();
                    $updatedCount++;
                } else {
                    Order::create($payload);
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
}
