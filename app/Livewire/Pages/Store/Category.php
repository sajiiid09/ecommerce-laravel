<?php

namespace App\Livewire\Pages\Store;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Category extends Component
{
    public ?string $slug = null;

    public function mount(?string $slug = null): void
    {
        $this->slug = $slug;
    }

    public function render()
    {
        return view('pages.store.category-content', ['slug' => $this->slug]);
    }
}
