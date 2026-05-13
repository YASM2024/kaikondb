<?php

namespace Kaikon2\Kaikondb\Mail;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Kaikon2\Kaikondb\Support\KaikonLoginNotificationContext;
use Kaikon2\Kaikondb\Support\KaikonLoginTrace;

/**
 * ログイン通知メールを、`kaikon.FEATURES.jobs.email_queue` に応じて queue / 同期で送る。
 *
 * ペイロードは {@see KaikonLoginNotificationContext}（リクエスト scoped）から取得する。
 *
 * Mailable に {@see \Illuminate\Contracts\Queue\ShouldQueue} を付けないこと。
 * 付与していると Laravel の Mailer が {@see Mail::send} でも常にキュー投入し、
 * 「即時送信」分岐が効かなくなる（明示的な {@see Mail::queue} との二段構えが崩れる）。
 */
final class LoginNotificationMailer
{
    /**
     * @param  array<int, string>  $recipients
     */
    public static function sendTo(array $recipients): void
    {
        if ($recipients === []) {
            return;
        }

        $ctx = app(KaikonLoginNotificationContext::class)->pull();
        if ($ctx === null) {
            Log::error('[kaikondb] login notification skipped: missing LogUserLogin context', [
                'recipients' => $recipients,
            ]);
            KaikonLoginTrace::append('login.notification.skip_no_context', ['recipients' => $recipients]);

            return;
        }

        $email = (string) ($ctx['email'] ?? '');
        if ($email === '') {
            Log::error('[kaikondb] login notification skipped: empty email in context', [
                'recipients' => $recipients,
            ]);
            KaikonLoginTrace::append('login.notification.skip_empty_context_email', ['recipients' => $recipients]);

            return;
        }

        $tz = (string) config('app.timezone', 'UTC');
        $loginDatetime = $ctx['login_at']->timezone($tz)->format('Y-m-d H:i:s T');
        $ip = (string) ($ctx['ip_address'] ?? '');
        $ua = (string) ($ctx['user_agent'] ?? '');
        $userId = $ctx['user_id'];
        $userIdStr = $userId !== null && $userId !== '' ? (string) $userId : '—';

        $mailable = new LoginNotificationMail($email, $loginDatetime, $ip, $ua, $userIdStr);
        $useQueue = (int) config('kaikon.FEATURES.jobs.email_queue', 0) === 1;

        try {
            if ($useQueue) {
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
}
