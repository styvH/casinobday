<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;

#[Layout('components.layouts.app')]
class CheckIn extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function mount()
    {
        if(Auth::check()) {
            $this->redirectRoute('dashboard.joueur');
        }
    }

    public function login()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:4',
        ]);

        if(Auth::attempt(['email'=>$this->email,'password'=>$this->password], $this->remember)){
            session()->regenerate();
            return redirect()->intended(route('dashboard.joueur'));
        }

        $this->addError('email','Identifiants invalides');
    }

    public function render()
    {
        return view('livewire.auth.check-in');
    }
}
