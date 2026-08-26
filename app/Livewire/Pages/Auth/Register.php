<?php

namespace App\Livewire\Pages\Auth;

use App\Models\User;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Register extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function register(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create($data);
        Auth::login($user);
        session()->regenerate();
        app(CartService::class)->merge($user);

        $this->redirect(route('account.dashboard'));
    }

    public function render()
    {
        return view('pages.auth.register-content');
    }
}
