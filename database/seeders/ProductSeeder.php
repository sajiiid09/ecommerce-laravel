<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductMedia;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use App\Models\ProductVariant;
use App\Models\Tag;
use Database\Seeders\Concerns\SeedsDemoMedia;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    use SeedsDemoMedia;

    private array $brandCache = [];

    private array $categoryCache = [];

    private array $tagCache = [];

    private array $attributeCache = [];

    private array $attributeValueCache = [];

    public function run(): void
    {
        $this->loadCaches();
        $this->seedProducts();
    }

    private function loadCaches(): void
    {
        foreach (Brand::all() as $brand) {
            $this->brandCache[$brand->slug] = $brand->id;
        }

        foreach (Category::all() as $category) {
            $this->categoryCache[$category->slug] = $category->id;
        }

        foreach (Tag::all() as $tag) {
            $this->tagCache[$tag->slug] = $tag->id;
        }

        foreach (Attribute::all() as $attribute) {
            $this->attributeCache[$attribute->slug] = $attribute->id;
        }

        foreach (AttributeValue::all() as $value) {
            $this->attributeValueCache[$value->attribute_id][$value->slug] = $value->id;
        }
    }

    private function seedProducts(): void
    {
        foreach ($this->getProducts() as $data) {
            $product = Product::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'brand_id' => $this->brandCache[$data['brand']] ?? null,
                    'product_type' => $data['type'],
                    'short_description' => $data['short_description'] ?? null,
                    'status' => 'published',
                    'visibility' => 'visible',
                    'is_featured' => $data['is_featured'] ?? false,
                    'taxable' => true,
                    'published_at' => now()->subDays(rand(1, 30)),
                ]
            );

            $this->syncCategories($product, $data['categories'] ?? []);
            $this->syncTags($product, $data['tags'] ?? []);
            $this->syncAttributes($product, $data['attributes'] ?? []);

            if ($data['type'] === 'simple') {
                $this->createSimpleVariant($product, $data);
            } else {
                $this->createVariableProduct($product, $data);
            }

            $this->syncProductImage($product, $data['image'] ?? null);
        }
    }

    private function createSimpleVariant(Product $product, array $data): void
    {
        $priceMinor = $data['price'] * 100;
        $oldPriceMinor = isset($data['old_price']) ? $data['old_price'] * 100 : null;

        $variant = ProductVariant::updateOrCreate(
            ['sku' => "STZ-{$product->id}"],
            [
                'product_id' => $product->id,
                'name' => $product->name,
                'combination_key' => 'default',
                'regular_price_minor' => $priceMinor,
                'sale_price_minor' => $oldPriceMinor && $oldPriceMinor > $priceMinor ? $priceMinor : null,
                'compare_at_price_minor' => $oldPriceMinor,
                'is_active' => true,
                'is_default' => true,
                'sort_order' => 0,
            ]
        );

        $this->createInventory($variant, $data['stock'] ?? rand(10, 200));
    }

    private function createVariableProduct(Product $product, array $data): void
    {
        $option = ProductOption::updateOrCreate(
            ['product_id' => $product->id, 'slug' => Str::slug($data['option_name'])],
            [
                'name' => $data['option_name'],
                'slug' => Str::slug($data['option_name']),
                'sort_order' => 0,
            ]
        );

        $optionValueIds = [];

        foreach ($data['options'] as $optionData) {
            $value = ProductOptionValue::updateOrCreate(
                ['product_option_id' => $option->id, 'slug' => Str::slug($optionData['value'])],
                [
                    'value' => $optionData['label'],
                    'slug' => Str::slug($optionData['value']),
                    'sort_order' => 0,
                ]
            );

            $optionValueIds[$optionData['value']] = $value->id;
        }

        foreach ($data['options'] as $index => $optionData) {
            $priceMinor = $optionData['price'] * 100;
            $combinationKey = (string) $optionValueIds[$optionData['value']];

            $variant = ProductVariant::updateOrCreate(
                ['sku' => "STZ-{$product->id}-".strtoupper(substr(md5($combinationKey), 0, 6))],
                [
                    'product_id' => $product->id,
                    'name' => $optionData['label'],
                    'combination_key' => $combinationKey,
                    'regular_price_minor' => $priceMinor,
                    'is_active' => true,
                    'is_default' => $index === 0,
                    'sort_order' => $index,
                ]
            );

            $variant->optionValues()->sync([$optionValueIds[$optionData['value']]]);
            $this->createInventory($variant, rand(10, 150));
        }
    }

    private function createInventory(ProductVariant $variant, int $quantity): void
    {
        $item = InventoryItem::updateOrCreate(
            ['product_variant_id' => $variant->id],
            [
                'quantity_on_hand' => $quantity,
                'quantity_reserved' => 0,
                'low_stock_threshold' => 5,
                'track_quantity' => true,
                'allow_backorders' => false,
            ]
        );

        InventoryMovement::updateOrCreate(
            [
                'inventory_item_id' => $item->id,
                'product_variant_id' => $variant->id,
                'type' => 'initial',
            ],
            [
                'quantity_delta' => $quantity,
                'quantity_before' => 0,
                'quantity_after' => $quantity,
                'note' => 'Initial stock from seeder',
            ]
        );
    }

    private function syncCategories(Product $product, array $slugs): void
    {
        $ids = array_filter(array_map(
            fn ($slug) => $this->categoryCache[$slug] ?? null,
            $slugs
        ));

        $product->categories()->sync($ids);
    }

    private function syncTags(Product $product, array $slugs): void
    {
        $ids = array_filter(array_map(
            fn ($slug) => $this->tagCache[$slug] ?? null,
            $slugs
        ));

        $product->tags()->sync($ids);
    }

    private function syncAttributes(Product $product, array $attributes): void
    {
        foreach ($attributes as $attributeSlug => $valueSlug) {
            $attributeId = $this->attributeCache[$attributeSlug] ?? null;
            $valueId = $attributeId
                ? ($this->attributeValueCache[$attributeId][$valueSlug] ?? null)
                : null;

            if (! $attributeId) {
                continue;
            }

            ProductAttributeValue::updateOrCreate(
                ['product_id' => $product->id, 'attribute_id' => $attributeId],
                ['attribute_value_id' => $valueId]
            );
        }
    }

    private function syncProductImage(Product $product, ?string $relativePath): void
    {
        if (! $relativePath) {
            return;
        }

        $media = $this->seedLocalImage($relativePath);

        if (! $media) {
            return;
        }

        ProductMedia::updateOrCreate(
            [
                'product_id' => $product->id,
                'role' => 'primary',
                'sort_order' => 0,
            ],
            [
                'product_variant_id' => null,
                'media_asset_id' => $media['id'],
                'path' => $media['path'],
                'alt_text' => $product->name,
            ]
        );
    }

    private function getProducts(): array
    {
        return [
            [
                'slug' => 'sony-wh-ch720n',
                'name' => 'Sony WH-CH720N Wireless Noise Cancelling Headphones',
                'brand' => 'sony',
                'type' => 'simple',
                'price' => 12990,
                'old_price' => 14990,
                'stock' => 85,
                'is_featured' => true,
                'categories' => ['electronics', 'audio'],
                'tags' => ['best-seller'],
                'attributes' => ['warranty' => '1-year', 'color' => 'black'],
                'image' => 'products/sony-wh-ch720n.png',
            ],
            [
                'slug' => 'wiz-a60-e27-smart-bulb',
                'name' => 'WiZ A60 E27 Smart LED Bulb',
                'brand' => 'wiz',
                'type' => 'simple',
                'price' => 1290,
                'old_price' => 1490,
                'stock' => 150,
                'is_featured' => false,
                'categories' => ['electronics', 'home-living'],
                'tags' => ['on-sale'],
                'attributes' => ['warranty' => '2-years'],
                'image' => 'products/wiz-a60-e27-smart-bulb.jpg',
            ],
            [
                'slug' => 'fresh-atta-2kg',
                'name' => 'Fresh Atta 2kg',
                'brand' => 'fresh',
                'type' => 'simple',
                'price' => 120,
                'old_price' => 135,
                'stock' => 200,
                'is_featured' => true,
                'categories' => ['groceries'],
                'tags' => ['best-seller', 'budget-friendly'],
                'attributes' => ['weight' => '2kg'],
                'image' => 'products/fresh-atta-2kg.jpg',
            ],
            [
                'slug' => 'teer-nazirshail-rice-5kg',
                'name' => 'Teer Nazirshail Rice 5kg',
                'brand' => 'teer',
                'type' => 'simple',
                'price' => 650,
                'old_price' => 690,
                'stock' => 120,
                'is_featured' => false,
                'categories' => ['groceries'],
                'tags' => ['premium'],
                'attributes' => ['weight' => '5kg'],
                'image' => 'products/teer-nazirshail-rice-5kg.jpg',
            ],
            [
                'slug' => 'fresh-soyabean-oil-2l',
                'name' => 'Fresh Soyabean Oil 2L',
                'brand' => 'fresh',
                'type' => 'simple',
                'price' => 398,
                'old_price' => 420,
                'stock' => 180,
                'is_featured' => false,
                'categories' => ['groceries'],
                'tags' => ['on-sale'],
                'attributes' => [],
                'image' => 'products/fresh-soyabean-oil-2l.webp',
            ],
            [
                'slug' => 'orix-crystal-detergent-powder-2kg',
                'name' => 'Orix Crystal Detergent Powder 2kg',
                'brand' => 'orix',
                'type' => 'simple',
                'price' => 520,
                'old_price' => 695,
                'stock' => 120,
                'is_featured' => false,
                'categories' => ['groceries'],
                'tags' => ['on-sale', 'budget-friendly'],
                'attributes' => ['weight' => '2kg'],
                'image' => 'products/orix-crystal-detergent-powder-2kg.png',
            ],
            [
                'slug' => 'redmi-note-13',
                'name' => 'Redmi Note 13',
                'brand' => 'xiaomi',
                'type' => 'variable',
                'price' => 20999,
                'old_price' => 24999,
                'stock' => 0,
                'is_featured' => true,
                'categories' => ['electronics', 'phones'],
                'tags' => ['new-arrival', 'best-seller'],
                'attributes' => ['warranty' => '1-year'],
                'option_name' => 'Storage',
                'options' => [
                    ['label' => '6/128GB', 'value' => '6-128gb', 'price' => 20999],
                    ['label' => '8/256GB', 'value' => '8-256gb', 'price' => 22999],
                ],
                'image' => 'products/redmi-note-13.png',
            ],
            [
                'slug' => 'nivea-soft-300ml',
                'name' => 'NIVEA Soft Light Moisturizer 300ml',
                'brand' => 'nivea',
                'type' => 'simple',
                'price' => 390,
                'old_price' => 475,
                'stock' => 160,
                'is_featured' => false,
                'categories' => ['beauty'],
                'tags' => ['on-sale'],
                'attributes' => [],
                'image' => 'products/nivea-soft-300ml.png',
            ],
            [
                'slug' => 'miyako-mjk-805-kettle',
                'name' => 'Miyako MJK-805 Electric Kettle 1.8L',
                'brand' => 'miyako',
                'type' => 'simple',
                'price' => 850,
                'old_price' => 1090,
                'stock' => 90,
                'is_featured' => false,
                'categories' => ['home-living'],
                'tags' => ['on-sale'],
                'attributes' => ['warranty' => '1-year', 'material' => 'steel'],
                'image' => 'products/miyako-mjk-805-kettle.webp',
            ],
            [
                'slug' => 'aarong-blue-cotton-waistcoat',
                'name' => 'Aarong Blue Cotton Waistcoat',
                'brand' => 'aarong',
                'type' => 'simple',
                'price' => 2777,
                'old_price' => 3100,
                'stock' => 75,
                'is_featured' => false,
                'categories' => ['fashion', 'mens-fashion'],
                'tags' => ['premium'],
                'attributes' => ['material' => 'cotton', 'color' => 'blue'],
                'image' => 'products/aarong-blue-cotton-waistcoat.jpg',
            ],
            [
                'slug' => 'samsung-galaxy-a15-5g',
                'name' => 'Samsung Galaxy A15 5G',
                'brand' => 'samsung',
                'type' => 'simple',
                'price' => 23999,
                'old_price' => 26999,
                'stock' => 60,
                'is_featured' => true,
                'categories' => ['electronics', 'phones'],
                'tags' => ['on-sale'],
                'attributes' => ['warranty' => '1-year'],
                'image' => 'products/samsung-galaxy-a15-5g.jpg',
            ],
            [
                'slug' => 'walton-wcw-comc70-cookware-set',
                'name' => 'Walton WCW-COMC70 Cookware 7-in-1 Combo',
                'brand' => 'walton',
                'type' => 'simple',
                'price' => 4530,
                'old_price' => 5090,
                'stock' => 45,
                'is_featured' => false,
                'categories' => ['home-living'],
                'tags' => ['on-sale'],
                'attributes' => [],
                'image' => 'products/walton-wcw-comc70-cookware-set.jpg',
            ],
            [
                'slug' => 'apex-95910a47-casual-shoe',
                'name' => "Apex Men's Washable Casual Shoe 95910A47",
                'brand' => 'apex',
                'type' => 'simple',
                'price' => 390,
                'old_price' => 490,
                'stock' => 100,
                'is_featured' => false,
                'categories' => ['fashion', 'mens-fashion'],
                'tags' => ['on-sale'],
                'attributes' => [],
                'image' => 'products/apex-95910a47-casual-shoe.jpg',
            ],
            [
                'slug' => 'mr-noodles-magic-masala-16-pack',
                'name' => 'Mr Noodles Magic Masala 16 Pack',
                'brand' => 'pran',
                'type' => 'simple',
                'price' => 335,
                'old_price' => 360,
                'stock' => 200,
                'is_featured' => false,
                'categories' => ['groceries'],
                'tags' => ['budget-friendly', 'best-seller'],
                'attributes' => [],
                'image' => 'products/mr-noodles-magic-masala-16-pack.jpg',
            ],
            [
                'slug' => 'ikea-taernaby-table-lamp',
                'name' => 'IKEA TÄRNABY Table Lamp',
                'brand' => 'ikea',
                'type' => 'simple',
                'price' => 2990,
                'old_price' => 3490,
                'stock' => 65,
                'is_featured' => false,
                'categories' => ['home-living'],
                'tags' => ['on-sale'],
                'attributes' => ['material' => 'steel'],
                'image' => 'products/ikea-taernaby-table-lamp.jpg',
            ],
            [
                'slug' => 'sony-wh-ch520',
                'name' => 'Sony WH-CH520 Wireless Headphones',
                'brand' => 'sony',
                'type' => 'simple',
                'price' => 5990,
                'old_price' => 6490,
                'stock' => 70,
                'is_featured' => false,
                'categories' => ['electronics', 'audio'],
                'tags' => ['new-arrival'],
                'attributes' => ['warranty' => '1-year', 'color' => 'black'],
                'image' => 'products/sony-wh-ch520.jpg',
            ],
            [
                'slug' => 'sony-srs-xb100',
                'name' => 'Sony SRS-XB100 Portable Bluetooth Speaker',
                'brand' => 'sony',
                'type' => 'simple',
                'price' => 6490,
                'old_price' => 6990,
                'stock' => 55,
                'is_featured' => true,
                'categories' => ['electronics', 'audio'],
                'tags' => ['best-seller'],
                'attributes' => ['warranty' => '1-year', 'color' => 'black'],
                'image' => 'products/sony-srs-xb100.jpg',
            ],
            [
                'slug' => 'samsung-galaxy-a25-5g',
                'name' => 'Samsung Galaxy A25 5G',
                'brand' => 'samsung',
                'type' => 'simple',
                'price' => 32999,
                'old_price' => 35999,
                'stock' => 48,
                'is_featured' => true,
                'categories' => ['electronics', 'phones'],
                'tags' => ['new-arrival'],
                'attributes' => ['warranty' => '1-year'],
                'image' => 'products/samsung-galaxy-a25-5g.jpg',
            ],
            [
                'slug' => 'redmi-buds-5',
                'name' => 'Redmi Buds 5',
                'brand' => 'xiaomi',
                'type' => 'simple',
                'price' => 4499,
                'old_price' => 4999,
                'stock' => 95,
                'is_featured' => false,
                'categories' => ['electronics', 'audio'],
                'tags' => ['on-sale'],
                'attributes' => ['warranty' => '6-months', 'color' => 'black'],
                'image' => 'products/redmi-buds-5.jpg',
            ],
            [
                'slug' => 'redmi-note-14',
                'name' => 'Redmi Note 14',
                'brand' => 'xiaomi',
                'type' => 'simple',
                'price' => 25999,
                'old_price' => 28999,
                'stock' => 52,
                'is_featured' => true,
                'categories' => ['electronics', 'phones'],
                'tags' => ['new-arrival'],
                'attributes' => ['warranty' => '1-year'],
                'image' => 'products/redmi-note-14.jpg',
            ],
            [
                'slug' => 'wiz-smart-plug',
                'name' => 'WiZ Smart Plug',
                'brand' => 'wiz',
                'type' => 'simple',
                'price' => 1890,
                'old_price' => 2190,
                'stock' => 110,
                'is_featured' => false,
                'categories' => ['electronics', 'home-living'],
                'tags' => ['new-arrival'],
                'attributes' => ['warranty' => '2-years'],
                'image' => 'products/wiz-smart-plug.jpg',
            ],
            [
                'slug' => 'nivea-men-deep-face-wash',
                'name' => 'NIVEA Men Deep Face Wash 100g',
                'brand' => 'nivea',
                'type' => 'simple',
                'price' => 450,
                'old_price' => 520,
                'stock' => 130,
                'is_featured' => false,
                'categories' => ['beauty'],
                'tags' => ['best-seller'],
                'attributes' => [],
                'image' => 'products/nivea-men-deep-face-wash.jpg',
            ],
            [
                'slug' => 'nivea-creme-150ml',
                'name' => 'NIVEA Creme 150ml',
                'brand' => 'nivea',
                'type' => 'simple',
                'price' => 520,
                'old_price' => 590,
                'stock' => 145,
                'is_featured' => false,
                'categories' => ['beauty'],
                'tags' => ['premium'],
                'attributes' => [],
                'image' => 'products/nivea-creme-150ml.jpg',
            ],
            [
                'slug' => 'miyako-blender-bl-152',
                'name' => 'Miyako BL-152 Blender',
                'brand' => 'miyako',
                'type' => 'simple',
                'price' => 2890,
                'old_price' => 3290,
                'stock' => 62,
                'is_featured' => false,
                'categories' => ['home-living'],
                'tags' => ['on-sale'],
                'attributes' => ['warranty' => '1-year'],
                'image' => 'products/miyako-blender-bl-152.jpg',
            ],
            [
                'slug' => 'walton-rice-cooker-wrc-sgae28',
                'name' => 'Walton WRC-SGAE28 Rice Cooker',
                'brand' => 'walton',
                'type' => 'simple',
                'price' => 3190,
                'old_price' => 3490,
                'stock' => 58,
                'is_featured' => false,
                'categories' => ['home-living'],
                'tags' => ['best-seller'],
                'attributes' => ['warranty' => '1-year'],
                'image' => 'products/walton-rice-cooker-wrc-sgae28.jpg',
            ],
            [
                'slug' => 'ikea-kallax-shelf-unit',
                'name' => 'IKEA KALLAX Shelf Unit',
                'brand' => 'ikea',
                'type' => 'simple',
                'price' => 8990,
                'old_price' => 9490,
                'stock' => 32,
                'is_featured' => true,
                'categories' => ['home-living'],
                'tags' => ['premium'],
                'attributes' => [],
                'image' => 'products/ikea-kallax-shelf-unit.jpg',
            ],
            [
                'slug' => 'aarong-navy-cotton-panjabi',
                'name' => 'Aarong Navy Blue Cotton Panjabi',
                'brand' => 'aarong',
                'type' => 'simple',
                'price' => 2450,
                'old_price' => 2750,
                'stock' => 68,
                'is_featured' => true,
                'categories' => ['fashion', 'mens-fashion'],
                'tags' => ['new-arrival', 'premium'],
                'attributes' => ['material' => 'cotton', 'color' => 'blue'],
                'image' => 'products/aarong-navy-cotton-panjabi.jpg',
            ],
            [
                'slug' => 'apex-mens-sports-shoe',
                'name' => 'Apex Men\'s Sports Shoe',
                'brand' => 'apex',
                'type' => 'simple',
                'price' => 3290,
                'old_price' => 3690,
                'stock' => 82,
                'is_featured' => false,
                'categories' => ['fashion', 'mens-fashion'],
                'tags' => ['best-seller'],
                'attributes' => ['color' => 'black'],
                'image' => 'products/apex-mens-sports-shoe.jpg',
            ],
            [
                'slug' => 'fresh-refined-sugar-1kg',
                'name' => 'Fresh Refined Sugar 1kg',
                'brand' => 'fresh',
                'type' => 'simple',
                'price' => 145,
                'old_price' => 155,
                'stock' => 190,
                'is_featured' => false,
                'categories' => ['groceries'],
                'tags' => ['budget-friendly'],
                'attributes' => ['weight' => '1kg'],
                'image' => 'products/fresh-refined-sugar-1kg.jpg',
            ],
            [
                'slug' => 'pran-chanachur-300g',
                'name' => 'PRAN Chanachur 300g',
                'brand' => 'pran',
                'type' => 'simple',
                'price' => 120,
                'old_price' => 135,
                'stock' => 170,
                'is_featured' => false,
                'categories' => ['groceries'],
                'tags' => ['best-seller', 'budget-friendly'],
                'attributes' => [],
                'image' => 'products/pran-chanachur-300g.jpg',
            ],
        ];
    }
}
