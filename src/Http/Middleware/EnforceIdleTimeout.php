<?php

namespace Kaikon2\Kaikondb\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforceIdleTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        $timeoutSeconds = (int) config('kaikon.SESSION_IDLE_TIMEOUT_SECONDS', 0);

        // 0 以下は無効化（互換性優先）
        if ($timeoutSeconds <= 0) {
            return $next($request);
        }

        // 認証中のユーザのみ対象（guest は何もしない）
        if (Auth::check()) {
            $now = time();
            $last = (int) $request->session()->get('kaikon.last_activity', $now);

            if (($now - $last) > $timeoutSeconds) {
                // Logout リスナーに「タイムアウト」を伝える
                $request->session()->put('kaikon.logout_status', 'timeout');

                Auth::guard('web')->logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Session timed out.'], 401);
                }

                return redirect()->route('login')
                    ->withErrors(['email' => 'セッションがタイムアウトしました。再ログインしてください。']);
            }

            // アクティビティ更新
            $request->session()->put('kaikon.last_activity', $now);
        }

        return $next($request);
    }
}

