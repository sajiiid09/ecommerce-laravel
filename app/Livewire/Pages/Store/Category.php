<?php

namespace App\Livewire\Pages\Store;

use App\Services\CatalogQueryService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Category extends Component
{
    use WithPagination;

    public ?string $slug = null;

    public array $selectedBrands = [];

    public bool $inStock = false;

    public string $sort = 'popular';

    protected CatalogQueryService $catalog;

    public function boot(CatalogQueryService $catalog): void
    {
        $this->catalog = $catalog;
    }

    public function mount(?string $slug = null): void
    {
        $this->slug = $slug;
        $this->selectedBrands = array_values(array_filter(array_map('strval', (array) request('brand', []))));
        $this->inStock = request()->boolean('in_stock');
        $this->sort = (string) request('sort', 'popular');
    }

    public function render()
    {
        $filters = [
            'category' => $this->slug,
            'brands' => $this->selectedBrands,
            'in_stock' => $this->inStock,
            'sort' => $this->sort,
        ];

        $categories = $this->catalog->categoryOptions();
        $activeCategory = collect($categories)->firstWhere('slug', $this->slug);

        return view('pages.store.category-content', [
            'slug' => $this->slug,
            'categoryName' => $activeCategory['name'] ?? 'All Products',
            'categories' => $categories,
            'brands' => collect($this->catalog->brandOptions())->pluck('name')->all(),
            'products' => $this->catalog->products($filters),
            'selectedBrands' => $this->selectedBrands,
            'inStock' => $this->inStock,
            'sort' => $this->sort,
        ]);
    }
}
