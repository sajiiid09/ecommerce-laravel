<?php

use App\Livewire\Pages\Admin\Catalog\Attributes\Index as AttributesIndex;
use App\Livewire\Pages\Admin\Catalog\Brands\Index as BrandsIndex;
use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\MediaAsset;
use App\Models\MediaUsage;
use App\Models\Tag;
use App\Models\User;
use App\Services\CatalogQueryService;
use App\Services\CategoryService;
use App\Services\InventoryService;
use App\Services\ProductService;
use App\Services\ProductVariantService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('persists product taxonomy, SEO, attributes, and rich text', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    $category = Category::create(['name' => 'Audio', 'slug' => 'audio', 'is_active' => true]);
    $secondaryCategory = Category::create(['name' => 'Electronics', 'slug' => 'electronics', 'is_active' => true]);
    $brand = Brand::create(['name' => 'SoundMax', 'slug' => 'soundmax', 'is_active' => true]);
    $tag = Tag::create(['name' => 'Premium', 'slug' => 'premium', 'is_active' => true]);
    $attribute = Attribute::create([
        'name' => 'Color',
        'slug' => 'color',
        'type' => 'select',
        'is_active' => true,
    ]);
    $attributeValue = $attribute->values()->create(['value' => 'Black', 'slug' => 'black']);

    $product = app(ProductService::class)->save([
        'name' => 'Studio Headphones',
        'slug' => 'studio-headphones',
        'product_type' => 'simple',
        'status' => 'published',
        'visibility' => 'visible',
        'brand_id' => $brand->id,
        'primary_category_id' => $category->id,
        'category_ids' => [$category->id, $secondaryCategory->id],
        'tag_ids' => [$tag->id],
        'attribute_values' => [$attribute->id => $attributeValue->id],
        'description_html' => '<p>Detailed <strong>headphones</strong></p><script>alert(1)</script>',
        'meta_title' => 'Studio Headphones | StoreZ',
        'meta_description' => 'Buy studio headphones from StoreZ.',
        'canonical_url' => 'https://storez.test/products/studio-headphones',
        'regular_price_minor' => 9999,
    ]);

    expect($product->meta_title)->toBe('Studio Headphones | StoreZ')
        ->and($product->description_html)->toContain('<strong>headphones</strong>')
        ->and($product->description_html)->not->toContain('<script>')
        ->and($product->categories()->pluck('categories.id')->all())->toEqualCanonicalizing([$category->id, $secondaryCategory->id])
        ->and($product->tags()->pluck('tags.id')->all())->toBe([$tag->id])
        ->and($product->attributeValues()->first()->attribute_value_id)->toBe($attributeValue->id);
});

it('maps real variant options and variant media for the storefront', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin);
    $product = app(ProductService::class)->save([
        'name' => 'Variable Shirt',
        'product_type' => 'variable',
        'status' => 'published',
        'visibility' => 'visible',
    ]);
    $option = $product->options()->create(['name' => 'Color', 'slug' => 'color']);
    $value = $option->values()->create(['value' => 'Black', 'slug' => 'black']);
    $variant = app(ProductVariantService::class)->generate($product)[0];
    $asset = MediaAsset::create([
        'disk' => 'public',
        'path' => 'media/black-shirt.jpg',
        'filename' => 'black-shirt.jpg',
        'mime_type' => 'image/jpeg',
        'size' => 100,
    ]);

    app(ProductVariantService::class)->save($variant, [
        'sku' => 'SHIRT-BLACK',
        'regular_price_minor' => 2499,
        'quantity_on_hand' => 4,
        'low_stock_threshold' => 1,
        'media_ids' => [$asset->id],
    ]);

    $mapped = app(CatalogQueryService::class)->product($product->slug);

    expect($mapped['variants'][0]['optionValues'][0]['value'])->toBe($value->slug)
        ->and($mapped['variants'][0]['gallery'])->toHaveCount(1)
        ->and($mapped['variants'][0]['sku'])->toBe('SHIRT-BLACK')
        ->and(MediaUsage::where('media_asset_id', $asset->id)->where('role', 'variant.image')->exists())->toBeTrue();
});

it('protects inventory reservations and releases with audited movements', function () {
    $product = app(ProductService::class)->save([
        'name' => 'Stocked Lamp',
        'product_type' => 'simple',
        'status' => 'draft',
        'regular_price_minor' => 1000,
        'inventory_quantity' => 3,
    ]);
    $inventory = app(InventoryService::class);

    $inventory->reserve($product->defaultVariant, 2);
    expect(fn () => $inventory->reserve($product->defaultVariant, 2))
        ->toThrow(InvalidArgumentException::class, 'Insufficient stock.');

    $inventory->release($product->defaultVariant, 1);

    expect($product->defaultVariant->fresh('inventory')->inventory->quantity_reserved)->toBe(1)
        ->and($product->defaultVariant->inventory->movements()->whereIn('type', ['reservation', 'release'])->count())->toBe(2);
});

test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

it('persists product gallery order and detaches replaced media usage', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    $product = app(ProductService::class)->save(['name' => 'Gallery Lamp', 'product_type' => 'simple', 'status' => 'draft']);
    $first = MediaAsset::create(['disk' => 'public', 'path' => 'media/first.jpg', 'filename' => 'first.jpg', 'mime_type' => 'image/jpeg', 'size' => 100]);
    $second = MediaAsset::create(['disk' => 'public', 'path' => 'media/second.jpg', 'filename' => 'second.jpg', 'mime_type' => 'image/jpeg', 'size' => 100]);

    app(ProductService::class)->save(['name' => 'Gallery Lamp', 'product_type' => 'simple', 'status' => 'draft', 'media_ids' => [$first->id, $second->id]], $product);
    app(ProductService::class)->save(['name' => 'Gallery Lamp', 'product_type' => 'simple', 'status' => 'draft', 'media_ids' => [$second->id]], $product);

    expect($product->fresh()->media()->pluck('media_asset_id')->all())->toBe([$second->id])
        ->and(MediaUsage::where('media_asset_id', $first->id)->where('usable_id', $product->id)->exists())->toBeFalse()
        ->and(MediaUsage::where('media_asset_id', $second->id)->where('usable_id', $product->id)->exists())->toBeTrue();
});

it('rejects inactive and invalid attribute assignments', function () {
    $attribute = Attribute::create(['name' => 'Material', 'slug' => 'material', 'type' => 'text', 'is_active' => false]);

    expect(fn () => app(ProductService::class)->save([
        'name' => 'Inactive Attribute Product',
        'product_type' => 'simple',
        'status' => 'draft',
        'attribute_values' => [$attribute->id => 'Cotton'],
    ]))->toThrow(InvalidArgumentException::class, 'attribute is inactive');
});

it('invalidates catalog option caches after category changes', function () {
    Cache::put('catalog:category-options', [['name' => 'Stale']], 300);
    app(CategoryService::class)->save(['name' => 'Fresh Category', 'slug' => 'fresh-category', 'is_active' => true]);

    expect(Cache::has('catalog:category-options'))->toBeFalse();
});

it('validates and persists type-aware catalog attributes', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));

    Livewire::test(AttributesIndex::class)
        ->set('name', 'Finish')
        ->set('slug', 'finish')
        ->set('type', 'select')
        ->set('values_text', "Matte\nGlossy")
        ->call('saveAttribute')
        ->assertHasNoErrors();

    $attribute = Attribute::where('slug', 'finish')->firstOrFail();
    expect($attribute->type)->toBe('select')
        ->and($attribute->values()->pluck('value')->all())->toEqualCanonicalizing(['Matte', 'Glossy']);

    Livewire::test(AttributesIndex::class)
        ->set('name', 'Invalid Finish')
        ->set('slug', 'invalid-finish')
        ->set('type', 'multi_select')
        ->set('values_text', "Matte\nmatte")
        ->call('saveAttribute')
        ->assertHasErrors('values_text');
});

it('opens the brand editor, updates a brand, and links to its storefront page', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    $brand = Brand::create([
        'name' => 'SoundMax',
        'slug' => 'soundmax',
        'description' => 'Original description',
        'is_active' => true,
        'is_featured' => false,
        'sort_order' => 0,
    ]);

    Livewire::test(BrandsIndex::class)
        ->call('openEdit', $brand->id)
        ->assertSet('editingId', $brand->id)
        ->assertSet('editName', 'SoundMax')
        ->assertSet('editSlug', 'soundmax')
        ->assertDispatched('open-modal', id: 'resource-edit')
        ->set('editName', 'SoundMax Pro')
        ->set('editSlug', 'soundmax-pro')
        ->set('editDescription', 'Updated description')
        ->set('editIsFeatured', true)
        ->call('updateRecord')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal', id: 'resource-edit')
        ->assertSee(route('store.brand', ['slug' => 'soundmax-pro']), false);

    expect($brand->fresh()->only(['name', 'slug', 'description', 'is_featured']))->toMatchArray([
        'name' => 'SoundMax Pro',
        'slug' => 'soundmax-pro',
        'description' => 'Updated description',
        'is_featured' => true,
    ]);
});

it('renders Sheaf selection controls and a readable page-size selector on the brand table', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    Brand::create(['name' => 'SoundMax', 'slug' => 'soundmax', 'is_active' => true]);

    Livewire::test(BrandsIndex::class)
        ->assertSee('data-slot="checkbox-wrapper"', false)
        ->assertSee('aria-label="Select all brands on this page"', false)
        ->assertSee('aria-label="Rows per page"', false)
        ->assertSee('<option value="15">15</option>', false)
        ->assertDontSee('153050', false);
});
