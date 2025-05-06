<?php

namespace Kaikon2\Kaikondb\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;

class BackupController extends Controller
{

    // protected static $xxxBackupLogPath = 'app/private/admin/logs/xx_report.txt';
    protected static $dbBackupLogPath = 'admin/logs/backup_report.txt';
    protected static $dbBackupPath = 'admin/backup/data';

    //
    public function showBackupStatus()
    {
        // ファイル読み込み(絶対パス)
        $filePath = Storage::path(self::$dbBackupLogPath);
        
        if (!file_exists($filePath)) {
            return view('kaikon::backup', ['logs' => []]); // ファイルがない場合は空のデータを渡す
        }
    
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $data = [];
    
        foreach ($lines as $line) {
            if (preg_match('/^(\d{4}\/\d{2}\/\d{2}) (\d{2}:\d{2}:\d{2}) \[(Success|Failed)\] (.+)$/', $line, $matches)) {
                $date = $matches[1];
                $time = $matches[2];
                $status = $matches[3];
                $file = $status == 'Success' ? $matches[4] : '';
                $message = $status == 'Success' ? '--' : $matches[4];

                // 各日付の最新情報を上書き
                $data[$date] = [
                    'time' => $time,
                    'status' => $status,
                    'file' => $file,
                ];
                $data[$date]['messages'][] = $message;

            } else {
                // ログフォーマット以外（エラーメッセージなど）は messages に格納
                $dates = array_keys($data);
                $date = !empty($dates) ? end($dates) : null;
                if ($date) {
                    $data[$date]['messages'][] = $line; // 複数行のメッセージを保持
                }
            }
        }
        // 最新5日間を取得
        $latestDates = array_slice(array_keys($data), -5, 5, true);
    
        // 必要なデータだけビューへ送る
        $logs = [];
        foreach ($latestDates as $date) {
            $logs[] = [
                'date' => $date,
                'time' => $data[$date]['time'] ?? 'なし',
                'status' => $data[$date]['status'] ?? 'なし',
                'file' => $data[$date]['file'] ?? 'なし',
                'message' => !empty($data[$date]['messages']) ? implode('<br>', $data[$date]['messages']) : 'なし',
            ];
        }
    
        return view('kaikon::backup', compact('logs'));
    }

    public function download($file)
    {
        $filePath = self::$dbBackupPath . '/' . $file; // 相対パスのまま
        if (!Storage::exists($filePath)) { abort(404); }
        if (Storage::mimeType($filePath) !== 'application/gzip') { abort(404); }

        $headers = ['Content-Type' => 'application/gzip'];
        return Storage::download($filePath, $file, $headers);
    }
    
    
}
