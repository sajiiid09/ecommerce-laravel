<?php

use App\Livewire\Pages\Admin\Catalog\Attributes\Index as AttributesIndex;
use App\Livewire\Pages\Admin\Catalog\Brands\Index as BrandsIndex;
use App\Livewire\Pages\Admin\Catalog\Categories\Index as CategoriesIndex;
use App\Livewire\Pages\Admin\Catalog\Inventory\Index as InventoryIndex;
use App\Livewire\Pages\Admin\Catalog\Products\Edit as ProductEdit;
use App\Livewire\Pages\Admin\Catalog\Products\Index as ProductsIndex;
use App\Livewire\Pages\Admin\Catalog\Products\Variants as ProductVariantsIndex;
use App\Livewire\Pages\Admin\Catalog\Tags\Index as TagsIndex;
use App\Livewire\Pages\Admin\Catalog\Variants\Index as VariantsIndex;
use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\MediaAsset;
use App\Models\MediaUsage;
use App\Models\Product;
use App\Models\ProductVariant;
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

it('renders the product edit choices with Sheaf selects', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    $category = Category::create(['name' => 'Edit Category', 'slug' => 'edit-category', 'is_active' => true]);
    $brand = Brand::create(['name' => 'Edit Brand', 'slug' => 'edit-brand', 'is_active' => true]);
    $tag = Tag::create(['name' => 'Edit Tag', 'slug' => 'edit-tag', 'is_active' => true]);
    $attribute = Attribute::create(['name' => 'Edit Color', 'slug' => 'edit-color', 'type' => 'select', 'is_active' => true]);
    $attribute->values()->create(['value' => 'Black', 'slug' => 'black']);
    $product = app(ProductService::class)->save([
        'name' => 'Editable Product',
        'product_type' => 'simple',
        'status' => 'published',
        'visibility' => 'visible',
        'brand_id' => $brand->id,
        'primary_category_id' => $category->id,
        'category_ids' => [$category->id],
        'tag_ids' => [$tag->id],
    ]);

    Livewire::test(ProductEdit::class, ['product' => $product])
        ->assertSee('data-slot="select-control"', false)
        ->assertSee('Select additional categories', false)
        ->assertSee('Select tags', false)
        ->assertSee('rounded-lg', false)
        ->assertDontSee('<select', false)
        ->assertSee('data-slot="option"', false);
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
        ->assertSee('wire:model="selectedIds"', false)
        ->assertSee('aria-label="Select all visible rows"', false)
        ->assertSee('aria-label="Rows per page"', false)
        ->assertSee('<option value="15">15</option>', false)
        ->assertDontSee('153050', false);
});

it('renders every requested catalog list through the Sheaf table', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    $product = Product::create([
        'name' => 'Table Test Product',
        'slug' => 'table-test-product',
        'product_type' => 'simple',
        'status' => 'published',
        'visibility' => 'visible',
    ]);

    foreach ([
        BrandsIndex::class,
        CategoriesIndex::class,
        TagsIndex::class,
        AttributesIndex::class,
        ProductsIndex::class,
        InventoryIndex::class,
        VariantsIndex::class,
    ] as $component) {
        Livewire::test($component)
            ->assertSee('<table', false)
            ->assertSee('wire:loading', false)
            ->assertDontSee('102550', false);
    }

    Livewire::test(ProductVariantsIndex::class, ['product' => $product])
        ->assertSee('<table', false)
        ->assertSee('Generated variants', false)
        ->assertDontSee('<table class="min-w-full text-left text-sm">', false);
});

it('renders the products table with compact responsive columns and full pagination', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));

    foreach (range(1, 16) as $index) {
        Product::create([
            'name' => "Responsive Product {$index}",
            'slug' => "responsive-product-{$index}",
            'product_type' => 'simple',
            'status' => 'published',
            'visibility' => 'visible',
        ]);
    }

    Livewire::test(ProductsIndex::class)
        ->assertSee('Pagination Navigation', false)
        ->assertSee('Items per page', false)
        ->assertSee('md:table-cell', false)
        ->assertSee('truncate', false)
        ->assertSee('text-[11px] font-bold', false)
        ->assertDontSee('uppercase', false)
        ->assertSee('Product', false)
        ->assertSee('SKU', false)
        ->assertSee('Category', false)
        ->assertSee('Brand', false)
        ->assertSee('Price', false)
        ->assertSee('Stock', false)
        ->assertSee('Status', false)
        ->assertSee('Actions', false);
});

it('filters products by name, slug, and variant SKU through the catalog table state', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    $matching = Product::create([
        'name' => 'Searchable Product',
        'slug' => 'searchable-product',
        'product_type' => 'simple',
        'status' => 'published',
        'visibility' => 'visible',
    ]);
    $matching->variants()->create([
        'sku' => 'SKU-SEARCH-42',
        'regular_price_minor' => 1000,
        'is_default' => true,
        'is_active' => true,
    ]);
    Product::create([
        'name' => 'Other Product',
        'slug' => 'other-product',
        'product_type' => 'simple',
        'status' => 'published',
        'visibility' => 'visible',
    ]);

    Livewire::test(ProductsIndex::class)
        ->set('searchQuery', 'SKU-SEARCH-42')
        ->assertSee('Searchable Product', false)
        ->assertDontSee('Other Product', false);
});

it('keeps product status, category, and brand filters functional and resettable', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    $category = Category::create(['name' => 'Filtered Category', 'slug' => 'filtered-category', 'is_active' => true]);
    $brand = Brand::create(['name' => 'Filtered Brand', 'slug' => 'filtered-brand', 'is_active' => true]);

    Product::create([
        'name' => 'Filtered Product',
        'slug' => 'filtered-product',
        'product_type' => 'simple',
        'primary_category_id' => $category->id,
        'brand_id' => $brand->id,
        'status' => 'published',
        'visibility' => 'visible',
    ]);
    Product::create([
        'name' => 'Unfiltered Product',
        'slug' => 'unfiltered-product',
        'product_type' => 'simple',
        'status' => 'draft',
        'visibility' => 'visible',
    ]);

    Livewire::test(ProductsIndex::class)
        ->call('toggleFilters')
        ->assertSet('filtersOpen', true)
        ->set('status', 'published')
        ->set('categoryFilter', $category->id)
        ->set('brandFilter', $brand->id)
        ->assertSee('Filtered Product', false)
        ->assertDontSee('Unfiltered Product', false)
        ->call('resetFilters')
        ->assertSet('status', '')
        ->assertSet('categoryFilter', null)
        ->assertSet('brandFilter', null)
        ->assertSee('Filtered Product', false)
        ->assertSee('Unfiltered Product', false);
});

it('renders readable catalog controls and symbols without mojibake', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    $product = Product::create([
        'name' => 'Readable Catalog Product',
        'slug' => 'readable-catalog-product',
        'product_type' => 'simple',
        'status' => 'published',
        'visibility' => 'visible',
    ]);
    $product->variants()->create([
        'sku' => 'READABLE-1',
        'regular_price_minor' => 1250,
        'is_default' => true,
        'is_active' => true,
    ]);

    foreach ([BrandsIndex::class, TagsIndex::class, ProductsIndex::class, InventoryIndex::class, VariantsIndex::class, ProductVariantsIndex::class] as $component) {
        $test = $component === ProductVariantsIndex::class
            ? Livewire::test($component, ['product' => $product])
            : Livewire::test($component);

        $test->assertDontSee('Ã', false)
            ->assertDontSee('Â', false)
            ->assertDontSee('â', false);
    }

    Livewire::test(ProductsIndex::class)
        ->assertSee('&#2547;', false)
        ->assertSee(html_entity_decode('&mdash;'), false)
        ->assertSee('&rarr;', false)
        ->assertDontSee('Ã', false)
        ->assertDontSee('Â', false)
        ->assertDontSee('â', false);

});

it('renders related products in a five-card carousel without dots', function () {
    Cache::flush();
    $category = Category::create(['name' => 'Related Products', 'slug' => 'related-products', 'is_active' => true]);

    $createProduct = function (string $name, int $index) use ($category): Product {
        $product = Product::create([
            'name' => $name,
            'slug' => str()->slug($name),
            'product_type' => 'simple',
            'primary_category_id' => $category->id,
            'short_description' => 'Related product description.',
            'status' => 'published',
            'visibility' => 'visible',
            'is_featured' => false,
            'taxable' => false,
            'is_indexable' => true,
            'published_at' => now(),
        ]);
        $product->categories()->attach($category);
        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => "RELATED-{$index}",
            'name' => 'Default',
            'combination_key' => 'default',
            'regular_price_minor' => 1000 + $index,
            'is_active' => true,
            'is_default' => true,
        ]);

        return $product;
    };

    $product = $createProduct('Main Related Product', 0);
    foreach (range(1, 6) as $index) {
        $createProduct("Related Product {$index}", $index);
    }

    $response = $this->get(route('store.product', ['slug' => $product->slug]))->assertSuccessful();
    $content = $response->getContent();

    expect(substr_count($content, 'aria-roledescription="carousel"'))->toBe(1)
        ->and($content)->toContain('aria-label="Related products"')
        ->and($content)->toContain('xl:basis-[calc((100%-3rem)/5)]')
        ->and($content)->toContain('Related Product 1')
        ->and($content)->toContain('View All')
        ->and($content)->not->toContain('role="tablist"');
});
