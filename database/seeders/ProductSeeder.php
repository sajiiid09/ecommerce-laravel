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
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use App\Models\ProductVariant;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
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
        foreach (Brand::all() as $b) {
            $this->brandCache[$b->slug] = $b->id;
        }
        foreach (Category::all() as $c) {
            $this->categoryCache[$c->slug] = $c->id;
        }
        foreach (Tag::all() as $t) {
            $this->tagCache[$t->slug] = $t->id;
        }
        foreach (Attribute::all() as $a) {
            $this->attributeCache[$a->slug] = $a->id;
        }
        foreach (AttributeValue::all() as $av) {
            $this->attributeValueCache[$av->attribute_id][$av->slug] = $av->id;
        }
    }

    private function seedProducts(): void
    {
        $products = $this->getProducts();

        foreach ($products as $data) {
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
        foreach ($data['options'] as $opt) {
            $val = ProductOptionValue::updateOrCreate(
                ['product_option_id' => $option->id, 'slug' => Str::slug($opt['value'])],
                [
                    'value' => $opt['label'],
                    'slug' => Str::slug($opt['value']),
                    'sort_order' => 0,
                ]
            );
            $optionValueIds[$opt['value']] = $val->id;
        }

        foreach ($data['options'] as $opt) {
            $priceMinor = $opt['price'] * 100;
            $combinationKey = (string) $optionValueIds[$opt['value']];

            $variant = ProductVariant::updateOrCreate(
                ['sku' => "STZ-{$product->id}-".strtoupper(substr(md5($combinationKey), 0, 6))],
                [
                    'product_id' => $product->id,
                    'name' => $opt['label'],
                    'combination_key' => $combinationKey,
                    'regular_price_minor' => $priceMinor,
                    'is_active' => true,
                    'is_default' => false,
                    'sort_order' => 0,
                ]
            );

            $variant->optionValues()->syncWithoutDetaching([$optionValueIds[$opt['value']]]);
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
        $ids = array_map(fn ($s) => $this->categoryCache[$s] ?? null, $slugs);
        $ids = array_filter($ids);
        $product->categories()->sync($ids);
    }

    private function syncTags(Product $product, array $slugs): void
    {
        $ids = array_map(fn ($s) => $this->tagCache[$s] ?? null, $slugs);
        $ids = array_filter($ids);
        $product->tags()->sync($ids);
    }

    private function syncAttributes(Product $product, array $attrs): void
    {
        foreach ($attrs as $attrSlug => $valueSlug) {
            $attrId = $this->attributeCache[$attrSlug] ?? null;
            $valId = $this->attributeValueCache[$attrId][$valueSlug] ?? null;
            if ($attrId) {
                ProductAttributeValue::updateOrCreate(
                    ['product_id' => $product->id, 'attribute_id' => $attrId],
                    ['attribute_value_id' => $valId]
                );
            }
        }
    }

    private function getProducts(): array
    {
        return [
            [
                'slug' => 'wireless-noise-cancelling-headphones',
                'name' => 'Wireless Noise Cancelling Headphones',
                'brand' => 'soundmax',
                'type' => 'simple',
                'price' => 4290,
                'old_price' => 4990,
                'stock' => 85,
                'is_featured' => false,
                'categories' => ['electronics', 'audio'],
                'tags' => ['best-seller'],
                'attributes' => ['warranty' => '1-year'],
            ],
            [
                'slug' => 'smart-led-bulb-pack',
                'name' => 'Smart LED Bulb, Pack of 2',
                'brand' => 'brighthome',
                'type' => 'simple',
                'price' => 890,
                'old_price' => 1090,
                'stock' => 150,
                'is_featured' => false,
                'categories' => ['electronics', 'home-living'],
                'tags' => ['on-sale'],
                'attributes' => ['warranty' => '2-years'],
            ],
            [
                'slug' => 'daily-essential-basket',
                'name' => 'Daily Essential Grocery Basket',
                'brand' => 'storez-fresh',
                'type' => 'simple',
                'price' => 1290,
                'stock' => 200,
                'is_featured' => true,
                'categories' => ['groceries'],
                'tags' => ['best-seller', 'budget-friendly'],
                'attributes' => [],
            ],
            [
                'slug' => 'premium-basmati-rice',
                'name' => 'Premium Basmati Rice',
                'brand' => 'teer',
                'type' => 'variable',
                'price' => 950,
                'old_price' => 1180,
                'stock' => 0,
                'is_featured' => false,
                'categories' => ['groceries'],
                'tags' => ['premium'],
                'attributes' => ['material' => 'plastic'],
                'option_name' => 'Weight',
                'options' => [
                    ['label' => '1kg', 'value' => '1kg', 'price' => 210],
                    ['label' => '5kg', 'value' => '5kg', 'price' => 950],
                    ['label' => '10kg', 'value' => '10kg', 'price' => 1780],
                ],
            ],
            [
                'slug' => 'fresh-soyabean-oil',
                'name' => 'Fresh Soyabean Oil 2L',
                'brand' => 'fresh',
                'type' => 'simple',
                'price' => 620,
                'old_price' => 730,
                'stock' => 180,
                'is_featured' => false,
                'categories' => ['groceries'],
                'tags' => ['on-sale'],
                'attributes' => [],
            ],
            [
                'slug' => 'orix-crystal-detergent',
                'name' => 'Orix Crystal Detergent Powder 2kg',
                'brand' => 'orix',
                'type' => 'simple',
                'price' => 520,
                'old_price' => 695,
                'stock' => 120,
                'is_featured' => false,
                'categories' => ['groceries'],
                'tags' => ['on-sale', 'budget-friendly'],
                'attributes' => ['material' => 'plastic'],
            ],
            [
                'slug' => 'redmi-note-13',
                'name' => 'Redmi Note 13',
                'brand' => 'xiaomi',
                'type' => 'variable',
                'price' => 18999,
                'old_price' => 24999,
                'stock' => 0,
                'is_featured' => true,
                'categories' => ['electronics', 'phones'],
                'tags' => ['new-arrival', 'best-seller'],
                'attributes' => ['warranty' => '1-year'],
                'option_name' => 'Storage',
                'options' => [
                    ['label' => '8/128GB', 'value' => '8-128gb', 'price' => 18999],
                    ['label' => '8/256GB', 'value' => '8-256gb', 'price' => 21999],
                ],
            ],
            [
                'slug' => 'nivea-soft-moisturizer',
                'name' => 'Nivea Soft Light Moisturizer 300ml',
                'brand' => 'nivea',
                'type' => 'simple',
                'price' => 390,
                'old_price' => 475,
                'stock' => 160,
                'is_featured' => false,
                'categories' => ['beauty'],
                'tags' => ['on-sale'],
                'attributes' => [],
            ],
            [
                'slug' => 'miyako-electric-kettle',
                'name' => 'Miyako Electric Kettle 1.8L',
                'brand' => 'miyako',
                'type' => 'simple',
                'price' => 850,
                'old_price' => 1090,
                'stock' => 90,
                'is_featured' => false,
                'categories' => ['home-living'],
                'tags' => ['on-sale'],
                'attributes' => ['warranty' => '1-year', 'material' => 'steel'],
            ],
            [
                'slug' => 'aarong-panjabi',
                'name' => 'Classic Cotton Panjabi',
                'brand' => 'aarong',
                'type' => 'simple',
                'price' => 1650,
                'old_price' => 2100,
                'stock' => 75,
                'is_featured' => false,
                'categories' => ['fashion', 'mens-fashion'],
                'tags' => ['premium'],
                'attributes' => ['material' => 'cotton'],
            ],
            [
                'slug' => 'samsung-galaxy-a15',
                'name' => 'Samsung Galaxy A15',
                'brand' => 'samsung',
                'type' => 'simple',
                'price' => 17499,
                'old_price' => 19999,
                'stock' => 60,
                'is_featured' => false,
                'categories' => ['electronics', 'phones'],
                'tags' => ['on-sale'],
                'attributes' => ['warranty' => '1-year'],
            ],
            [
                'slug' => 'walton-nonstick-cookware',
                'name' => 'Walton Non-Stick Cookware Set',
                'brand' => 'walton',
                'type' => 'simple',
                'price' => 3450,
                'old_price' => 4200,
                'stock' => 45,
                'is_featured' => false,
                'categories' => ['home-living'],
                'tags' => ['on-sale'],
                'attributes' => ['material' => 'steel', 'warranty' => '2-years'],
            ],
            [
                'slug' => 'apex-casual-shoes',
                'name' => 'Apex Casual Shoes',
                'brand' => 'apex',
                'type' => 'simple',
                'price' => 1799,
                'old_price' => 2390,
                'stock' => 100,
                'is_featured' => false,
                'categories' => ['fashion', 'mens-fashion'],
                'tags' => ['on-sale'],
                'attributes' => ['material' => 'polyester'],
            ],
            [
                'slug' => 'fresh-noodles',
                'name' => 'Instant Noodles Family Pack',
                'brand' => 'fresh',
                'type' => 'simple',
                'price' => 75,
                'old_price' => 90,
                'stock' => 200,
                'is_featured' => false,
                'categories' => ['groceries'],
                'tags' => ['budget-friendly', 'best-seller'],
                'attributes' => [],
            ],
            [
                'slug' => 'decorative-table-lamp',
                'name' => 'Decorative Table Lamp',
                'brand' => 'brighthome',
                'type' => 'simple',
                'price' => 890,
                'old_price' => 1200,
                'stock' => 65,
                'is_featured' => false,
                'categories' => ['home-living'],
                'tags' => ['on-sale'],
                'attributes' => ['material' => 'plastic'],
            ],
        ];
    }
}
