<?php

namespace App\Livewire\Components\Store;

use App\Services\CatalogQueryService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class RelatedProducts extends Component
{
    public int $productId = 0;

    public ?string $categorySlug = null;

    public function mount(int $productId, ?string $categorySlug = null): void
    {
        $this->productId = $productId;
        $this->categorySlug = $categorySlug;
    }

    public function placeholder(): View
    {
        return view('livewire.components.store.related-products-placeholder');
    }

    public function render(): View
    {
        $related = new Collection;

        if ($this->categorySlug !== null) {
            $related = app(CatalogQueryService::class)->products(['category' => $this->categorySlug], 6)
                ->getCollection()
                ->reject(fn (array $product): bool => $product['id'] === $this->productId)
                ->take(6);
        }

        return view('livewire.components.store.related-products', compact('related'));
    }
}
