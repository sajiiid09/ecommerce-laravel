<?php

namespace App\Livewire\Pages\Store;

use App\Services\CatalogQueryService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Search extends Component
{
    protected CatalogQueryService $catalog;

    public function boot(CatalogQueryService $catalog): void
    {
        $this->catalog = $catalog;
    }

    public function render()
    {
        $query = trim((string) request('q', ''));

        return view('pages.store.search-content', [
            'query' => $query,
            'products' => $this->catalog->products([
                'search' => $query,
                'brands' => (array) request('brand', []),
                'in_stock' => request()->boolean('in_stock'),
                'sort' => request('sort', 'relevance'),
            ]),
            'brands' => collect($this->catalog->brandOptions())->pluck('name')->all(),
            'selectedBrands' => (array) request('brand', []),
            'sort' => request('sort', 'relevance'),
        ]);
    }
}
