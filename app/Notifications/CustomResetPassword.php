<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class CustomResetPassword extends ResetPassword
{
    public function toMail($notifiable)
    {
        $url = $this->resetUrl($notifiable);

        return (new MailMessage)
            ->subject('Réinitialisation du mot de passe')
            ->markdown('emails.reset-password', [
                'user' => $notifiable,
                'url' => $url,
                'appName' => config('app.name'),
                'supportEmail' => config('mail.from.address'),
            ]);
    }
}
