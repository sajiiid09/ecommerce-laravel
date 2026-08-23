<?php

namespace App\Livewire\Pages\Account;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    public function render()
    {
        return view('pages.account.dashboard-content');
    }
}
