<?php

namespace Kaikon2\Kaikondb\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class LogUserLogout
{
    public function handle(Logout $event): void
    {
        $user = $event->user;
        $status = session()->pull('kaikon.logout_status', 'logout');

        DB::table('user_login_logs')->insert([
            'user_id' => $user?->getAuthIdentifier(),
            'email' => $user?->email ?? null,
            'logout_at' => now(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::header('User-Agent'),
            'status' => is_string($status) && $status !== '' ? $status : 'logout',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

