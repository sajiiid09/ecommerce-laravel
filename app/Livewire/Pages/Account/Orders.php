<?php

namespace App\Livewire\Pages\Account;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Orders extends Component
{
    public function render()
    {
        return view('pages.account.orders-content', ['orders' => auth()->user()->orders()->with('items')->latest('placed_at')->paginate(10)]);
    }
}
