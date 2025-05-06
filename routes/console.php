<?php

use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

use Kaikon2\Kaikondb\Console\Commands\KaikonInit;
use Kaikon2\Kaikondb\Jobs\UpdateArticleTitlesAndAuthors;

// Inspireコマンド
Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// スケジュールタスク
// 記事のタイトルと著者を更新するジョブを毎日23:40に実行
Schedule::call(function () {
    UpdateArticleTitlesAndAuthors::dispatch();
})->dailyAt('23:40');

// KaikonInitコマンド
Artisan::command('kaikon:init', function () {
    $command = new KaikonInit();
    $command->setOutput($this->output);
    $command->handle();
})->describe('Initializing the system.');