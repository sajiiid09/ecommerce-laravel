<?php

namespace App\Livewire\Pages\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

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
        // Keep the application base path when the app is served from a subdirectory
        // (for example, /storez/public in the local XAMPP setup). Avoid redirecting
        // to a stale intended path captured before the user opened the login screen.
        $this->redirect(Auth::user()->is_admin ? url('/admin') : url('/account'));
    }

    public function render()
    {
        return view('pages.auth.login-content');
    }
}
