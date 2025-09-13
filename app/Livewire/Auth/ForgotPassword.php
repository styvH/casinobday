<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ForgotPassword extends Component
{
    public string $email = '';
    public string $status = '';

    public function sendLink()
    {
        $data = $this->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(['email' => $data['email']]);
        if ($status === Password::RESET_LINK_SENT) {
            $this->status = __($status);
        } else {
            $this->addError('email', __($status));
        }
    }

    public function render()
    {
        return view('livewire.auth.forgot-password');
    }
}
