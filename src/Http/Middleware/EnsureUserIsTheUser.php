<?php

namespace Kaikon2\Kaikondb\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

use Kaikon2\Kaikondb\Models\User;

class EnsureUserIsTheUser
{
    /**
     * 受信リクエストの処理
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || !User::fromAppUser(Auth::user())->roles->contains('name', 'User')) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }

}

