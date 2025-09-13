<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ResetPassword extends Component
{
    public string $email = '';
    public string $token = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $status = '';

    public function mount(string $token, string $email = ''): void
    {
        $this->token = $token;
        $this->email = $email;
    }

    public function resetPassword()
    {
        $data = $this->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|string|min:6',
        ]);

        $status = Password::reset([
            'email' => $data['email'],
            'password' => $data['password'],
            'password_confirmation' => $data['password_confirmation'],
            'token' => $this->token,
        ], function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password),
            ])->save();
        });

        if ($status === Password::PASSWORD_RESET) {
            $this->status = __($status);
        } else {
            $this->addError('email', __($status));
        }
    }

    public function render()
    {
        return view('livewire.auth.reset-password');
    }
}
