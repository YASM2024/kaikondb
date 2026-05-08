<?php

namespace Kaikon2\Kaikondb\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

use Kaikon2\Kaikondb\Models\User;

class EnsureUserIsDeveloper
{
    /**
     * 受信リクエストの処理
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            abort(403, 'Unauthorized');
        }

        $roles = User::fromAppUser(Auth::user())->roles;
        $isDeveloper = $roles->contains('name', 'Developer');
        $isAdministrator = $roles->contains('name', 'Administrator');

        if (!$isDeveloper && !$isAdministrator) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }

}

