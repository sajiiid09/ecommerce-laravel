<?php

namespace App\Livewire\Pages\Admin\Catalog\Products;

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

    public ?int $brand_id = null;

    public ?int $primary_category_id = null;

    public array $category_ids = [];

    public array $tag_ids = [];

    public array $selectedMediaIds = [];

    public ?string $short_description = '';

    public ?string $description_html = '';

    public int $regular_price_minor = 0;

    public ?int $sale_price_minor = null;

    public ?int $cost_price_minor = null;

    public ?int $compare_at_price_minor = null;

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
        $this->fill($product->only(['name', 'slug', 'brand_id', 'primary_category_id', 'short_description', 'description_html', 'is_featured']));
        $this->category_ids = $product->categories()->pluck('categories.id')->all();
        $this->tag_ids = $product->tags()->pluck('tags.id')->all();
        $this->selectedMediaIds = $product->media()->orderBy('sort_order')->pluck('media_asset_id')->filter()->map(fn ($id) => (int) $id)->all();
        $this->product_type = $product->product_type?->value ?? 'simple';
        $this->status = $product->status?->value ?? 'draft';
        $this->visibility = $product->visibility?->value ?? 'visible';
        $this->description_json = $product->description_json ?? [];
        $this->regular_price_minor = (int) ($product->defaultVariant?->regular_price_minor ?? 0);
        $this->sale_price_minor = $product->defaultVariant?->sale_price_minor;
        $this->cost_price_minor = $product->defaultVariant?->cost_price_minor;
        $this->compare_at_price_minor = $product->defaultVariant?->compare_at_price_minor;
        $inventory = $product->defaultVariant?->inventory;
        $this->inventory_quantity = (int) ($inventory?->quantity_on_hand ?? 0);
        $this->low_stock_threshold = (int) ($inventory?->low_stock_threshold ?? 10);
        $this->track_quantity = (bool) ($inventory?->track_quantity ?? true);
        $this->allow_backorders = (bool) ($inventory?->allow_backorders ?? false);
    }

    public function saveProduct(): void
    {
        $this->authorize($this->product?->exists ? 'update' : 'create', $this->product?->exists ? $this->product : Product::class);
        $data = $this->validate([
            'name' => 'required|string|max:255', 'slug' => 'nullable|string|max:255', 'product_type' => 'required|in:simple,variable',
            'status' => 'required|in:draft,published,archived', 'visibility' => 'required|string', 'brand_id' => 'nullable|exists:brands,id',
            'primary_category_id' => 'nullable|exists:categories,id', 'category_ids' => 'array', 'category_ids.*' => 'integer|exists:categories,id',
            'tag_ids' => 'array', 'tag_ids.*' => 'integer|exists:tags,id', 'regular_price_minor' => 'required|integer|min:0', 'sale_price_minor' => 'nullable|integer|min:0',
            'compare_at_price_minor' => 'nullable|integer|min:0', 'cost_price_minor' => 'nullable|integer|min:0', 'inventory_quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:0', 'track_quantity' => 'boolean', 'allow_backorders' => 'boolean', 'image' => 'nullable|image|max:5120',
        ]);
        $data['is_featured'] = $this->is_featured;
        $data['description_json'] = $this->description_json;
        $data['description_html'] = $this->description_html;
        $data['category_ids'] = array_values(array_unique(array_map('intval', $this->category_ids ?: array_filter([$this->primary_category_id]))));
        $data['tag_ids'] = array_values(array_unique(array_map('intval', $this->tag_ids)));
        $mediaIds = $this->selectedMediaIds;
        if ($this->image) {
            $mediaIds[] = $this->media->upload($this->image, 'products')->id;
        }
        $data['media_ids'] = array_values(array_unique(array_map('intval', $mediaIds)));
        $this->product = $this->products->save($data, $this->product);
        $this->selectedMediaIds = $this->product->media()->orderBy('sort_order')->pluck('media_asset_id')->filter()->map(fn ($id) => (int) $id)->all();
        $this->reset('image');
        session()->flash('status', 'Product saved.');
        $this->redirect('/admin/catalog/products');
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

    public function moveMedia(int $index, int $direction): void
    {
        $target = $index + $direction;
        if (! isset($this->selectedMediaIds[$target])) {
            return;
        }

        [$this->selectedMediaIds[$index], $this->selectedMediaIds[$target]] = [$this->selectedMediaIds[$target], $this->selectedMediaIds[$index]];
    }

    public function render()
    {
        $this->authorize('viewAny', Product::class);

        return view('livewire.pages.admin.catalog.products.edit', [
            'brands' => Brand::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
            'mediaAssets' => MediaAsset::query()->latest()->limit(20)->get(),
            'selectedMedia' => $this->selectedMediaIds === [] ? collect() : MediaAsset::query()->whereKey($this->selectedMediaIds)->get()->sortBy(fn ($asset) => array_search($asset->id, $this->selectedMediaIds, true))->values(),
        ]);
    }
}
