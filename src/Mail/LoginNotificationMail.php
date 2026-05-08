<?php

namespace Kaikon2\Kaikondb\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * キュー／同期の切替は {@see LoginNotificationMailer} が担当する。
 * 本クラスに ShouldQueue を付けないこと（理由は LoginNotificationMailer の説明を参照）。
 */
class LoginNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $email
    ) {
    }

    public function build(): self
    {
        return $this
            ->subject('kai-kon: ログイン通知')
            ->view('kaikon::emails.login', ['email' => $this->email]);
    }
}

