<?php

namespace Kaikon2\Kaikondb\Mail;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Kaikon2\Kaikondb\Support\KaikonLoginTrace;

/**
 * ログイン通知メールを、`kaikon.FEATURES.jobs.email_queue` に応じて queue / 同期で送る。
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
    public static function sendTo(array $recipients, string $loginEmail): void
    {
        if ($recipients === []) {
            return;
        }

        $mailable = new LoginNotificationMail($loginEmail);
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
