<?php

namespace Kaikon2\Kaikondb\Http\Controllers;

use Illuminate\View\View;
use Kaikon2\Kaikondb\Support\KaikonLoginTrace;

class SystemStatusController extends Controller
{
    public function show(): View
    {
        $queueDefault = (string) config('queue.default', '');
        $queueDriver = $queueDefault !== ''
            ? (string) config("queue.connections.$queueDefault.driver", '')
            : '';

        $statuses = [
            'jobs' => [
                [
                    'key' => 'kaikon.FEATURES.jobs.email_queue',
                    'name' => 'メール送信（キュー/遅延送信）',
                    'description' => 'メール送信機能を使用する',
                    'enabled' => (int) config('kaikon.FEATURES.jobs.email_queue', 1) === 1,
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

        $loginTraceTail = KaikonLoginTrace::tail(30);

        return view('kaikon::admin.system-status', compact(
            'statuses',
            'queueDefault',
            'queueDriver',
            'loginTraceTail'
        ));
    }
}

