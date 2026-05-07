<?php

namespace Kaikon2\Kaikondb\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LoginNotificationMail extends Mailable implements ShouldQueue
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

