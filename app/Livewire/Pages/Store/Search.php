<?php

namespace App\Livewire\Pages\Store;

use App\Services\CatalogQueryService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Search extends Component
{
    use WithPagination;

    public string $query = '';

    public array $selectedBrands = [];

    public bool $inStock = false;

    public string $sort = 'relevance';

    protected CatalogQueryService $catalog;

    public function boot(CatalogQueryService $catalog): void
    {
        $this->catalog = $catalog;
    }

    public function mount(): void
    {
        $this->query = trim((string) request('q', ''));
        $this->selectedBrands = array_values(array_filter(array_map('strval', (array) request('brand', []))));
        $this->inStock = request()->boolean('in_stock');
        $this->sort = (string) request('sort', 'relevance');
    }

    public function render()
    {
        return view('pages.store.search-content', [
            'query' => $this->query,
            'products' => $this->catalog->products([
                'search' => $this->query,
                'brands' => $this->selectedBrands,
                'in_stock' => $this->inStock,
                'sort' => $this->sort,
            ]),
            'brands' => collect($this->catalog->brandOptions())->pluck('name')->all(),
            'selectedBrands' => $this->selectedBrands,
            'inStock' => $this->inStock,
            'sort' => $this->sort,
        ]);
    }
}
