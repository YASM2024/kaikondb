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
Artisan::command('kaikon:init', function () {
    $command = new KaikonInit();
    $command->setOutput($this->output);
    $command->handle();
})->describe('システムを初期化します。');
