<?php

namespace Kaikon2\Kaikondb\Notifications;

use Illuminate\Support\Facades\URL;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;

class VerifyEmailQueued extends Notification implements ShouldQueue
{
    use Queueable;


    public function __construct()
    {
        $this->onConnection('database');
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        return (new MailMessage)
            ->subject('メールアドレスの確認')
            ->line('以下のボタンをクリックして、メールアドレスを確認してください。')
            ->action('メール確認', $verificationUrl);
    }
}