<?php

namespace App\Livewire\Pages\Store;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class OrderSuccess extends Component
{
    public function render()
    {
        return view('pages.store.order-success-content');
    }
}
