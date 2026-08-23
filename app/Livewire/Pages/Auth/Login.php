<?php

namespace App\Livewire\Pages\Auth;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Login extends Component
{
    public function render()
    {
        return view('pages.auth.login-content');
    }
}
