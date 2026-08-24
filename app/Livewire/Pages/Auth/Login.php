<?php

namespace App\Livewire\Pages\Auth;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.app')]
class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function login(): void
    {
        $credentials = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);
        if (! Auth::attempt($credentials, $this->remember)) {
            $this->addError('email', 'These credentials are not valid.');
            return;
        }
        request()->session()->regenerate();
        $this->redirectIntended(Auth::user()->is_admin ? '/admin' : '/account');
    }
    public function render()
    {
        return view('pages.auth.login-content');
    }
}
