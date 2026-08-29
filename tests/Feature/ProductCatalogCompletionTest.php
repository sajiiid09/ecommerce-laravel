<?php

use App\Livewire\Components\Store\RelatedProducts;
use App\Livewire\Pages\Admin\Catalog\Attributes\Index as AttributesIndex;
use App\Livewire\Pages\Admin\Catalog\Brands\Index as BrandsIndex;
use App\Livewire\Pages\Admin\Catalog\Categories\Index as CategoriesIndex;
use App\Livewire\Pages\Admin\Catalog\Inventory\Index as InventoryIndex;
use App\Livewire\Pages\Admin\Catalog\Products\Edit as ProductEdit;
use App\Livewire\Pages\Admin\Catalog\Products\Index as ProductsIndex;
use App\Livewire\Pages\Admin\Catalog\Products\Variants as ProductVariantsIndex;
use App\Livewire\Pages\Admin\Catalog\Tags\Index as TagsIndex;
use App\Livewire\Pages\Admin\Catalog\Variants\Index as VariantsIndex;
use App\Livewire\Pages\Store\Category as StoreCategory;
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
use Database\Seeders\CatalogSeeder;
use Database\Seeders\ProductSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('queries homepage product sections by source, filters, sort, and limit', function () {
    Cache::flush();
    $category = Category::create(['name' => 'Homepage Audio', 'slug' => 'homepage-audio', 'is_active' => true]);
    $brand = Brand::create(['name' => 'Homepage Brand', 'slug' => 'homepage-brand', 'is_active' => true]);
    $otherCategory = Category::create(['name' => 'Homepage Other Category', 'slug' => 'homepage-other-category', 'is_active' => true]);
    $otherBrand = Brand::create(['name' => 'Homepage Other Brand', 'slug' => 'homepage-other-brand', 'is_active' => true]);

    $makeProduct = function (string $name, array $attributes = [], int $price = 1000, ?int $salePrice = null) use ($category, $brand): Product {
        $categoryId = $attributes['category_id'] ?? $category->id;
        unset($attributes['category_id']);
        $product = Product::create(array_merge([
            'name' => $name,
            'slug' => str()->slug($name),
            'product_type' => 'simple',
            'brand_id' => $brand->id,
            'primary_category_id' => $category->id,
            'status' => 'published',
            'visibility' => 'visible',
            'is_featured' => false,
            'published_at' => now(),
        ], $attributes));
        $product->categories()->attach($categoryId);
        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'HOME-'.$product->id,
            'name' => 'Default',
            'combination_key' => 'default',
            'regular_price_minor' => $price,
            'sale_price_minor' => $salePrice,
            'is_active' => true,
            'is_default' => true,
        ]);

        return $product;
    };

    $featured = $makeProduct('Homepage Featured', ['is_featured' => true], 1500);
    $newest = $makeProduct('Homepage Newest', ['published_at' => now()->addMinute()], 1200);
    $sale = $makeProduct('Homepage Sale', [], 2000, 1600);
    $brandProduct = $makeProduct('Homepage Brand Product', [], 1800);
    $cheap = $makeProduct('Homepage Cheap', ['is_featured' => true], 500);
    $hidden = $makeProduct('Homepage Hidden', ['status' => 'draft', 'is_featured' => true], 100);
    $outside = $makeProduct('Homepage Outside Filters', [
        'brand_id' => $otherBrand->id,
        'primary_category_id' => $otherCategory->id,
        'category_id' => $otherCategory->id,
    ], 2200);

    $ids = fn (array $settings): array => collect(app(CatalogQueryService::class)->homepageProducts($settings))
        ->pluck('id')
        ->all();

    expect($ids(['source' => 'featured', 'limit' => 24]))->toContain($featured->id)
        ->and($ids(['source' => 'newest', 'limit' => 24]))->toContain($newest->id)
        ->and($ids(['source' => 'bestsellers', 'limit' => 24]))->toContain($brandProduct->id)->not->toContain($hidden->id)
        ->and($ids(['source' => 'on_sale', 'limit' => 24]))->toEqual([$sale->id])
        ->and($ids(['source' => 'category', 'category' => $category->slug, 'limit' => 24]))
        ->toContain($brandProduct->id)->not->toContain($outside->id)
        ->and($ids(['source' => 'brand', 'brand' => $brand->slug, 'limit' => 24]))
        ->toContain($brandProduct->id)->not->toContain($outside->id)
        ->and($ids(['source' => 'featured', 'sort' => 'price_asc', 'limit' => 1]))->toEqual([$cheap->id]);
});

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

it('renders the product edit choices with Sheaf selects and a categories combobox', function () {
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
        'regular_price_minor' => 1099000,
        'sale_price_minor' => 999900,
        'compare_at_price_minor' => 1199000,
        'cost_price_minor' => 800000,
    ]);

    Livewire::test(ProductEdit::class, ['product' => $product])
        ->assertSee('data-slot="combobox-control"', false)
        ->assertSee('data-slot="combobox-input"', false)
        ->assertSee('wire:model="category_ids"', false)
        ->assertSeeInOrder(['Product Name', 'Slug', 'Product Type', 'Categories'])
        ->assertSee('Select categories', false)
        ->assertSee('Select tags', false)
        ->assertSee('rounded-lg', false)
        ->assertSet('regular_price', '10990.00')
        ->assertSet('sale_price', '9999.00')
        ->assertSee('wire:model="regular_price"', false)
        ->assertSee('wire:model="brand_id"', false)
        ->assertSee('wire:model="status"', false)
        ->assertSee('wire:model="visibility"', false)
        ->assertDontSee('wire:model.live', false)
        ->assertSee('Slug <span class="text-xs font-normal text-[#9ca3af]">(optional)</span>', false)
        ->assertSee('Enter product slug (optional)', false)
        ->assertDontSee('generateSlug', false)
        ->assertSee('x-on:submit="flushSync()"', false)
        ->assertSee('pt-1', false)
        ->assertDontSee('Search Engine Optimization', false)
        ->assertDontSee('<select', false)
        ->assertSee('data-slot="option"', false);

    Livewire::test(ProductEdit::class, ['product' => $product])
        ->set('regular_price', '12990.00')
        ->set('sale_price', '11990.00')
        ->set('compare_at_price', '13990.00')
        ->set('cost_price', '8000.00')
        ->set('description_json', ['type' => 'doc', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Updated product content']]]]])
        ->set('description_html', '<p>Updated product content</p>')
        ->call('saveProduct');

    $savedVariant = $product->fresh(['defaultVariant'])->defaultVariant;

    expect($savedVariant->regular_price_minor)->toBe(1299000)
        ->and($savedVariant->sale_price_minor)->toBe(1199000)
        ->and($savedVariant->compare_at_price_minor)->toBe(1399000)
        ->and($savedVariant->cost_price_minor)->toBe(800000)
        ->and($product->fresh()->description_html)->toContain('Updated product content');
});

it('generates a product slug and allows an empty brand selection', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));

    Livewire::test(ProductEdit::class)
        ->set('name', 'Auto Slug Product')
        ->set('slug', '')
        ->set('brand_id', '')
        ->call('saveProduct');

    $product = Product::query()->where('slug', 'auto-slug-product')->firstOrFail();

    expect($product->brand_id)->toBeNull()
        ->and($product->slug)->toBe('auto-slug-product');
});

it('persists and clears multiple product categories through the deferred combobox binding', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    $firstCategory = Category::create(['name' => 'First Category', 'slug' => 'first-category', 'is_active' => true]);
    $secondCategory = Category::create(['name' => 'Second Category', 'slug' => 'second-category', 'is_active' => true]);
    $product = app(ProductService::class)->save([
        'name' => 'Category Binding Product',
        'product_type' => 'simple',
        'status' => 'draft',
        'visibility' => 'visible',
        'regular_price_minor' => 1000,
    ]);

    Livewire::test(ProductEdit::class, ['product' => $product])
        ->set('category_ids', [$firstCategory->id, $secondCategory->id])
        ->call('saveProduct');

    expect($product->fresh()->categories()->pluck('categories.id')->all())
        ->toEqualCanonicalizing([$firstCategory->id, $secondCategory->id]);

    Livewire::test(ProductEdit::class, ['product' => $product->fresh()])
        ->set('category_ids', [])
        ->call('saveProduct');

    expect($product->fresh()->categories()->pluck('categories.id')->all())->toBe([]);
});

it('renders ampersands once in combobox option labels', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    Category::create(['name' => 'Baby & Toys', 'slug' => 'baby-toys', 'is_active' => true]);

    Livewire::test(ProductEdit::class)
        ->assertSee('data-label="Baby &amp; Toys"', false)
        ->assertDontSee('Baby &amp;amp; Toys', false);
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

it('renders storefront category filters and pagination with Sheaf UI', function () {
    Cache::flush();
    $category = Category::create(['name' => 'Store Category', 'slug' => 'store-category', 'is_active' => true]);
    $brand = Brand::create(['name' => 'Store Brand', 'slug' => 'store-brand', 'is_active' => true]);

    foreach (range(1, 13) as $index) {
        $product = Product::create([
            'name' => "Category Product {$index}",
            'slug' => "category-product-{$index}",
            'product_type' => 'simple',
            'brand_id' => $brand->id,
            'primary_category_id' => $category->id,
            'status' => 'published',
            'visibility' => 'visible',
        ]);

        $product->categories()->attach($category);
    }

    Livewire::test(StoreCategory::class, ['slug' => $category->slug])
        ->assertSee('data-slot="checkbox-wrapper"', false)
        ->assertSee('data-slot="checkbox-indicator"', false)
        ->assertSee('name="brand[]"', false)
        ->assertSee('Pagination Navigation', false)
        ->assertSee('wire:click="gotoPage(2)"', false)
        ->call('gotoPage', 2)
        ->assertSee('Category Product 1', false);
});

it('formats storefront card prices from minor units', function () {
    Cache::flush();
    $category = Category::create(['name' => 'Pricing Category', 'slug' => 'pricing-category', 'is_active' => true]);
    $product = Product::create([
        'name' => 'Pricing Product',
        'slug' => 'pricing-product',
        'product_type' => 'simple',
        'primary_category_id' => $category->id,
        'status' => 'published',
        'visibility' => 'visible',
    ]);
    $product->categories()->attach($category);
    $product->variants()->create([
        'sku' => 'PRICE-1',
        'name' => 'Default',
        'combination_key' => 'default',
        'regular_price_minor' => 299000,
        'compare_at_price_minor' => 349000,
        'is_active' => true,
        'is_default' => true,
    ]);

    $currency = html_entity_decode('&#2547;');

    Livewire::test(StoreCategory::class, ['slug' => $category->slug])
        ->assertSee($currency.'2,990.00', false)
        ->assertDontSee($currency.'299,000.00', false);
});

it('renders the first product image as the admin product thumbnail', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    $asset = MediaAsset::create([
        'disk' => 'public',
        'path' => 'media/product-thumbnail.jpg',
        'filename' => 'product-thumbnail.jpg',
        'mime_type' => 'image/jpeg',
        'size' => 100,
    ]);
    $product = Product::create([
        'name' => 'Thumbnail Product',
        'slug' => 'thumbnail-product',
        'product_type' => 'simple',
        'status' => 'published',
        'visibility' => 'visible',
    ]);
    $product->media()->create([
        'media_asset_id' => $asset->id,
        'role' => 'primary',
        'sort_order' => 0,
    ]);

    Livewire::test(ProductsIndex::class)
        ->assertSee('<img', false)
        ->assertSee(Storage::disk('public')->url($asset->path), false)
        ->assertSee('Thumbnail Product', false);
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

it('renders the attributes datatable with dropdown actions without summary cards', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    Attribute::create([
        'name' => 'Material',
        'slug' => 'material',
        'type' => 'text',
        'is_active' => true,
    ]);

    Livewire::test(AttributesIndex::class)
        ->assertSee('<table', false)
        ->assertSee('wire:loading', false)
        ->assertSee('aria-label="Rows per page"', false)
        ->assertSee('Action', false)
        ->assertSee('Edit', false)
        ->assertSee('Deactivate', false)
        ->assertSee('Delete', false)
        ->assertSee('wire:click="openEdit(', false)
        ->assertSee('wire:click="toggleActive(', false)
        ->assertSee('wire:click="deleteAttribute(', false)
        ->assertDontSee('Total attributes', false)
        ->assertDontSee('With products', false);
});

it('renders the tags datatable full width with dropdown actions without side cards', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    Tag::create([
        'name' => 'New Arrival',
        'slug' => 'new-arrival',
        'type' => 'product',
        'is_active' => true,
    ]);

    Livewire::test(TagsIndex::class)
        ->assertSee('<table', false)
        ->assertSee('w-full overflow-hidden rounded-xl', false)
        ->assertSee('mb-2 flex flex-wrap items-center gap-2', false)
        ->assertSee('w-full table-fixed text-left', false)
        ->assertSee('<col class="w-[5%]">', false)
        ->assertSee('<col class="w-[25%]">', false)
        ->assertSee('Action', false)
        ->assertSee('Edit', false)
        ->assertSee('Deactivate', false)
        ->assertSee('Delete', false)
        ->assertSee('wire:click="openEdit(', false)
        ->assertSee('wire:click="toggleActive(', false)
        ->assertSee('wire:click="delete(', false)
        ->assertSee('wire:confirm="Delete this tag?"', false)
        ->assertDontSee('Total Tags', false)
        ->assertDontSee('Most Used Tags', false)
        ->assertDontSee('Tag Health', false)
        ->assertDontSee('xl:grid-cols-[minmax(0,1fr)_280px]', false);
});

it('renders the categories datatable full width with dropdown actions without summary cards', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    Category::create([
        'name' => 'Electronics',
        'slug' => 'electronics',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    Livewire::test(CategoriesIndex::class)
        ->assertSee('<table', false)
        ->assertSee('mb-2 flex flex-wrap items-center gap-2', false)
        ->assertSee('w-full table-fixed text-left', false)
        ->assertSee('<col class="w-[27%]">', false)
        ->assertSee('Action', false)
        ->assertSee('Edit', false)
        ->assertSee('Deactivate', false)
        ->assertSee('Delete', false)
        ->assertSee('wire:click="openEdit(', false)
        ->assertSee('wire:click="toggleStatus(', false)
        ->assertSee('wire:click="deleteCategory(', false)
        ->assertSee('wire:confirm="Delete this category?"', false)
        ->assertDontSee('Total Categories', false)
        ->assertDontSee('Category Overview', false)
        ->assertDontSee('Category Health', false)
        ->assertDontSee('xl:grid-cols-[minmax(0,1fr)_280px]', false);
});

it('renders the brands datatable full width with dropdown actions without side cards', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    Brand::create([
        'name' => 'Xiaomi',
        'slug' => 'xiaomi',
        'is_active' => true,
    ]);

    Livewire::test(BrandsIndex::class)
        ->assertSee('<table', false)
        ->assertSee('w-full overflow-hidden rounded-xl', false)
        ->assertSee('<col class="w-[5%]">', false)
        ->assertSee('Action', false)
        ->assertSee('Edit', false)
        ->assertSee('Deactivate', false)
        ->assertSee('Delete', false)
        ->assertSee('wire:click="openEdit(', false)
        ->assertSee('wire:click="toggleActive(', false)
        ->assertSee('wire:click="delete(', false)
        ->assertSee('wire:confirm="Delete this brand?"', false)
        ->assertDontSee('Total Brands', false)
        ->assertDontSee('Most Used Brands', false)
        ->assertDontSee('Brand Health', false)
        ->assertDontSee('xl:grid-cols-[minmax(0,1fr)_280px]', false);
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

it('renders the full-width inventory datatable while hiding summary cards', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));

    Livewire::test(InventoryIndex::class)
        ->assertSee('<table', false)
        ->assertSee('data-slot="select-control"', false)
        ->assertSee('aria-label="Rows per page"', false)
        ->assertDontSee('<select wire:model.live="perPage"', false)
        ->assertDontSee('Adjust stock', false)
        ->assertSee('w-full min-w-[980px] table-fixed text-left', false)
        ->assertSee('<col class="w-[34%]">', false)
        ->assertSee('<col class="w-[14%]">', false)
        ->assertSee('<col class="w-[10%]">', false)
        ->assertSee('<col class="w-[12%]">', false)
        ->assertDontSee('Total Units', false)
        ->assertDontSee('Inventory Value', false)
        ->assertDontSee('Inventory Alerts', false)
        ->assertDontSee('Recent Stock Movements', false)
        ->assertDontSee('Tracked units', false)
        ->assertDontSee('View details', false)
        ->assertDontSee('Ã', false)
        ->assertDontSee('â', false);
});

it('identifies the selected product in the inventory adjustment modal', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    $product = app(ProductService::class)->save([
        'name' => 'Adjustment Test Product',
        'product_type' => 'simple',
        'status' => 'published',
        'regular_price_minor' => 1000,
        'inventory_quantity' => 12,
    ]);

    Livewire::test(InventoryIndex::class)
        ->call('openAdjust', $product->defaultVariant->inventory->id)
        ->assertSee('Adjust stock', false)
        ->assertSee('Adjustment Test Product', false)
        ->assertSee('Add or remove units from this inventory item.', false);
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

it('renders the variants table full width without summary cards', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    $product = Product::create([
        'name' => 'Variant Layout Product',
        'slug' => 'variant-layout-product',
        'product_type' => 'simple',
        'status' => 'published',
        'visibility' => 'visible',
    ]);
    $product->variants()->create([
        'sku' => 'VARIANT-LAYOUT-1',
        'regular_price_minor' => 1000,
        'is_default' => true,
        'is_active' => true,
    ]);

    Livewire::test(VariantsIndex::class)
        ->assertSee('w-full min-w-[1200px] table-fixed text-left', false)
        ->assertSee('text-sm font-semibold text-[#111827]', false)
        ->assertSee('Options', false)
        ->assertSee('Actions', false)
        ->assertDontSee('<th>SKU</th>', false)
        ->assertDontSee('<th>Default</th>', false)
        ->assertDontSee('Total Variants', false)
        ->assertDontSee('Variant Summary', false)
        ->assertDontSee('Low Stock Variants', false)
        ->assertDontSee('xl:grid-cols-[minmax(0,1fr)_280px]', false);
});

it('renders variant actions and opens the selected variant details modal', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    $product = Product::create([
        'name' => 'Variant Actions Product',
        'slug' => 'variant-actions-product',
        'product_type' => 'simple',
        'status' => 'published',
        'visibility' => 'visible',
    ]);
    $variant = $product->variants()->create([
        'sku' => 'VARIANT-ACTIONS-1',
        'name' => 'Large / Blue',
        'regular_price_minor' => 249900,
        'is_default' => true,
        'is_active' => true,
    ]);

    $component = Livewire::test(VariantsIndex::class)
        ->assertSee('Actions for Variant Actions Product', false)
        ->assertSee('View', false)
        ->assertSee('Edit', false)
        ->assertSee('Delete', false)
        ->assertSee('wire:confirm="Delete this variant?"', false)
        ->assertSee(route('admin.catalog.products.variants', ['product' => $product->id]), false)
        ->call('showVariant', $variant->id);

    $component
        ->assertSee('Variant details', false)
        ->assertSee('VARIANT-ACTIONS-1', false)
        ->assertSee('Available stock', false)
        ->assertDispatched('open-modal', id: 'variant-details');
});

it('soft deletes a variant and promotes a replacement default', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    $product = Product::create([
        'name' => 'Variant Deletion Product',
        'slug' => 'variant-deletion-product',
        'product_type' => 'variable',
        'status' => 'published',
        'visibility' => 'visible',
    ]);
    $defaultVariant = $product->variants()->create([
        'sku' => 'VARIANT-DELETE-1',
        'combination_key' => 'delete-default',
        'regular_price_minor' => 1000,
        'is_default' => true,
        'is_active' => true,
    ]);
    $replacementVariant = $product->variants()->create([
        'sku' => 'VARIANT-DELETE-2',
        'combination_key' => 'delete-replacement',
        'regular_price_minor' => 1200,
        'is_default' => false,
        'is_active' => true,
    ]);

    Livewire::test(VariantsIndex::class)
        ->call('delete', $defaultVariant->id);

    expect(ProductVariant::withTrashed()->findOrFail($defaultVariant->id)->trashed())->toBeTrue()
        ->and($replacementVariant->fresh()->is_default)->toBeTrue();
});

it('keeps the last variant when deletion would leave a product without a variant', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    $product = Product::create([
        'name' => 'Last Variant Product',
        'slug' => 'last-variant-product',
        'product_type' => 'simple',
        'status' => 'published',
        'visibility' => 'visible',
    ]);
    $variant = $product->variants()->create([
        'sku' => 'VARIANT-LAST-1',
        'regular_price_minor' => 1000,
        'is_default' => true,
        'is_active' => true,
    ]);

    Livewire::test(VariantsIndex::class)
        ->call('delete', $variant->id)
        ->assertHasErrors(['variant' => 'A product must retain at least one variant.']);

    expect(ProductVariant::query()->whereKey($variant->id)->exists())->toBeTrue();
});

it('renders the products table with compact responsive columns and no summary cards', function () {
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
        ->assertSee('Actions', false)
        ->assertDontSee('mb-4 bg-white shadow-sm p-', false)
        ->assertDontSee('Total Products', false)
        ->assertDontSee('Active Products', false)
        ->assertDontSee('Low Stock Products', false)
        ->assertDontSee('Draft Products', false);
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
        ->assertDontSee('&rarr;', false)
        ->assertDontSee('Ã', false)
        ->assertDontSee('Â', false)
        ->assertDontSee('â', false);

});

it('lazy loads related products in a five-card carousel without dots', function () {
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

    expect($content)->toContain('aria-label="Loading related products"')
        ->and($content)->not->toContain('Related Product 1')
        ->and($content)->toContain('View All');

    Livewire::withoutLazyLoading();

    Livewire::test(RelatedProducts::class, [
        'productId' => $product->id,
        'categorySlug' => $category->slug,
    ])
        ->assertSee('aria-roledescription="carousel"', false)
        ->assertSee('aria-label="Related products"', false)
        ->assertSee('xl:basis-[calc((100%-3rem)/5)]', false)
        ->assertSee('Related Product 1', false)
        ->assertSee(html_entity_decode('&#2547;').'10.01', false)
        ->assertDontSee('role="tablist"', false);
});

it('processes catalog seed images into idempotent webp assets', function () {
    Storage::fake('public');

    $this->seed([CatalogSeeder::class, ProductSeeder::class]);

    $asset = MediaAsset::query()
        ->where('path', 'seeded/catalog/products/sony-wh-ch720n.webp')
        ->firstOrFail();
    $sonyWfC700n = Product::query()
        ->where('slug', 'sony-wf-c700n')
        ->with('primaryCategory')
        ->firstOrFail();
    $discountedProduct = Product::query()
        ->where('slug', 'redmi-watch-5-active')
        ->with('defaultVariant')
        ->firstOrFail();
    $fullPriceProduct = Product::query()
        ->where('slug', 'sony-wh-ch720n')
        ->with('defaultVariant')
        ->firstOrFail();
    $sonyGallery = $sonyWfC700n->media()
        ->whereNull('product_variant_id')
        ->orderBy('sort_order')
        ->get();
    $xiaomiPowerBank = Product::query()
        ->where('slug', 'xiaomi-power-bank-4i-20000mah')
        ->firstOrFail();
    $xiaomiGallery = $xiaomiPowerBank->media()
        ->whereNull('product_variant_id')
        ->orderBy('sort_order')
        ->get();
    $initialSeededAssetCount = MediaAsset::query()
        ->where('path', 'like', 'seeded/catalog/%')
        ->count();

    expect($asset->original_filename)->toBe('sony-wh-ch720n.png')
        ->and($asset->extension)->toBe('webp')
        ->and($asset->mime_type)->toBe('image/webp')
        ->and($asset->width)->toBeGreaterThan(0)
        ->and($asset->height)->toBeGreaterThan(0)
        ->and(Storage::disk('public')->exists($asset->path))->toBeTrue()
        ->and($sonyWfC700n->primaryCategory->slug)->toBe('audio')
        ->and(Product::query()->whereNull('primary_category_id')->exists())->toBeFalse()
        ->and($discountedProduct->defaultVariant->sale_price_minor)->toBe(499900)
        ->and($discountedProduct->defaultVariant->compare_at_price_minor)->toBe(549900)
        ->and($fullPriceProduct->defaultVariant->sale_price_minor)->toBeNull()
        ->and($fullPriceProduct->defaultVariant->compare_at_price_minor)->toBeNull()
        ->and($sonyGallery->pluck('role')->all())->toBe(['primary', 'gallery', 'gallery'])
        ->and($sonyGallery->pluck('path')->all())->toBe([
            'seeded/catalog/products/sony-wf-c700n-2.webp',
            'seeded/catalog/products/sony-wf-c700n-22.webp',
            'seeded/catalog/products/sony-wf-c700n.webp',
        ])
        ->and($xiaomiGallery->pluck('role')->all())->toBe(['primary', 'gallery'])
        ->and($xiaomiGallery->pluck('path')->all())->toBe([
            'seeded/catalog/products/xiaomi-power-bank-4i.webp',
            'seeded/catalog/products/xiaomi-power-bank-4i-20000mah.webp',
        ]);

    $fullPriceProduct->defaultVariant->update([
        'sale_price_minor' => 1299000,
        'compare_at_price_minor' => 1499000,
    ]);

    $this->seed([CatalogSeeder::class, ProductSeeder::class]);

    $refreshedFullPriceProduct = $fullPriceProduct->fresh('defaultVariant');

    expect(MediaAsset::query()->where('path', 'like', 'seeded/catalog/%')->count())
        ->toBe($initialSeededAssetCount)
        ->and(MediaAsset::query()->where('path', $asset->path)->count())->toBe(1)
        ->and($refreshedFullPriceProduct->defaultVariant->sale_price_minor)->toBeNull()
        ->and($refreshedFullPriceProduct->defaultVariant->compare_at_price_minor)->toBeNull();
});
