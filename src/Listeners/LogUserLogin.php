<?php

namespace Kaikon2\Kaikondb\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Kaikon2\Kaikondb\Support\KaikonLoginNotificationContext;

class LogUserLogin
{
    public function handle(Login $event): void
    {
        $user = $event->user;
        $loginAt = now();
        $ipAddress = Request::ip();
        $userAgent = Request::header('User-Agent');
        $email = $user?->email ?? null;
        $userId = $user?->getAuthIdentifier();

        app(KaikonLoginNotificationContext::class)->store([
            'email' => $email,
            'login_at' => $loginAt,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'user_id' => $userId,
        ]);

        if ((int) config('kaikon.FEATURES.listeners.log_user_login', 1) !== 1) {
            return;
        }

        DB::table('user_login_logs')->insert([
            'user_id' => $userId,
            'email' => $email,
            'login_at' => $loginAt,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'status' => 'success',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

