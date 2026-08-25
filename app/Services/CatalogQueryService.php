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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class CatalogQueryService
{
    private const PUBLIC_VISIBILITIES = ['visible', 'catalog_search', 'catalog_only'];

    public function __construct(private readonly CatalogCache $cache) {}

    public function products(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        if (! Schema::hasTable('products')) {
            return $this->demoPaginator($this->filterDemo(StorefrontDemoData::products(), $filters), $perPage);
        }

        $query = $this->publicProductsQuery();
        $this->applyFilters($query, $filters);
        $this->applySort($query, (string) ($filters['sort'] ?? 'popular'));

        $paginator = $query->paginate($perPage)->withQueryString();

        if ($paginator->total() === 0 && ! $this->hasMeaningfulFilters($filters)) {
            return $this->demoPaginator(StorefrontDemoData::products(), $perPage);
        }

        return $paginator->through(fn (Product $product): array => $this->toCard($product));
    }

    public function homepageProducts(string $type, int $limit = 6): array
    {
        if (! Schema::hasTable('products')) {
            return $this->homepageDemoProducts($type, $limit);
        }

        $query = $this->publicProductsQuery();

        match ($type) {
            'featured_products' => $query->featured()->latest('products.updated_at'),
            'bestsellers' => $query->latest('products.created_at'),
            'new_arrivals' => $query->latest('products.published_at'),
            'flash_deals' => $query->whereHas('variants', fn (Builder $variants) => $variants->whereNotNull('sale_price_minor'))
                ->orderByDesc('catalog_price'),
            default => $query->latest('products.updated_at'),
        };

        return $query->limit($limit)->get()->map(fn (Product $product): array => $this->toCard($product))->all();
    }

    public function product(string $slug): ?array
    {
        if (! Schema::hasTable('products')) {
            return collect(StorefrontDemoData::products())->firstWhere('slug', $slug);
        }

        $product = $this->publicProductsQuery()
            ->where('slug', $slug)
            ->with(['categories', 'attributeValues.attribute', 'attributeValues.attributeValue'])
            ->first();

        return $product ? $this->toDetail($product) : null;
    }

    public function categoryOptions(): array
    {
        if (! Schema::hasTable('categories')) {
            return StorefrontDemoData::categories();
        }

        return Cache::remember($this->cache->categoryOptions(), 300, function (): array {
            $categories = Category::active()->orderBy('sort_order')->orderBy('name')->get();

            return $categories->isEmpty()
                ? StorefrontDemoData::categories()
                : $categories->map(fn (Category $category): array => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ])->all();
        });
    }

    public function brandOptions(): array
    {
        if (! Schema::hasTable('brands')) {
            return StorefrontDemoData::brands();
        }

        return Cache::remember($this->cache->brandOptions(), 300, function (): array {
            $brands = Brand::active()->orderBy('sort_order')->orderBy('name')->get();

            return $brands->isEmpty()
                ? StorefrontDemoData::brands()
                : $brands->map(fn (Brand $brand): array => [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'slug' => $brand->slug,
                ])->all();
        });
    }

    public function category(string $slug): ?Category
    {
        return Schema::hasTable('categories')
            ? Category::active()->where('slug', $slug)->first()
            : null;
    }

    public function brand(string $slug): ?Brand
    {
        return Schema::hasTable('brands')
            ? Brand::active()->where('slug', $slug)->first()
            : null;
    }

    public function forgetCachedOptions(): void
    {
        $this->cache->forgetAll();
    }

    private function publicProductsQuery(): Builder
    {
        return Product::query()
            ->published()
            ->whereIn('visibility', self::PUBLIC_VISIBILITIES)
            ->with([
                'brand',
                'primaryCategory',
                'media' => fn ($media) => $media->whereNull('product_variant_id')->orderBy('sort_order'),
                'media.asset',
                'options' => fn ($options) => $options->orderBy('sort_order'),
                'options.values' => fn ($values) => $values->orderBy('sort_order'),
                'variants' => fn ($variants) => $variants->where('is_active', true)->orderByDesc('is_default')->orderBy('sort_order'),
                'variants.inventory',
                'variants.media.asset',
                'variants.optionValues.option',
            ])
            ->addSelect([
                'catalog_price' => ProductVariant::query()
                    ->selectRaw('COALESCE(sale_price_minor, regular_price_minor, 0)')
                    ->whereColumn('product_id', 'products.id')
                    ->where('is_active', true)
                    ->orderByDesc('is_default')
                    ->orderBy('sort_order')
                    ->limit(1),
            ]);
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
            $query->whereHas('brand', fn (Builder $brand) => $brand
                ->whereIn('name', $brands)
                ->orWhereIn('slug', $brands));
        }

        if (($filters['in_stock'] ?? false) === true) {
            $query->whereHas('variants.inventory', fn (Builder $inventory) => $inventory
                ->where(function (Builder $stock): void {
                    $stock->where('track_quantity', false)
                        ->orWhere(function (Builder $tracked): void {
                            $tracked->where('track_quantity', true)
                                ->whereColumn('quantity_on_hand', '>', 'quantity_reserved');
                        });
                }));
        }
    }

    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'price_asc' => $query->orderBy('catalog_price')->orderByDesc('products.id'),
            'price_desc' => $query->orderByDesc('catalog_price')->orderByDesc('products.id'),
            default => $query->latest('products.created_at')->latest('products.id'),
        };
    }

    private function toCard(Product $product): array
    {
        $variant = $product->variants->first();
        $gallery = $product->media
            ->sortBy('sort_order')
            ->map(fn ($media): ?string => $media->asset?->url() ?: $media->path)
            ->filter()
            ->values()
            ->all();
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
            'inStock' => $this->variantIsAvailable($variant),
            'sku' => $variant?->sku,
            'variantId' => $variant?->id,
            'options' => $this->options($product),
            'variants' => $product->variants->map(fn (ProductVariant $item): array => $this->variantData($item))->all(),
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
            'attributes' => $product->attributeValues->map(fn ($value): array => [
                'name' => $value->attribute?->name,
                'value' => $value->attributeValue?->value ?? $value->text_value ?? $value->number_value ?? ($value->boolean_value ? 'Yes' : 'No'),
                'unit' => $value->attribute?->unit,
            ])->filter(fn (array $attribute): bool => filled($attribute['name']) && filled($attribute['value']))->values()->all(),
            'metaTitle' => $product->meta_title,
            'stock' => $product->variants->first()?->availableQuantity() ?? 0,
        ];
    }

    private function options(Product $product): array
    {
        return $product->options->map(fn ($option): array => [
            'label' => $option->name,
            'name' => $option->slug,
            'values' => $option->values->map(fn ($value): array => [
                'label' => $value->value,
                'value' => $value->slug,
            ])->all(),
        ])->all();
    }

    private function variantData(ProductVariant $variant): array
    {
        return [
            'id' => $variant->id,
            'sku' => $variant->sku,
            'price' => $variant->currentPriceMinor(),
            'compareAtPrice' => $variant->compare_at_price_minor,
            'stock' => $variant->availableQuantity(),
            'available' => $this->variantIsAvailable($variant),
            'optionValues' => $variant->optionValues->map(fn ($value): array => [
                'option' => $value->option?->slug,
                'value' => $value->slug,
                'label' => $value->value,
            ])->values()->all(),
            'gallery' => $variant->media
                ->sortBy('sort_order')
                ->map(fn ($media): ?string => $media->asset?->url() ?: $media->path)
                ->filter()
                ->values()
                ->all(),
        ];
    }

    private function variantIsAvailable(?ProductVariant $variant): bool
    {
        return $variant !== null && (! $variant->inventory?->track_quantity || $variant->availableQuantity() > 0);
    }

    private function hasMeaningfulFilters(array $filters): bool
    {
        return filled($filters['search'] ?? null)
            || filled($filters['category'] ?? null)
            || filled($filters['brands'] ?? [])
            || (bool) ($filters['in_stock'] ?? false);
    }

    private function homepageDemoProducts(string $type, int $limit): array
    {
        $products = StorefrontDemoData::products();
        $offset = match ($type) {
            'bestsellers', 'flash_deals' => 3,
            'new_arrivals' => 6,
            default => 0,
        };

        return array_slice($products, $offset, $limit);
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
