<?php

namespace App\Livewire\Pages\Store;

use App\Services\CatalogQueryService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Category extends Component
{
    public ?string $slug = null;

    protected CatalogQueryService $catalog;

    public function boot(CatalogQueryService $catalog): void
    {
        $this->catalog = $catalog;
    }

    public function mount(?string $slug = null): void
    {
        $this->slug = $slug;
    }

    public function render()
    {
        $filters = [
            'category' => $this->slug,
            'brands' => (array) request('brand', []),
            'in_stock' => request()->boolean('in_stock'),
            'sort' => request('sort', 'popular'),
        ];

        $categories = $this->catalog->categoryOptions();
        $activeCategory = collect($categories)->firstWhere('slug', $this->slug);

        return view('pages.store.category-content', [
            'slug' => $this->slug,
            'categoryName' => $activeCategory['name'] ?? 'All Products',
            'categories' => $categories,
            'brands' => collect($this->catalog->brandOptions())->pluck('name')->all(),
            'products' => $this->catalog->products($filters),
            'selectedBrands' => $filters['brands'],
            'sort' => $filters['sort'],
        ]);
    }
}
