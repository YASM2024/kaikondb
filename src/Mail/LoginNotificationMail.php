<?php

namespace Kaikon2\Kaikondb\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * キュー／同期の切替は {@see LoginNotificationMailer} が担当する。
 * 本クラスに ShouldQueue を付けないこと（理由は LoginNotificationMailer の説明を参照）。
 * 本文用データは {@see KaikonLoginNotificationContext} 経由で Mailer に渡される。
 */
class LoginNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $email,
        public readonly string $loginDatetime,
        public readonly string $ipAddress,
        public readonly string $userAgent,
        public readonly string $userId,
    ) {
    }

    public function build(): self
    {
        return $this
            ->subject('kai-kon: ログイン通知')
            ->view('kaikon::emails.login', [
                'email' => $this->email,
                'login_datetime' => $this->loginDatetime,
                'ip_address' => $this->ipAddress,
                'user_agent' => $this->userAgent,
                'user_id' => $this->userId,
            ]);
    }
}

