<?php

namespace App\Livewire\Pages\Store;

use App\Services\CatalogQueryService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Product extends Component
{
    public array $product;

    protected CatalogQueryService $catalog;

    public function boot(CatalogQueryService $catalog): void
    {
        $this->catalog = $catalog;
    }

    public function mount(string $slug): void
    {
        $product = $this->catalog->product($slug);
        abort_unless($product, 404);
        $this->product = $product;
    }

    public function render()
    {
        $related = $this->catalog->products(['category' => $this->product['categorySlug'] ?? null], 6)
            ->getCollection()
            ->reject(fn (array $product): bool => $product['id'] === $this->product['id'])
            ->take(6);

        return view('pages.store.product', ['product' => $this->product, 'related' => $related]);
    }
}
