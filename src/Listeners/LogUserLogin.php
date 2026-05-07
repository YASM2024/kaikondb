<?php

namespace Kaikon2\Kaikondb\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class LogUserLogin
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        DB::table('user_login_logs')->insert([
            'user_id' => $user?->getAuthIdentifier(),
            'email' => $user?->email ?? null,
            'login_at' => now(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::header('User-Agent'),
            'status' => 'success',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

