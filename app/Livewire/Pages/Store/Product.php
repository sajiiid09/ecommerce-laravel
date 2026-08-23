<?php

namespace App\Livewire\Pages\Store;

use App\Support\StorefrontDemoData;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Product extends Component
{
    public array $product;

    public function mount(string $slug): void
    {
        $product = collect(StorefrontDemoData::products())->firstWhere('slug', $slug);
        abort_unless($product, 404);
        $this->product = $product;
    }

    public function render()
    {
        return view('pages.store.product', ['product' => $this->product]);
    }
}
