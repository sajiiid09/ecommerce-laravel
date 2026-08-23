<?php

namespace App\Livewire\Pages\Store;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Brand extends Component
{
    public ?string $slug = null;

    public function mount(string $slug): void
    {
        $this->slug = $slug;
    }

    public function render()
    {
        return view('pages.store.brand-content', ['slug' => $this->slug]);
    }
}
