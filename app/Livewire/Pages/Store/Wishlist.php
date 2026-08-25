<?php

namespace App\Livewire\Pages\Store;

use App\Services\CatalogQueryService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Wishlist extends Component
{
    protected CatalogQueryService $catalog;

    public function boot(CatalogQueryService $catalog): void
    {
        $this->catalog = $catalog;
    }

    public function render()
    {
        return view('pages.store.wishlist-content', [
            'products' => $this->catalog->products([], 1000)->getCollection(),
        ]);
    }
}
