<?php

namespace App\Services;

use App\Enums\ProductType;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductMedia;
use App\Models\ProductReview;
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

    private const HOMEPAGE_PRODUCT_SOURCES = ['featured', 'newest', 'bestsellers', 'on_sale', 'category', 'brand'];

    private const HOMEPAGE_PRODUCT_SORTS = ['default', 'newest', 'price_asc', 'price_desc'];

    private const LEGACY_HOMEPAGE_PRODUCT_SOURCES = [
        'featured_products' => 'featured',
        'new_arrivals' => 'newest',
        'bestsellers' => 'bestsellers',
        'flash_deals' => 'on_sale',
    ];

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

    /**
     * @return array<int, array<string, mixed>>
     */
    public function productSuggestions(?string $term, int $limit = 5): array
    {
        $term = trim((string) $term);

        if ($term === '' || mb_strlen($term) < 2) {
            return [];
        }

        $limit = max(1, min(10, $limit));

        if (! Schema::hasTable('products')) {
            return array_slice($this->filterDemo(StorefrontDemoData::products(), ['search' => $term]), 0, $limit);
        }

        return $this->publicProductsQuery()
            ->search($term)
            ->latest('products.created_at')
            ->latest('products.id')
            ->limit($limit)
            ->get()
            ->map(fn (Product $product): array => $this->toCard($product))
            ->all();
    }

    public function homepageProducts(array|string $querySettings, int $limit = 6): array
    {
        $settings = $this->normalizeHomepageProductSettings($querySettings, $limit);

        if (! Schema::hasTable('products')) {
            return $this->homepageDemoProducts($settings);
        }

        $query = $this->publicProductsQuery();
        $this->applyHomepageProductSource($query, $settings);
        $this->applyHomepageProductSort($query, $settings);

        return $query->limit($settings['limit'])->get()->map(fn (Product $product): array => $this->toCard($product))->all();
    }

    public function normalizeHomepageProductSettings(array|string $querySettings, int $legacyLimit = 6): array
    {
        if (is_string($querySettings)) {
            return [
                'source' => self::LEGACY_HOMEPAGE_PRODUCT_SOURCES[$querySettings] ?? 'featured',
                'category' => null,
                'brand' => null,
                'sort' => 'default',
                'limit' => max(1, min(24, $legacyLimit)),
            ];
        }

        $source = (string) ($querySettings['source'] ?? 'featured');
        $sort = (string) ($querySettings['sort'] ?? 'default');

        return [
            'source' => in_array($source, self::HOMEPAGE_PRODUCT_SOURCES, true) ? $source : 'featured',
            'category' => filled($querySettings['category'] ?? null) ? (string) $querySettings['category'] : null,
            'brand' => filled($querySettings['brand'] ?? null) ? (string) $querySettings['brand'] : null,
            'sort' => in_array($sort, self::HOMEPAGE_PRODUCT_SORTS, true) ? $sort : 'default',
            'limit' => max(1, min(24, (int) ($querySettings['limit'] ?? 6))),
        ];
    }

    public function product(string $slug): ?array
    {
        if (! Schema::hasTable('products')) {
            return collect(StorefrontDemoData::products())->firstWhere('slug', $slug);
        }

        return Cache::remember($this->cache->product($slug), 900, function () use ($slug): ?array {
            $product = $this->publicProductsQuery()
                ->where('slug', $slug)
                ->with([
                    'categories',
                    /* 'attributeValues.attribute', 'attributeValues.attributeValue', */
                ])
                ->first();

            return $product ? $this->toDetail($product) : null;
        });
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
        $query = Product::query()
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
                    ->selectRaw('CASE WHEN sale_price_minor IS NOT NULL AND sale_price_minor < regular_price_minor THEN sale_price_minor ELSE COALESCE(regular_price_minor, 0) END')
                    ->whereColumn('product_id', 'products.id')
                    ->where('is_active', true)
                    ->orderByDesc('is_default')
                    ->orderBy('sort_order')
                    ->limit(1),
            ]);

        if (! Schema::hasTable('product_reviews')) {
            return $query;
        }

        return $query->addSelect([
            'review_average' => ProductReview::query()
                ->selectRaw('ROUND(AVG(rating), 1)')
                ->whereColumn('product_id', 'products.id')
                ->where('status', 'approved'),
            'review_count' => ProductReview::query()
                ->selectRaw('COUNT(*)')
                ->whereColumn('product_id', 'products.id')
                ->where('status', 'approved'),
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
        $isVariable = $product->product_type === ProductType::Variable;
        $parentGallery = $product->media
            ->sortBy('sort_order')
            ->map(fn (ProductMedia $media): ?string => $this->mediaUrl($media))
            ->filter()
            ->values()
            ->all();
        $variantGallery = $variant?->media
            ->sortBy('sort_order')
            ->map(fn (ProductMedia $media): ?string => $this->mediaUrl($media))
            ->filter()
            ->values()
            ->all() ?? [];
        $gallery = $isVariable ? ($variantGallery ?: $parentGallery) : $parentGallery;
        $placeholder = asset('images/placeholders/no-image.svg');
        $price = (int) ($variant?->currentPriceMinor() ?? 0);
        $oldPrice = (int) ($variant?->compareAtPriceMinor() ?? 0);

        return [
            'id' => $product->id,
            'slug' => $product->slug,
            'name' => $product->name,
            'brand' => $product->brand?->name ?? 'StoreZ',
            'categorySlug' => $product->primaryCategory?->slug,
            'image' => $gallery[0] ?? $placeholder,
            'gallery' => $gallery ?: [$placeholder],
            'isVariable' => $isVariable,
            'shortDescription' => $product->short_description,
            'price' => $price,
            'oldPrice' => $oldPrice ?: null,
            'discount' => $oldPrice > 0 ? max(0, (int) round(($oldPrice - $price) / $oldPrice * 100)) : null,
            'rating' => round((float) ($product->review_average ?? 0), 1),
            'reviews' => (int) ($product->review_count ?? 0),
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
            /*
            'attributes' => $product->attributeValues->map(fn ($value): array => [
                'name' => $value->attribute?->name,
                'value' => $value->attributeValue?->value ?? $value->text_value ?? $value->number_value ?? ($value->boolean_value ? 'Yes' : 'No'),
                'unit' => $value->attribute?->unit,
            ])->filter(fn (array $attribute): bool => filled($attribute['name']) && filled($attribute['value']))->values()->all(),
            */
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
            'compareAtPrice' => $variant->compareAtPriceMinor(),
            'stock' => $variant->availableQuantity(),
            'available' => $this->variantIsAvailable($variant),
            'optionValues' => $variant->optionValues->map(fn ($value): array => [
                'option' => $value->option?->slug,
                'value' => $value->slug,
                'label' => $value->value,
            ])->values()->all(),
            'gallery' => $variant->media
                ->sortBy('sort_order')
                ->map(fn (ProductMedia $media): ?string => $this->mediaUrl($media))
                ->filter()
                ->values()
                ->all(),
        ];
    }

    private function variantIsAvailable(?ProductVariant $variant): bool
    {
        return $variant !== null && (! $variant->inventory?->track_quantity || $variant->availableQuantity() > 0);
    }

    private function mediaUrl(ProductMedia $media): ?string
    {
        $path = $media->asset?->url() ?: $media->path;

        if (! filled($path)) {
            return null;
        }

        return str($path)->startsWith(['http://', 'https://', '/'])
            ? $path
            : asset(ltrim($path, '/'));
    }

    private function hasMeaningfulFilters(array $filters): bool
    {
        return filled($filters['search'] ?? null)
            || filled($filters['category'] ?? null)
            || filled($filters['brands'] ?? [])
            || (bool) ($filters['in_stock'] ?? false);
    }

    private function applyHomepageProductSource(Builder $query, array $settings): void
    {
        match ($settings['source']) {
            'featured' => $query->featured(),
            'on_sale' => $query->whereHas('variants', fn (Builder $variants) => $variants
                ->whereNotNull('sale_price_minor')
                ->whereColumn('sale_price_minor', '<', 'regular_price_minor')),
            'category' => $query->whereHas('categories', fn (Builder $categories) => $categories
                ->active()
                ->where('slug', $settings['category'] ?? '')),
            'brand' => $query->whereHas('brand', fn (Builder $brand) => $brand
                ->active()
                ->where('slug', $settings['brand'] ?? '')),
            default => null,
        };
    }

    private function applyHomepageProductSort(Builder $query, array $settings): void
    {
        if ($settings['sort'] !== 'default') {
            match ($settings['sort']) {
                'newest' => $query->latest('products.published_at')->latest('products.id'),
                'price_asc' => $query->orderBy('catalog_price')->orderByDesc('products.id'),
                'price_desc' => $query->orderByDesc('catalog_price')->orderByDesc('products.id'),
                default => null,
            };

            return;
        }

        match ($settings['source']) {
            'newest' => $query->latest('products.published_at')->latest('products.id'),
            'bestsellers' => $query->latest('products.created_at')->latest('products.id'),
            'on_sale' => $query->orderByDesc('catalog_price')->orderByDesc('products.id'),
            default => $query->latest('products.updated_at')->latest('products.id'),
        };
    }

    private function homepageDemoProducts(array $settings): array
    {
        $products = StorefrontDemoData::products();
        $offset = match ($settings['source']) {
            'bestsellers', 'on_sale' => 3,
            'newest' => 6,
            default => 0,
        };

        if ($settings['source'] === 'brand' && filled($settings['brand'])) {
            $products = $this->filterDemo($products, ['brands' => [$settings['brand']]]);
            $offset = 0;
        }

        if ($settings['source'] === 'category' && filled($settings['category'])) {
            $products = $this->filterDemo($products, ['category' => $settings['category']]);
            $offset = 0;
        }

        if (in_array($settings['sort'], ['price_asc', 'price_desc'], true)) {
            usort($products, fn (array $left, array $right): int => $settings['sort'] === 'price_asc'
                ? $left['price'] <=> $right['price']
                : $right['price'] <=> $left['price']);
            $offset = 0;
        }

        return array_slice($products, $offset, $settings['limit']);
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
        $page = Paginator::resolveCurrentPage();
        $collection = collect($items);
        $slice = $collection->forPage($page, $perPage)->values();

        return new Paginator($slice, $collection->count(), $perPage, $page, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
    }
}
