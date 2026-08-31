<?php

namespace App\Livewire\Pages\Admin\Catalog\Products;

use App\Enums\ImagePreset;
// use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\MediaAsset;
use App\Models\Product;
use App\Models\Tag;
use App\Services\MediaService;
use App\Services\ProductService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class Edit extends Component
{
    use WithFileUploads;

    public ?Product $product = null;

    public $image;

    public array $description_json = ['type' => 'doc', 'content' => []];

    public string $name = '';

    public string $slug = '';

    public string $product_type = 'simple';

    public string $status = 'draft';

    public string $visibility = 'visible';

    public $brand_id = null;

    public $primary_category_id = null;

    public array $category_ids = [];

    public array $tag_ids = [];

    /* public array $attribute_values = []; */

    public array $selectedMediaIds = [];

    public ?string $short_description = '';

    public ?string $description_html = '';

    public ?string $meta_title = '';

    public ?string $meta_description = '';

    public ?string $canonical_url = '';

    public bool $is_indexable = true;

    public bool $taxable = true;

    public string $regular_price = '0.00';

    public ?string $sale_price = null;

    public ?string $cost_price = null;

    public int $inventory_quantity = 0;

    public int $low_stock_threshold = 10;

    public bool $track_quantity = true;

    public bool $allow_backorders = false;

    public bool $is_featured = false;

    protected ProductService $products;

    protected MediaService $media;

    public function boot(ProductService $products, MediaService $media): void
    {
        $this->products = $products;
        $this->media = $media;
    }

    public function mount(?Product $product = null): void
    {
        $this->authorize($product?->exists ? 'update' : 'create', $product?->exists ? $product : Product::class);
        if (! $product?->exists) {
            return;
        }
        $this->product = $product->load('defaultVariant');
        $this->fill($product->only([
            'name', 'slug', 'brand_id', 'primary_category_id', 'short_description',
            'description_html', 'meta_title', 'meta_description', 'canonical_url',
            'is_indexable', 'taxable', 'is_featured',
        ]));
        $this->category_ids = $product->categories()->pluck('categories.id')->all();
        $this->tag_ids = $product->tags()->pluck('tags.id')->all();
        /*
        foreach ($product->attributeValues as $attributeValue) {
            $attribute = $attributeValue->attribute;
            if (! $attribute) {
                continue;
            }

            $value = match ($attribute->type) {
                'select', 'multi_select' => $attributeValue->attribute_value_id,
                'number' => $attributeValue->number_value,
                'boolean' => $attributeValue->boolean_value,
                default => $attributeValue->text_value,
            };

            if ($attribute->type === 'multi_select') {
                $this->attribute_values[$attribute->id] ??= [];
                $this->attribute_values[$attribute->id][] = $value;
            } else {
                $this->attribute_values[$attribute->id] = $value;
            }
        }
        */
        $this->selectedMediaIds = $product->media()->orderBy('sort_order')->pluck('media_asset_id')->filter()->map(fn ($id) => (int) $id)->all();
        $this->product_type = $product->product_type?->value ?? 'simple';
        $this->status = $product->status?->value ?? 'draft';
        $this->visibility = $product->visibility?->value ?? 'visible';
        $this->description_json = $product->description_json ?? ['type' => 'doc', 'content' => []];
        $this->regular_price = $this->formatPrice($product->defaultVariant?->regular_price_minor ?? 0);
        $this->sale_price = $this->formatNullablePrice($product->defaultVariant?->sale_price_minor);
        $this->cost_price = $this->formatNullablePrice($product->defaultVariant?->cost_price_minor);
        $inventory = $product->defaultVariant?->inventory;
        $this->inventory_quantity = (int) ($inventory?->quantity_on_hand ?? 0);
        $this->low_stock_threshold = (int) ($inventory?->low_stock_threshold ?? 10);
        $this->track_quantity = (bool) ($inventory?->track_quantity ?? true);
        $this->allow_backorders = (bool) ($inventory?->allow_backorders ?? false);
    }

    public function saveProduct(): void
    {
        $this->authorize($this->product?->exists ? 'update' : 'create', $this->product?->exists ? $this->product : Product::class);

        if ($this->brand_id === true || blank($this->brand_id)) {
            $this->brand_id = null;
        }

        $data = $this->validate([
            'name' => 'required|string|max:255', 'slug' => 'nullable|string|max:255', 'product_type' => 'required|in:simple,variable',
            'status' => 'required|in:draft,published,archived', 'visibility' => 'required|in:visible,catalog_search,catalog_only,search_only,hidden', 'brand_id' => 'nullable|exists:brands,id',
            'primary_category_id' => 'nullable|exists:categories,id', 'category_ids' => 'array', 'category_ids.*' => 'integer|exists:categories,id',
            'tag_ids' => 'array', 'tag_ids.*' => 'integer|exists:tags,id', 'regular_price' => 'required|numeric|min:0|decimal:0,2', 'sale_price' => 'nullable|numeric|min:0|decimal:0,2',
            /* 'attribute_values' => 'array', */
            'cost_price' => 'nullable|numeric|min:0|decimal:0,2', 'inventory_quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:0', 'track_quantity' => 'boolean', 'allow_backorders' => 'boolean',
            'is_indexable' => 'boolean', 'taxable' => 'boolean', 'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500', 'canonical_url' => 'nullable|url|max:255',
            'image' => 'nullable|image|max:5120',
        ]);

        $data['brand_id'] = filled($data['brand_id'] ?? null) ? (int) $data['brand_id'] : null;
        $data['primary_category_id'] = filled($data['primary_category_id'] ?? null) ? (int) $data['primary_category_id'] : null;
        $data['slug'] = filled($data['slug'] ?? null) ? $data['slug'] : $data['name'];

        foreach (['regular_price', 'sale_price', 'cost_price'] as $priceField) {
            $data[$priceField.'_minor'] = $this->priceToMinor($data[$priceField] ?? null);
            unset($data[$priceField]);
        }

        if ($data['sale_price_minor'] !== null && $data['sale_price_minor'] > $data['regular_price_minor']) {
            $this->addError('sale_price', 'Sale price must not exceed the regular price.');

            return;
        }

        $data['is_featured'] = $this->is_featured;
        $data['taxable'] = $this->taxable;
        $data['is_indexable'] = $this->is_indexable;
        $data['meta_title'] = $this->meta_title;
        $data['meta_description'] = $this->meta_description;
        $data['canonical_url'] = $this->canonical_url;
        $data['description_json'] = $this->description_json;
        $data['description_html'] = $this->description_html;
        $data['category_ids'] = array_values(array_unique(array_map('intval', $this->category_ids ?: array_filter([$this->primary_category_id]))));
        $data['tag_ids'] = array_values(array_unique(array_map('intval', $this->tag_ids)));
        /* $data['attribute_values'] = $this->attribute_values; */
        $mediaIds = $this->selectedMediaIds;
        if ($this->image) {
            $mediaIds[] = $this->media->upload($this->image, 'products', ImagePreset::Product)->id;
        }
        $data['media_ids'] = array_values(array_unique(array_map('intval', $mediaIds)));
        $this->product = $this->products->save($data, $this->product);
        $this->selectedMediaIds = $this->product->media()->orderBy('sort_order')->pluck('media_asset_id')->filter()->map(fn ($id) => (int) $id)->all();
        $this->reset('image');
        session()->flash('status', 'Product saved.');
        $this->redirect(route('admin.catalog.products'));
    }

    #[On('media-selected')]
    public function selectMedia(int $id, ?string $url = null, ?string $context = null): void
    {
        if ($context !== 'product-gallery') {
            return;
        }

        $asset = MediaAsset::findOrFail($id);
        Gate::authorize('view', $asset);

        if (! in_array($asset->id, $this->selectedMediaIds, true)) {
            $this->selectedMediaIds[] = $asset->id;
        }
    }

    public function removeMedia(int $id): void
    {
        $this->selectedMediaIds = array_values(array_filter($this->selectedMediaIds, fn ($mediaId) => (int) $mediaId !== $id));
    }

    public function removeImageUpload(): void
    {
        $this->reset('image');
        $this->resetValidation('image');
    }

    public function moveMedia(int $index, int $direction): void
    {
        $target = $index + $direction;
        if (! isset($this->selectedMediaIds[$target])) {
            return;
        }

        [$this->selectedMediaIds[$index], $this->selectedMediaIds[$target]] = [$this->selectedMediaIds[$target], $this->selectedMediaIds[$index]];
        $this->persistMediaOrder();
    }

    public function sortMedia(string|int $item, int $position): void
    {
        $this->authorize('update', $this->product);
        $item = (int) $item;
        $currentPosition = array_search($item, $this->selectedMediaIds, true);

        if ($currentPosition === false || $position < 0 || $position >= count($this->selectedMediaIds)) {
            return;
        }

        array_splice($this->selectedMediaIds, $currentPosition, 1);
        array_splice($this->selectedMediaIds, $position, 0, [$item]);
        $this->persistMediaOrder();
    }

    private function persistMediaOrder(): void
    {
        $this->authorize('update', $this->product);

        foreach ($this->selectedMediaIds as $sortOrder => $mediaId) {
            $this->product->media()->where('media_asset_id', $mediaId)->update([
                'sort_order' => $sortOrder,
                'role' => $sortOrder === 0 ? 'main' : 'gallery',
            ]);
        }
    }

    private function formatPrice(int $minor): string
    {
        return number_format($minor / 100, 2, '.', '');
    }

    private function formatNullablePrice(?int $minor): ?string
    {
        return $minor === null ? null : $this->formatPrice($minor);
    }

    private function priceToMinor(?string $price): ?int
    {
        if ($price === null || trim($price) === '') {
            return null;
        }

        [$whole, $fraction] = array_pad(explode('.', trim($price), 2), 2, '');

        return ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
    }

    public function render()
    {
        $this->authorize('viewAny', Product::class);

        return view('livewire.pages.admin.catalog.products.edit', [
            'brands' => Brand::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
            /* 'attributes' => Attribute::active()->with('values')->orderBy('sort_order')->orderBy('name')->get(), */
            'mediaAssets' => MediaAsset::query()->latest()->limit(20)->get(),
            'selectedMedia' => $this->selectedMediaIds === [] ? collect() : MediaAsset::query()->whereKey($this->selectedMediaIds)->get()->sortBy(fn ($asset) => array_search($asset->id, $this->selectedMediaIds, true))->values(),
            'variantMediaGroups' => $this->product?->exists
                ? $this->product->variants()
                    ->with('media.asset')
                    ->orderByDesc('is_default')
                    ->orderBy('sort_order')
                    ->get()
                : collect(),
        ]);
    }
}
