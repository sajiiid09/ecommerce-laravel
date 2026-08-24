<?php

namespace App\Livewire\Pages\Admin\Catalog\Products;

use App\Models\{Brand, Category, Product};
use App\Services\{MediaService, ProductService};
use Livewire\Attributes\Layout; use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class Edit extends Component
{
    use WithFileUploads;
    public ?Product $product = null;
    public $image;
    public array $description_json = [];
    public string $name = '';
    public string $slug = '';
    public string $product_type = 'simple';
    public string $status = 'draft';
    public string $visibility = 'visible';
    public ?int $brand_id = null;
    public ?int $primary_category_id = null;
    public string $short_description = '';
    public string $description_html = '';
    public int $regular_price_minor = 0;
    public ?int $sale_price_minor = null;
    public ?int $cost_price_minor = null;
    public bool $is_featured = false;

    public function mount(?Product $product = null): void
    {
        if (! $product?->exists) return;
        $this->product = $product->load('defaultVariant');
        $this->fill($product->only(['name', 'slug', 'brand_id', 'primary_category_id', 'short_description', 'description_html', 'is_featured']));
        $this->product_type = $product->product_type?->value ?? 'simple';
        $this->status = $product->status?->value ?? 'draft';
        $this->visibility = $product->visibility?->value ?? 'visible';
        $this->description_json = $product->description_json ?? [];
        $this->regular_price_minor = (int) ($product->defaultVariant?->regular_price_minor ?? 0);
        $this->sale_price_minor = $product->defaultVariant?->sale_price_minor;
        $this->cost_price_minor = $product->defaultVariant?->cost_price_minor;
    }

    public function saveProduct(): void
    {
        $data = $this->validate([
            'name' => 'required|string|max:255', 'slug' => 'nullable|string|max:255', 'product_type' => 'required|in:simple,variable',
            'status' => 'required|in:draft,published,archived', 'visibility' => 'required|string', 'brand_id' => 'nullable|integer',
            'primary_category_id' => 'nullable|integer', 'regular_price_minor' => 'required|integer|min:0', 'sale_price_minor' => 'nullable|integer|min:0',
            'cost_price_minor' => 'nullable|integer|min:0', 'image' => 'nullable|image|max:5120',
        ]);
        $data['is_featured'] = $this->is_featured;
        $data['description_json'] = $this->description_json;
        $data['description_html'] = $this->description_html;
        $this->product = app(ProductService::class)->save($data, $this->product);
        if ($this->image) {
            $asset = app(MediaService::class)->upload($this->image);
            $this->product->media()->create(['media_asset_id' => $asset->id, 'role' => 'main', 'sort_order' => 0]);
        }
        session()->flash('status', 'Product saved.');
        $this->redirect('/admin/catalog/products');
    }

    public function render()
    {
        return view('livewire.pages.admin.catalog.products.edit', ['brands' => Brand::orderBy('name')->get(), 'categories' => Category::orderBy('name')->get()]);
    }
}
