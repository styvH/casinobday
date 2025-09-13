<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user)
    {
    }

    public function build(): self
    {
        return $this
            ->subject('Bienvenue sur '.config('app.name'))
            ->markdown('emails.welcome', [
                'user' => $this->user,
                'appName' => config('app.name'),
                'loginUrl' => route('login'),
            ]);
    }
}
