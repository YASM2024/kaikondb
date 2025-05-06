<?php

namespace Kaikon2\Kaikondb\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use Illuminate\Auth\Events\Failed;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Kaikon2\Kaikondb\Models\User;

class LogFailedLogin
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Failed $event): void
    {
        // ログイン試行の失敗情報を取得
        $credentials = $event->credentials;
        $username = $credentials['email'];

        $userAgent = Request::header('User-Agent');
        $ipAddress = Request::ip();

        // 既存ユーザーの場合はユーザーIDを取得、それ以外はnull
        $user = User::where('email', $username)->first();
        $userId = $user ? $user->id : null;

        DB::table('user_login_logs')->insert([
            'user_id' => $userId,
            'email' => $username,
            'login_at' => now(),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'status' => 'failed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
