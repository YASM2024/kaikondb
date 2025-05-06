<?php

namespace Kaikon2\Kaikondb\Listeners;

use Kaikon2\Kaikondb\Events\UserLoggedOut;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class LogUserLogout
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
    public function handle(UserLoggedOut $event): void
    {
        //
        $user = $event->user;
        $email = $event->email;

        $userAgent = Request::header('User-Agent');
        $ipAddress = Request::ip();

        DB::table('user_login_logs')->insert([
            'user_id' => $user->id,
            'email' => $email,
            'logout_at' => now(),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
