<?php

namespace App\Livewire\Pages\Auth;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Register extends Component
{
    public function render()
    {
        return view('pages.auth.register-content');
    }
}
