<?php

namespace Kaikon2\Kaikondb\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LargeCsvDataController extends Controller
{

    private const MAX_COUNT = 10000; // 取込み最大行数＝10000行

    public function updateLargeFile(Request $request, $table_name, $file_name)
    {

        $file = $request->file($file_name);
        $tmpTableName = 'tmp_' . time();

        // 一時テーブルを作成（必要なカラムをすべて定義）
        DB::statement("CREATE TEMPORARY TABLE $tmpTableName (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(6) NOT NULL,
            family_ja VARCHAR(255) NOT NULL,
            family VARCHAR(255) NOT NULL,
            order_id INT NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        // ファイルパスを取得（例：storage/app/data.csv）
        $filePath = $request->file('family_file')->store('uploads', 'public');
        $fileFullPath = Storage::disk('public')->path($filePath);

        // 行数チェック
        $lineCount = 0;

        if (($handle = fopen($fileFullPath, 'r')) !== false) {
            while (!feof($handle)) {
                fgets($handle);
                $lineCount++;

                if ($lineCount > self::MAX_COUNT) {
                    fclose($handle);
                    // 行数オーバー時の処理
                    throw new \Exception("CSVファイルは最大" . self::MAX_COUNT . "行までです。");
                }
            }
            fclose($handle);
        }


        // ファイルを開いて1行ずつ読み込む
        if (($handle = fopen($fileFullPath, 'r')) !== false) {
            $isFirstLine = true;

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                // ヘッダー行をスキップ
                if ($isFirstLine) {
                    $isFirstLine = false;
                    continue;
                }

                // データを挿入
                DB::table($tmpTableName)->insert([
                    'code' => $data[0] ?? '',
                    'family_ja' => $data[1] ?? '',
                    'family' => $data[2] ?? '',
                    'order_id' => (int)($data[3] ?? 0),
                ]);
            }

            fclose($handle);

        }

        // コードのunique確認
        $codeDuplicationCount = DB::table($tmpTableName)
            ->join('families', "$tmpTableName.code", '=', 'families.code')
            ->count();
        if ($codeDuplicationCount > 0) {
            $duplicatedcodes = DB::table($tmpTableName)
                ->join('families', "$tmpTableName.code", '=', 'families.code')
                ->pluck("$tmpTableName.code");
            $codeList = $duplicatedcodes->implode('; ');
            return new \Exception("このコードはすでに登録されています\n$codeList", 422);
        }

        // order_idの存在確認
        $libraryStatusExistenceCount = DB::table($tmpTableName)
            ->leftJoin('orders', 'orders.id', '=', "$tmpTableName.order_id")
            ->whereNull("orders.id")
            ->count();
        if ($libraryStatusExistenceCount > 0) {
            $invalidOrderIds = DB::table($tmpTableName)
                ->leftJoin('orders', 'orders.id', '=', "$tmpTableName.order_id")
                ->whereNull("orders.id")
                ->pluck("$tmpTableName.order_id");
            $idList = $invalidOrderIds->implode(', ');
            return new \Exception("order_idが存在しません\n$idList", 422);
        }

        // 本テーブルにデータを挿入
        DB::statement(
            "INSERT INTO families
                (code,
                family_ja,
                family,
                order_id
                )
             SELECT
                 {$tmpTableName}.code,
                 {$tmpTableName}.family_ja,
                 {$tmpTableName}.family,
                 {$tmpTableName}.order_id
             FROM {$tmpTableName}"
            );

    }

}