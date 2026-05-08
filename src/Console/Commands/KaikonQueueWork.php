<?php

namespace Kaikon2\Kaikondb\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Kaikon2\Kaikondb\KaikonServiceProvider;

class KaikonQueueWork extends Command
{
    protected $signature = 'kaikon:queue-work
        {--stop-when-empty : キューが空になったら終了}
        {--once : ジョブを1件だけ処理して終了}
        {--sleep=1 : ジョブが無いときの待機秒数}
        {--tries=3 : 失敗時のリトライ回数}
        {--queue= : queue 名（未指定なら設定に従う）}
    ';

    protected $description = 'Kaikon 用ラッパー（内部で queue:work を実行し、PID ファイルで生存確認可能にする）';

    public function handle(): int
    {
        $pidPath = storage_path('app/' . KaikonServiceProvider::QUEUE_WORKER_PID_FILE);
        $dir = \dirname($pidPath);
        if (! is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        @file_put_contents($pidPath, (string) getmypid(), LOCK_EX);

        try {
            $params = [
                '--sleep' => (int) $this->option('sleep'),
                '--tries' => (int) $this->option('tries'),
            ];
            if ((bool) $this->option('stop-when-empty') === true) {
                $params['--stop-when-empty'] = true;
            }
            if ((bool) $this->option('once') === true) {
                $params['--once'] = true;
            }
            $queue = $this->option('queue');
            if (is_string($queue) && trim($queue) !== '') {
                $params['--queue'] = $queue;
            }

            return (int) Artisan::call('queue:work', $params, $this->output);
        } finally {
            @unlink($pidPath);
        }
    }
}
