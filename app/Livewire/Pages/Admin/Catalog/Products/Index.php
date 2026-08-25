<?php

namespace App\Livewire\Pages\Admin\Catalog\Products;

use App\Enums\ProductStatus;
use App\Livewire\Pages\Admin\Catalog\ResourceIndex;
use App\Models\Category;
use App\Models\InventoryItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class Index extends ResourceIndex
{
    public string $status = '';

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
            ->when($this->search, fn ($query) => $query->search($this->search))
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->orderBy($this->sortField, $this->sortDirection);
    }

    public function bulk(string $action): void
    {
        $this->validate(['selected' => ['array']]);

        $products = Product::query()->whereKey($this->selected)->get();

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

    public function render()
    {
        return view('livewire.pages.admin.catalog.products.index', [
            'rows' => $this->rows()->paginate($this->perPage),
            'title' => $this->title(),
            'stats' => [
                'total' => Product::count(),
                'active' => Product::query()->where('status', ProductStatus::Published->value)->count(),
                'low_stock' => InventoryItem::query()->where('track_quantity', true)->whereRaw('(quantity_on_hand - quantity_reserved) > 0')->whereRaw('(quantity_on_hand - quantity_reserved) <= low_stock_threshold')->count(),
                'draft' => Product::query()->where('status', ProductStatus::Draft->value)->count(),
            ],
            'topCategories' => Category::query()->withCount('products')->orderByDesc('products_count')->limit(5)->get(),
            'totalCategories' => Category::count(),
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
