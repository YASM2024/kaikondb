<?php

namespace Kaikon2\Kaikondb\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Kaikon2\Kaikondb\KaikonServiceProvider;

class SystemStatusController extends Controller
{
    public function show(): View
    {
        $queueDefault = (string) config('queue.default', '');
        $queueDriver = $queueDefault !== ''
            ? (string) config("queue.connections.$queueDefault.driver", '')
            : '';

        $disableFunctions = (string) ini_get('disable_functions');
        $disableFunctionsLower = strtolower($disableFunctions);
        $execDisabled = str_contains($disableFunctionsLower, 'exec');
        $popenDisabled = str_contains($disableFunctionsLower, 'popen');
        $procOpenDisabled = str_contains($disableFunctionsLower, 'proc_open');

        $emailQueueConfigured = (int) config('kaikon.FEATURES.jobs.email_queue', 0) === 1;
        $emailQueueWorkerAlive = false;
        $queueWorkerPidPath = storage_path('app/' . KaikonServiceProvider::QUEUE_WORKER_PID_FILE);
        $queueWorkerPid = is_file($queueWorkerPidPath) ? (int) @file_get_contents($queueWorkerPidPath) : 0;

        if ($emailQueueConfigured && $queueDriver === 'sync') {
            $emailQueueWorkerAlive = true;
        } elseif ($emailQueueConfigured && $queueDriver !== 'sync') {
            $emailQueueWorkerAlive = $queueWorkerPid > 0 && $this->isProcessAlive($queueWorkerPid);
        }

        $jobsCount = null;
        $failedJobsCount = null;
        $jobsReservedCount = null;
        try {
            if (DB::getSchemaBuilder()->hasTable('jobs')) {
                $jobsCount = (int) DB::table('jobs')->count();
                $jobsReservedCount = (int) DB::table('jobs')->whereNotNull('reserved_at')->count();
            }
            if (DB::getSchemaBuilder()->hasTable('failed_jobs')) {
                $failedJobsCount = (int) DB::table('failed_jobs')->count();
            }
        } catch (\Throwable) {
            // 画面は落とさない（DB 未接続や権限問題など）
        }

        $statuses = [
            'email_sending' => [
                [
                    'key' => 'kaikon.FEATURES.jobs.email_queue',
                    'name' => '遅延送信',
                    'description' => 'キュー経由で送信',
                    'enabled' => $emailQueueConfigured,
                ],
            ],
            'backup' => [],
            'jobs' => [
                [
                    'key' => 'kaikon.queue.email_worker',
                    'name' => 'メール送信ワーカー',
                    'description' => $emailQueueConfigured
                        ? ($queueDriver === 'sync'
                            ? '遅延送信ON（driver=sync のためワーカー不要・常に稼働扱い）'
                            : '遅延送信ON（`php artisan kaikon:queue-work` が動いていれば ON）')
                        : '対象外',
                    'enabled' => $emailQueueConfigured && $emailQueueWorkerAlive,
                    'dimmed' => ! $emailQueueConfigured,
                ],
                [
                    'key' => 'kaikon.FEATURES.jobs.article_records_completion',
                    'name' => '文献検索のレコード補完',
                    'description' => '文献検索のレコードを補完する',
                    'enabled' => (int) config('kaikon.LITERATURES', 0) === 1
                        && (int) config('kaikon.FEATURES.jobs.article_records_completion', 1) === 1,
                ],
            ],
            'listeners' => [
                [
                    'key' => 'kaikon.FEATURES.listeners.log_failed_login',
                    'name' => 'LogFailedLogin',
                    'description' => 'ログイン失敗を記録する',
                    'enabled' => (int) config('kaikon.FEATURES.listeners.log_failed_login', 1) === 1,
                ],
                [
                    'key' => 'kaikon.FEATURES.listeners.log_user_login',
                    'name' => 'LogUserLogin',
                    'description' => 'ログイン成功を記録する',
                    'enabled' => (int) config('kaikon.FEATURES.listeners.log_user_login', 1) === 1,
                ],
                [
                    'key' => 'kaikon.FEATURES.listeners.log_user_logout',
                    'name' => 'LogUserLogout',
                    'description' => 'ログアウトを記録する',
                    'enabled' => (int) config('kaikon.FEATURES.listeners.log_user_logout', 1) === 1,
                ],
            ],
        ];

        return view('kaikon::admin.system-status', compact(
            'statuses',
            'queueDefault',
            'queueDriver',
            'emailQueueConfigured',
            'emailQueueWorkerAlive',
            'queueWorkerPidPath',
            'queueWorkerPid',
            'jobsCount',
            'jobsReservedCount',
            'failedJobsCount',
            'disableFunctions',
            'execDisabled',
            'popenDisabled',
            'procOpenDisabled',
        ));
    }

    public function startQueueWorker(): RedirectResponse
    {
        $queueDefault = (string) config('queue.default', '');
        $queueDriver = $queueDefault !== ''
            ? (string) config("queue.connections.$queueDefault.driver", '')
            : '';

        if ((int) config('kaikon.FEATURES.jobs.email_queue', 0) !== 1) {
            return back()->with('status', 'queue-worker-not-started');
        }
        if ($queueDriver === 'sync') {
            return back()->with('status', 'queue-worker-not-required');
        }

        $pidPath = storage_path('app/' . KaikonServiceProvider::QUEUE_WORKER_PID_FILE);
        $pid = is_file($pidPath) ? (int) @file_get_contents($pidPath) : 0;
        if ($pid > 0 && $this->isProcessAlive($pid)) {
            return back()->with('status', 'queue-worker-already-running');
        }

        $artisanPath = base_path('artisan');
        if (! is_string($artisanPath) || ! is_file($artisanPath)) {
            return back()->with('status', 'queue-worker-artisan-missing');
        }

        $php = defined('PHP_BINARY') ? PHP_BINARY : 'php';
        $args = [
            $php,
            $artisanPath,
            'kaikon:queue-work',
            '--stop-when-empty',
            '--sleep=1',
            '--tries=3',
        ];

        if (PHP_OS_FAMILY === 'Windows') {
            $cmd = 'cmd /c start "" /B ' . implode(' ', array_map('escapeshellarg', $args));
            @pclose(@popen($cmd, 'r'));
        } else {
            $cmd = implode(' ', array_map('escapeshellarg', $args)) . ' > /dev/null 2>&1 &';
            @exec($cmd);
        }

        return back()->with('status', 'queue-worker-drain-started');
    }

    public function stopQueueWorker(): RedirectResponse
    {
        $queueDefault = (string) config('queue.default', '');
        $queueDriver = $queueDefault !== ''
            ? (string) config("queue.connections.$queueDefault.driver", '')
            : '';

        if ($queueDriver === 'sync') {
            return back()->with('status', 'queue-worker-not-required');
        }

        try {
            Artisan::call('queue:restart');
        } catch (\Throwable) {
            return back()->with('status', 'queue-worker-stop-failed');
        }

        return back()->with('status', 'queue-worker-stop-signaled');
    }

    public function drainQueueNow(): RedirectResponse
    {
        $queueDefault = (string) config('queue.default', '');
        $queueDriver = $queueDefault !== ''
            ? (string) config("queue.connections.$queueDefault.driver", '')
            : '';

        if ((int) config('kaikon.FEATURES.jobs.email_queue', 0) !== 1) {
            return back()->with('status', 'queue-drain-not-enabled');
        }
        if ($queueDriver === 'sync') {
            return back()->with('status', 'queue-drain-not-required');
        }

        $processed = 0;
        $start = microtime(true);
        $maxSeconds = 20.0;
        $maxJobs = 50;

        while ($processed < $maxJobs && (microtime(true) - $start) < $maxSeconds) {
            $before = null;
            try {
                $before = DB::getSchemaBuilder()->hasTable('jobs') ? (int) DB::table('jobs')->count() : null;
            } catch (\Throwable) {
                $before = null;
            }

            if ($before === null) {
                return back()->with('status', '滞留ジョブ数を確認できませんでした。');
            }

            if ($before === 0) {
                break;
            }

            try {
                Artisan::call('kaikon:queue-work', [
                    '--once' => true,
                    '--sleep' => 0,
                    '--tries' => 3,
                ]);
            } catch (\Throwable $e) {
                Log::error('[kaikondb] queue drain failed', [
                    'queue_default' => $queueDefault,
                    'queue_driver' => $queueDriver,
                    'email_queue' => (int) config('kaikon.FEATURES.jobs.email_queue', 0),
                    'processed' => $processed,
                    'jobs_before' => $before,
                    'exception' => $e,
                ]);
                return back()->with('status', 'queue-drain-failed');
            }

            $after = $before;
            try {
                $after = (int) DB::table('jobs')->count();
            } catch (\Throwable) {
                return back()->with('status', '滞留ジョブ数を確認できませんでした。');
            }

            $delta = $before - $after;
            if ($delta <= 0) {
                break;
            }

            $processed += $delta;
        }

        if ($processed === 0) {
            return back()->with('status', 'メールはありませんでした');
        }

        return back()->with('status', sprintf('キューのメールを送信しました：%d件', $processed));
    }

    private function isProcessAlive(int $pid): bool
    {
        if ($pid <= 0) {
            return false;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $out = [];
            $code = 1;
            @exec('tasklist /FI "PID eq ' . (int) $pid . '" /NH', $out, $code);
            $text = implode("\n", $out);

            return $code === 0 && $text !== '' && stripos($text, (string) $pid) !== false && stripos($text, 'No tasks') === false;
        }

        if (function_exists('posix_kill')) {
            return @posix_kill($pid, 0);
        }

        $out = [];
        $code = 1;
        @exec('ps -p ' . (int) $pid . ' -o pid=', $out, $code);

        return $code === 0 && count($out) > 0;
    }
}
