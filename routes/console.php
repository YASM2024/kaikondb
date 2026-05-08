<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

use Kaikon2\Kaikondb\Console\Commands\KaikonInit;
use Kaikon2\Kaikondb\Jobs\UpdateArticleTitlesAndAuthors;

// スケジュールタスク
// 記事のタイトルと著者を更新するジョブを毎日23:40に実行
Schedule::call(function () {
    if ((int) config('kaikon.LITERATURES', 0) === 1 && (int) config('kaikon.FEATURES.jobs.article_records_completion', 1) === 1) {
        UpdateArticleTitlesAndAuthors::dispatch();
    }
})->dailyAt('23:40');

// KaikonInitコマンド
Artisan::command('kaikon:init
    {--no-queue-worker : email_queue=1 のときでも queue worker を起動しない}
', function () {
    $command = new KaikonInit();
    $command->setOutput($this->output);
    $command->handle();

    // メールがキュー送信設定の場合のみ、初期設定用に worker を回す（ジョブが空になったら終了）
    if ((int) config('kaikon.FEATURES.jobs.email_queue', 0) !== 1) {
        return;
    }
    if ((bool) $this->option('no-queue-worker') === true) {
        return;
    }

    $artisanPath = base_path('artisan');
    if (!is_string($artisanPath) || !is_file($artisanPath)) {
        $this->warn('WARN: artisan が見つからないため、queue worker を起動できませんでした。');
        return;
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
        $this->info('INFO: queue worker をバックグラウンド起動しました（ジョブが空になったら終了します）。');
        return;
    }

    $cmd = implode(' ', array_map('escapeshellarg', $args)) . ' > /dev/null 2>&1 &';
    @exec($cmd);
    $this->info('INFO: queue worker をバックグラウンド起動しました（ジョブが空になったら終了します）。');
})->describe('システムを初期化します。');
