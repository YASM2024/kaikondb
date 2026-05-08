<?php

namespace Kaikon2\Kaikondb\Http\Controllers\Auth;

use Kaikon2\Kaikondb\Http\Controllers\Controller;
use Kaikon2\Kaikondb\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

use Kaikon2\Kaikondb\Mail\LoginNotificationMailer;
use Kaikon2\Kaikondb\Support\KaikonLoginTrace;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('kaikon::auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // ログイン POST がこのコントローラに届いたかどうか（LOG_LEVEL で info が捨てられる環境向けに専用ファイル + warning）
        $emailInput = (string) $request->input('email', '');
        $ctx = ['email' => $emailInput, 'queue_default' => config('queue.default')];
        KaikonLoginTrace::append('login.store.enter', $ctx);
        Log::warning('[kaikondb] login store reached', $ctx);
        $request->authenticate();
        KaikonLoginTrace::append('login.store.after_auth');
        $email = $request->input('email');

        // ログイン通知メール（kaikon.FEATURES.jobs.email_queue でキュー/同期を切替）
        $adminEmail = config('kaikon.Email');
        $recipients = array_values(array_unique(array_filter([
            is_string($adminEmail) ? $adminEmail : null,
            is_string($email) ? $email : null,
        ], fn ($v) => is_string($v) && $v !== '')));

        if (count($recipients) === 0) {
            $skipCtx = ['kaikon.Email' => $adminEmail, 'login_email' => $email];
            KaikonLoginTrace::append('login.notification.skip_no_recipients', $skipCtx);
            Log::warning('[kaikondb] login notification skipped: no recipients', $skipCtx);
        }

        if (count($recipients) > 0) {
            LoginNotificationMailer::sendTo($recipients, (string) $email);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
