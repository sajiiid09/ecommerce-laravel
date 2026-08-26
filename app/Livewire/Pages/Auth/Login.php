<?php

namespace App\Livewire\Pages\Auth;

use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
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
        $key = strtolower($this->email).'|'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('email', 'Too many login attempts. Please try again later.');

            return;
        }

        $credentials = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);
        if (! Auth::attempt($credentials, $this->remember)) {
            RateLimiter::hit($key, 60);
            $this->addError('email', 'These credentials are not valid.');

            return;
        }
        RateLimiter::clear($key);
        session()->regenerate();
        app(CartService::class)->merge(Auth::user());
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
