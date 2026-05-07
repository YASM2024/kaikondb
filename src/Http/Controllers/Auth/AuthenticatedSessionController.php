<?php

namespace Kaikon2\Kaikondb\Http\Controllers\Auth;

use Kaikon2\Kaikondb\Http\Controllers\Controller;
use Kaikon2\Kaikondb\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

use Kaikon2\Kaikondb\Mail\LoginNotificationMail;
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
        $email = $request->input('email');

        // ログイン通知メール（可能ならキュー送信）
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
            $mailable = new LoginNotificationMail((string) $email);
            try {
                if ((int) config('kaikon.FEATURES.jobs.email_queue', 1) === 1) {
                    Mail::to($recipients)->queue($mailable);
                    KaikonLoginTrace::append('login.notification.queued', ['recipients' => $recipients]);
                    Log::warning('[kaikondb] login notification queued', ['recipients' => $recipients]);
                } else {
                    Mail::to($recipients)->send($mailable);
                    KaikonLoginTrace::append('login.notification.sent_sync', ['recipients' => $recipients]);
                    Log::warning('[kaikondb] login notification sent (sync)', ['recipients' => $recipients]);
                }
            } catch (\Throwable $e) {
                KaikonLoginTrace::append('login.notification.failed', [
                    'recipients' => $recipients,
                    'message' => $e->getMessage(),
                ]);
                Log::error('Login notification failed.', [
                    'recipients' => $recipients,
                    'message' => $e->getMessage(),
                    'exception' => $e,
                ]);
            }
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
