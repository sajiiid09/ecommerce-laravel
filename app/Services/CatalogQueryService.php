<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\StorefrontDemoData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\Schema;

class CatalogQueryService
{
    public function products(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        if (! Schema::hasTable('products')) {
            return $this->demoPaginator($this->filterDemo(StorefrontDemoData::products(), $filters), $perPage);
        }

        $query = Product::query()
            ->published()
            ->whereIn('visibility', ['visible', 'catalog_search', 'catalog_only'])
            ->with(['brand', 'primaryCategory', 'media.asset', 'variants.inventory', 'variants.optionValues.option'])
            ->addSelect([
                'catalog_price' => ProductVariant::query()
                    ->selectRaw('COALESCE(sale_price_minor, regular_price_minor, 0)')
                    ->whereColumn('product_id', 'products.id')
                    ->where('is_default', true)
                    ->limit(1),
            ]);

        $this->applyFilters($query, $filters);
        $this->applySort($query, (string) ($filters['sort'] ?? 'popular'));

        $paginator = $query->paginate($perPage)->withQueryString();

        if ($paginator->total() === 0 && empty(array_filter($filters))) {
            return $this->demoPaginator(StorefrontDemoData::products(), $perPage);
        }

        return $paginator->through(fn (Product $product): array => $this->toCard($product));
    }

    public function product(string $slug): ?array
    {
        if (! Schema::hasTable('products')) {
            return collect(StorefrontDemoData::products())->firstWhere('slug', $slug);
        }

        $product = Product::query()
            ->published()
            ->whereIn('visibility', ['visible', 'catalog_search', 'catalog_only'])
            ->where('slug', $slug)
            ->with(['brand', 'primaryCategory', 'categories', 'media.asset', 'options.values', 'variants.inventory', 'variants.optionValues.option'])
            ->first();

        return $product ? $this->toDetail($product) : null;
    }

    public function categoryOptions(): array
    {
        if (! Schema::hasTable('categories')) {
            return StorefrontDemoData::categories();
        }

        $categories = Category::active()->orderBy('sort_order')->orderBy('name')->get();

        return $categories->isEmpty()
            ? StorefrontDemoData::categories()
            : $categories->map(fn (Category $category): array => ['id' => $category->id, 'name' => $category->name, 'slug' => $category->slug])->all();
    }

    public function brandOptions(): array
    {
        if (! Schema::hasTable('brands')) {
            return StorefrontDemoData::brands();
        }

        $brands = Brand::active()->orderBy('sort_order')->orderBy('name')->get();

        return $brands->isEmpty()
            ? StorefrontDemoData::brands()
            : $brands->map(fn (Brand $brand): array => ['id' => $brand->id, 'name' => $brand->name, 'slug' => $brand->slug])->all();
    }

    public function category(string $slug): ?Category
    {
        return Schema::hasTable('categories') ? Category::active()->where('slug', $slug)->first() : null;
    }

    public function brand(string $slug): ?Brand
    {
        return Schema::hasTable('brands') ? Brand::active()->where('slug', $slug)->first() : null;
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if ($search = trim((string) ($filters['search'] ?? ''))) {
            $query->search($search);
        }

        if ($category = $filters['category'] ?? null) {
            $query->whereHas('categories', fn (Builder $categories) => $categories->active()->where('slug', $category));
        }

        $brands = array_values(array_filter((array) ($filters['brands'] ?? [])));
        if ($brands !== []) {
            $query->whereHas('brand', fn (Builder $brand) => $brand->whereIn('name', $brands)->orWhereIn('slug', $brands));
        }

        if (($filters['in_stock'] ?? false) === true) {
            $query->whereHas('defaultVariant.inventory', fn (Builder $inventory) => $inventory
                ->where('track_quantity', true)
                ->whereColumn('quantity_on_hand', '>', 'quantity_reserved')
                ->orWhere('track_quantity', false));
        }
    }

    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'price_asc' => $query->orderBy('catalog_price'),
            'price_desc' => $query->orderByDesc('catalog_price'),
            'rating' => $query->latest('products.created_at'),
            default => $query->latest('products.created_at'),
        };
    }

    private function toCard(Product $product): array
    {
        $variant = $product->variants->where('is_active', true)->sortByDesc('is_default')->first();
        $gallery = $product->media->sortBy('sort_order')->map(fn ($media) => $media->asset?->url())->filter()->values()->all();
        $price = (int) ($variant?->currentPriceMinor() ?? 0);
        $oldPrice = (int) ($variant?->compare_at_price_minor ?? 0);

        return [
            'id' => $product->id,
            'slug' => $product->slug,
            'name' => $product->name,
            'brand' => $product->brand?->name ?? 'StoreZ',
            'categorySlug' => $product->primaryCategory?->slug,
            'image' => $gallery[0] ?? asset('images/placeholders/no-image.svg'),
            'gallery' => $gallery ?: [asset('images/placeholders/no-image.svg')],
            'price' => $price,
            'oldPrice' => $oldPrice ?: null,
            'discount' => $oldPrice > 0 ? max(0, (int) round(($oldPrice - $price) / $oldPrice * 100)) : null,
            'rating' => 0,
            'reviews' => 0,
            'inStock' => ($variant?->availableQuantity() ?? 0) > 0,
            'sku' => $variant?->sku,
            'variantId' => $variant?->id,
            'options' => $this->options($product),
            'variants' => $product->variants->map(fn ($item): array => ['id' => $item->id, 'sku' => $item->sku, 'price' => $item->currentPriceMinor(), 'stock' => $item->availableQuantity(), 'optionValues' => $item->optionValues->pluck('slug')->all()])->all(),
        ];
    }

    private function toDetail(Product $product): array
    {
        return [
            ...$this->toCard($product),
            'shortDescription' => $product->short_description,
            'descriptionHtml' => $product->description_html,
            'highlights' => $product->short_description ? [$product->short_description] : [],
            'categories' => $product->categories->pluck('name')->all(),
            'metaTitle' => $product->meta_title,
        ];
    }

    private function options(Product $product): array
    {
        return $product->options->map(fn ($option): array => [
            'label' => $option->name,
            'name' => $option->slug,
            'values' => $option->values->map(fn ($value): array => ['label' => $value->value, 'value' => $value->slug])->all(),
        ])->all();
    }

    private function filterDemo(array $products, array $filters): array
    {
        $search = strtolower(trim((string) ($filters['search'] ?? '')));
        $brands = array_map('strtolower', array_values(array_filter((array) ($filters['brands'] ?? []))));

        return array_values(array_filter($products, function (array $product) use ($search, $brands, $filters): bool {
            if ($search !== '' && ! str_contains(strtolower($product['name'].' '.$product['brand'].' '.($product['sku'] ?? '')), $search)) {
                return false;
            }
            if ($brands !== [] && ! in_array(strtolower($product['brand']), $brands, true)) {
                return false;
            }
            if (($filters['category'] ?? null) && ($product['categorySlug'] ?? null) !== $filters['category']) {
                return false;
            }
            if (($filters['in_stock'] ?? false) && ! ($product['inStock'] ?? false)) {
                return false;
            }

            return true;
        }));
    }

    private function demoPaginator(array $items, int $perPage): LengthAwarePaginator
    {
        $page = max(1, (int) request('page', 1));
        $collection = collect($items);
        $slice = $collection->forPage($page, $perPage)->values();

        return new Paginator($slice, $collection->count(), $perPage, $page, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
    }
}
