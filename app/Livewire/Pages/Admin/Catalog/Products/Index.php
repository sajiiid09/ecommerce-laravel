<?php

namespace App\Livewire\Pages\Admin\Catalog\Products;

use App\Enums\ProductStatus;
use App\Livewire\Pages\Admin\Catalog\ResourceIndex;
use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class Index extends ResourceIndex
{
    public string $status = '';

    public ?int $categoryFilter = null;

    public ?int $brandFilter = null;

    protected function model(): string
    {
        return Product::class;
    }

    protected function title(): string
    {
        return 'Products';
    }

    protected function rows()
    {
        return Product::query()
            ->with(['brand', 'primaryCategory', 'defaultVariant.inventory'])
            ->when($this->searchQuery, fn ($query) => $query->search($this->searchQuery))
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->when($this->categoryFilter, fn ($query) => $query->where('primary_category_id', $this->categoryFilter))
            ->when($this->brandFilter, fn ($query) => $query->where('brand_id', $this->brandFilter))
            ->when($this->sortBy !== '', fn ($query) => $this->applyCatalogSorting($query))
            ->when($this->sortBy === '', fn ($query) => $query->latest());
    }

    public function bulk(string $action): void
    {
        $this->validate(['selectedIds' => ['array']]);

        $products = Product::query()->whereKey($this->selectedIds)->get();

        foreach ($products as $product) {
            $ability = $action === 'delete' ? 'delete' : 'update';
            Gate::authorize($ability, $product);

            match ($action) {
                'publish' => $product->update(['status' => ProductStatus::Published->value]),
                'draft' => $product->update(['status' => ProductStatus::Draft->value]),
                'archive' => $product->update(['status' => ProductStatus::Archived->value]),
                'feature' => $product->update(['is_featured' => true]),
                'unfeature' => $product->update(['is_featured' => false]),
                'delete' => $product->delete(),
                default => null,
            };
        }

        $this->clearSelection();
        $this->forgetCatalogCache();
    }

    public function resetFilters(): void
    {
        $this->reset(['searchQuery', 'status', 'categoryFilter', 'brandFilter']);
        $this->clearSelection();
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedBrandFilter(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    /** @return array<int, string> */
    protected function sortableColumns(): array
    {
        return ['name', 'status', 'created_at'];
    }

    public function render()
    {
        $rows = $this->rows()->paginate($this->perPage);

        $this->syncVisibleIds($rows);

        return view('livewire.pages.admin.catalog.products.index', [
            'rows' => $rows,
            'title' => $this->title(),
            'stats' => [
                'total' => Product::count(),
                'active' => Product::query()->where('status', ProductStatus::Published->value)->count(),
                'low_stock' => InventoryItem::query()->where('track_quantity', true)->whereRaw('(quantity_on_hand - quantity_reserved) > 0')->whereRaw('(quantity_on_hand - quantity_reserved) <= low_stock_threshold')->count(),
                'draft' => Product::query()->where('status', ProductStatus::Draft->value)->count(),
            ],
            'topCategories' => Category::query()->withCount('products')->orderByDesc('products_count')->limit(5)->get(),
            'totalCategories' => Category::count(),
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'brands' => Brand::query()->orderBy('name')->get(['id', 'name']),
            'inventoryAlerts' => [
                'Out of Stock' => InventoryItem::query()->whereRaw('(quantity_on_hand - quantity_reserved) <= 0')->count(),
                'Low Stock' => InventoryItem::query()->where('track_quantity', true)->whereRaw('(quantity_on_hand - quantity_reserved) > 0')->whereRaw('(quantity_on_hand - quantity_reserved) <= low_stock_threshold')->count(),
                'Expiring Soon' => 0,
            ],
            'totalProductValue' => (int) Product::query()
                ->join('product_variants', 'product_variants.product_id', '=', 'products.id')
                ->where('product_variants.is_default', true)
                ->sum(DB::raw('COALESCE(product_variants.regular_price_minor, 0)')),
        ]);
    }
}
