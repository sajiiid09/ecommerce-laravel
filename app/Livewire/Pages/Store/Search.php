<?php

namespace App\Livewire\Pages\Store;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Search extends Component
{
    public function render()
    {
        return view('pages.store.search-content', [
            'query' => trim((string) request('q', '')),
        ]);
    }
}
