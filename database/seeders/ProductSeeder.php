<?php

namespace Database\Seeders;

use App\Enums\ImagePreset;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\MediaAsset;
use App\Models\MediaUsage;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductMedia;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use App\Models\ProductVariant;
use App\Models\Tag;
use App\Services\MediaService;
use App\Services\ProductVariantService;
use Database\Seeders\Concerns\SeedsDemoMedia;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    use SeedsDemoMedia;

    public function __construct(
        private readonly ProductVariantService $variantService,
        private readonly MediaService $mediaService,
    ) {}

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
            $primaryCategorySlug = $data['primary_category'] ?? collect($data['categories'] ?? [])->last();
            $description = $this->buildRichDescription($data['slug'], $data['name']);

            $product = Product::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'brand_id' => isset($data['brand']) ? ($this->brandCache[$data['brand']] ?? null) : null,
                    'primary_category_id' => $this->categoryCache[$primaryCategorySlug] ?? null,
                    'product_type' => $data['type'],
                    'short_description' => $data['short_description'] ?? $description['short'],
                    'description_json' => $description['json'],
                    'description_html' => $description['html'],
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

            $this->syncProductImages(
                $product,
                $data['images'] ?? ($data['image'] ?? null),
            );
        }
    }

    private function createSimpleVariant(Product $product, array $data): void
    {
        $priceMinor = $data['price'] * 100;
        $hasDiscount = in_array('on-sale', $data['tags'] ?? [], true)
            && isset($data['old_price'])
            && $data['old_price'] > $data['price'];
        $regularPriceMinor = $hasDiscount ? $data['old_price'] * 100 : $priceMinor;
        $salePriceMinor = $hasDiscount ? $priceMinor : null;

        $variant = ProductVariant::updateOrCreate(
            ['sku' => "STZ-{$product->id}"],
            [
                'product_id' => $product->id,
                'name' => $product->name,
                'combination_key' => 'default',
                'regular_price_minor' => $regularPriceMinor,
                'sale_price_minor' => $salePriceMinor,
                'compare_at_price_minor' => $hasDiscount ? $regularPriceMinor : null,
                'is_active' => true,
                'is_default' => true,
                'sort_order' => 0,
            ]
        );

        $this->createInventory($variant, $data['stock'] ?? rand(10, 200));
    }

    private function createVariableProduct(Product $product, array $data): void
    {
        $optionSlugs = collect($data['options'])
            ->map(fn (array $optionData): string => $optionData['slug'] ?? Str::slug($optionData['name']))
            ->all();

        $product->options()
            ->whereNotIn('slug', $optionSlugs)
            ->get()
            ->each(function (ProductOption $option): void {
                $option->delete();
            });

        foreach ($data['options'] as $optionIndex => $optionData) {
            $optionSlug = $optionData['slug'] ?? Str::slug($optionData['name']);
            $option = ProductOption::updateOrCreate(
                ['product_id' => $product->id, 'slug' => $optionSlug],
                [
                    'name' => $optionData['name'],
                    'slug' => $optionSlug,
                    'sort_order' => $optionIndex,
                ]
            );

            $valueSlugs = collect($optionData['values'])
                ->pluck('slug')
                ->all();

            $option->values()
                ->whereNotIn('slug', $valueSlugs)
                ->get()
                ->each(function (ProductOptionValue $value): void {
                    $value->variants()->detach();
                    $value->delete();
                });

            foreach ($optionData['values'] as $valueIndex => $valueData) {
                ProductOptionValue::updateOrCreate(
                    ['product_option_id' => $option->id, 'slug' => $valueData['slug']],
                    [
                        'value' => $valueData['label'],
                        'slug' => $valueData['slug'],
                        'sort_order' => $valueIndex,
                    ]
                );
            }
        }

        $this->quarantineObsoleteVariants($product, array_keys($data['variants']));

        foreach ($this->variantService->generate($product) as $index => $variant) {
            $variantData = $data['variants'][$variant->combination_key] ?? null;

            if ($variantData === null) {
                throw new \InvalidArgumentException("Missing seeded data for {$product->slug}: {$variant->combination_key}");
            }

            $regularPriceMinor = (int) round($variantData['price'] * 100);
            $salePriceMinor = isset($variantData['sale_price'])
                ? (int) round($variantData['sale_price'] * 100)
                : null;

            $variant->forceFill([
                'sku' => $variantData['sku'],
                'regular_price_minor' => $regularPriceMinor,
                'sale_price_minor' => $salePriceMinor,
                'compare_at_price_minor' => $salePriceMinor !== null && $salePriceMinor < $regularPriceMinor
                    ? $regularPriceMinor
                    : null,
                'is_active' => true,
                'is_default' => $index === 0,
                'sort_order' => $index,
            ])->save();

            $this->createInventory($variant, $variantData['stock']);
            $this->syncVariantImages($product, $variant, $variantData['images'] ?? []);
        }
    }

    private function quarantineObsoleteVariants(Product $product, array $validCombinationKeys): void
    {
        $seenCombinationKeys = [];

        ProductVariant::query()
            ->whereBelongsTo($product)
            ->withTrashed()
            ->get()
            ->each(function (ProductVariant $variant) use ($validCombinationKeys, &$seenCombinationKeys): void {
                $combinationKey = $variant->combination_key;

                if ($variant->trashed()) {
                    $variant->forceFill([
                        'sku' => 'STZ-LEGACY-'.$variant->id,
                        'combination_key' => 'seed-legacy-'.$variant->id,
                    ])->save();

                    return;
                }

                if (
                    ! in_array($combinationKey, $validCombinationKeys, true)
                    || in_array($combinationKey, $seenCombinationKeys, true)
                ) {
                    $variant->optionValues()->detach();
                    $variant->forceFill([
                        'sku' => 'STZ-LEGACY-'.$variant->id,
                        'combination_key' => 'seed-legacy-'.$variant->id,
                    ])->save();

                    return;
                }

                $seenCombinationKeys[] = $combinationKey;
            });
    }

    private function syncVariantImages(Product $product, ProductVariant $variant, array $relativePaths): void
    {
        $relativePaths = array_values(array_filter($relativePaths));

        if ($relativePaths === []) {
            return;
        }

        $processedMedia = [];

        foreach ($relativePaths as $sortOrder => $relativePath) {
            $media = $this->seedLocalImage(
                $relativePath,
                ImagePreset::Product,
                "seeded/catalog/variants/{$product->slug}/{$variant->sku}-{$sortOrder}.webp",
            );

            if (! $media) {
                return;
            }

            $processedMedia[$sortOrder] = $media;
        }

        MediaUsage::query()
            ->where('usable_type', ProductVariant::class)
            ->where('usable_id', $variant->id)
            ->where('role', 'variant.image')
            ->delete();
        $variant->media()->delete();

        foreach ($processedMedia as $sortOrder => $media) {
            $variant->media()->create([
                'product_id' => $product->id,
                'media_asset_id' => $media['id'],
                'role' => $sortOrder === 0 ? 'main' : 'gallery',
                'sort_order' => $sortOrder,
                'alt_text' => $product->name.' '.$variant->name,
            ]);

            $asset = MediaAsset::query()->find($media['id']);

            if ($asset) {
                $this->mediaService->attach($asset, $variant, 'variant.image');
            }
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

    private function syncProductImages(Product $product, string|array|null $relativePaths): void
    {
        $relativePaths = is_array($relativePaths) ? $relativePaths : [$relativePaths];
        $relativePaths = array_values(array_filter($relativePaths));

        if ($relativePaths === []) {
            return;
        }

        $processedMedia = [];

        foreach ($relativePaths as $sortOrder => $relativePath) {
            $media = $this->seedLocalImage($relativePath, ImagePreset::Product);

            if (! $media) {
                return;
            }

            $processedMedia[$sortOrder] = $media;
        }

        foreach ($processedMedia as $sortOrder => $media) {
            ProductMedia::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'role' => $sortOrder === 0 ? 'primary' : 'gallery',
                    'sort_order' => $sortOrder,
                ],
                [
                    'product_variant_id' => null,
                    'media_asset_id' => $media['id'],
                    'path' => $media['path'],
                    'alt_text' => $product->name,
                ]
            );
        }

        $desiredMediaKeys = collect($processedMedia)
            ->keys()
            ->map(fn (int $sortOrder): string => ($sortOrder === 0 ? 'primary' : 'gallery').':'.$sortOrder)
            ->all();

        ProductMedia::query()
            ->where('product_id', $product->id)
            ->whereNull('product_variant_id')
            ->whereIn('role', ['primary', 'gallery'])
            ->get()
            ->each(function (ProductMedia $productMedia) use ($desiredMediaKeys): void {
                $mediaKey = $productMedia->role.':'.$productMedia->sort_order;

                if (! in_array($mediaKey, $desiredMediaKeys, true)) {
                    $productMedia->delete();
                }
            });
    }

    /**
     * Build both Tiptap JSON and display HTML from the same product copy.
     *
     * description_json remains the editable source of truth. The generated HTML
     * intentionally uses only conservative structural tags that are expected to
     * survive the same sanitizer used by the normal product editing flow.
     */
    private function buildRichDescription(string $slug, string $name): array
    {
        $copy = $this->productDescriptionCatalog()[$slug] ?? [
            'short' => $name.' for dependable everyday use.',
            'overview' => $name.' is selected for the StoreZ demo catalog as a practical everyday product.',
            'highlights' => [
                'Suitable for routine everyday use.',
                'Product details are presented in a clean, easy-to-scan format.',
                'Designed to fit naturally into the StoreZ demo shopping experience.',
            ],
            'note' => 'Review the product label, dimensions, compatibility or care instructions before purchase where applicable.',
        ];

        $json = [
            'type' => 'doc',
            'content' => [
                $this->headingNode(2, 'Overview'),
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'marks' => [['type' => 'bold']],
                            'text' => $name,
                        ],
                        [
                            'type' => 'text',
                            'text' => ' — '.$copy['overview'],
                        ],
                    ],
                ],
                $this->headingNode(3, 'Highlights'),
                [
                    'type' => 'bulletList',
                    'content' => array_map(
                        fn (string $item): array => [
                            'type' => 'listItem',
                            'content' => [[
                                'type' => 'paragraph',
                                'content' => [[
                                    'type' => 'text',
                                    'text' => $item,
                                ]],
                            ]],
                        ],
                        $copy['highlights'],
                    ),
                ],
                $this->headingNode(3, 'Good to know'),
                $this->paragraphNode($copy['note']),
            ],
        ];

        return [
            'short' => $copy['short'],
            'json' => $json,
            'html' => $this->descriptionHtml($name, $copy),
        ];
    }

    private function headingNode(int $level, string $text): array
    {
        return [
            'type' => 'heading',
            'attrs' => ['level' => $level],
            'content' => [[
                'type' => 'text',
                'text' => $text,
            ]],
        ];
    }

    private function paragraphNode(string $text): array
    {
        return [
            'type' => 'paragraph',
            'content' => [[
                'type' => 'text',
                'text' => $text,
            ]],
        ];
    }

    private function descriptionHtml(string $name, array $copy): string
    {
        $escape = fn (string $value): string => htmlspecialchars(
            $value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8',
        );

        $items = collect($copy['highlights'])
            ->map(fn (string $item): string => '<li>'.$escape($item).'</li>')
            ->implode('');

        return '<h2>Overview</h2>'
            .'<p><strong>'.$escape($name).'</strong> — '.$escape($copy['overview']).'</p>'
            .'<h3>Highlights</h3>'
            .'<ul>'.$items.'</ul>'
            .'<h3>Good to know</h3>'
            .'<p>'.$escape($copy['note']).'</p>';
    }

    private function productDescriptionCatalog(): array
    {
        return [
            'sony-wh-ch720n' => [
                'short' => 'Lightweight wireless noise-cancelling headphones designed for comfortable everyday listening, calls and commuting.',
                'overview' => 'Sony WH-CH720N combines wireless convenience with active noise cancellation in a lightweight over-ear design suited to music, calls and long listening sessions.',
                'highlights' => [
                    'Noise-cancelling over-ear design helps reduce everyday background distractions.',
                    'Lightweight fit is practical for commuting, study sessions and office use.',
                    'Wireless connectivity keeps daily listening simple across compatible devices.',
                ],
                'note' => 'For the best comfort and sound, adjust the headband so the earcups sit evenly around your ears. Actual battery life and noise reduction vary with volume, settings and environment.',
            ],
            'wiz-a60-e27-smart-bulb' => [
                'short' => 'A connected E27 LED bulb for app-controlled lighting, schedules and flexible everyday ambience.',
                'overview' => 'WiZ A60 E27 Smart LED Bulb is an easy way to add connected lighting to a compatible lamp or fixture without replacing the whole fitting.',
                'highlights' => [
                    'Control compatible lighting functions from the WiZ app.',
                    'Useful for schedules, routines and changing the room ambience through the day.',
                    'Standard E27 format makes it suitable for many common household fittings.',
                ],
                'note' => 'Check that your lamp uses an E27 socket and supports the bulb size before ordering. Smart features depend on your home network and compatible app setup.',
            ],
            'fresh-atta-2kg' => [
                'short' => 'A practical 2kg pack of atta for everyday roti, chapati and home cooking.',
                'overview' => 'Fresh Atta 2kg is a convenient pantry staple for households that prepare roti, chapati and other wheat-based dishes regularly.',
                'highlights' => [
                    '2kg pack is easy to store and suitable for routine household use.',
                    'Useful for soft flatbreads and a range of everyday recipes.',
                    'Sealed retail packaging helps keep the flour protected before opening.',
                ],
                'note' => 'After opening, transfer the flour to a clean airtight container and store it in a cool, dry place away from moisture.',
            ],
            'teer-nazirshail-rice-5kg' => [
                'short' => 'A 5kg pack of Nazirshail rice for aromatic everyday meals and traditional Bangladeshi dishes.',
                'overview' => 'Teer Nazirshail Rice 5kg is suited to families looking for a fragrant rice option for regular meals, pulao-style dishes and special occasions.',
                'highlights' => [
                    'Nazirshail-style rice is valued for its familiar aroma and fine grain.',
                    '5kg pack is practical for family kitchens.',
                    'Works well with curries, vegetables, fish and meat dishes.',
                ],
                'note' => 'Rinse before cooking and adjust the water ratio to your preferred grain softness. Store the opened rice in a dry, covered container.',
            ],
            'fresh-soyabean-oil-2l' => [
                'short' => 'A 2L bottle of soybean oil for frying, sautéing and everyday home cooking.',
                'overview' => 'Fresh Soyabean Oil 2L provides a convenient everyday cooking-oil size for family kitchens and routine meal preparation.',
                'highlights' => [
                    'Suitable for common cooking methods including frying and sautéing.',
                    '2L bottle balances household capacity with manageable storage.',
                    'Sealed bottle format helps make pantry handling straightforward.',
                ],
                'note' => 'Keep the bottle capped and store it away from direct sunlight and excessive heat. Follow the label guidance for storage and best-before information.',
            ],
            'orix-crystal-detergent-powder-2kg' => [
                'short' => 'A family-size 2kg detergent powder for routine washing and everyday laundry care.',
                'overview' => 'Orix Crystal Detergent Powder 2kg is intended for regular household laundry, giving you a larger pack for frequent washing.',
                'highlights' => [
                    '2kg pack is convenient for households with recurring laundry loads.',
                    'Suitable for everyday garments and general washing routines.',
                    'Powder format makes it easy to measure the amount needed for each load.',
                ],
                'note' => 'Use the amount recommended on the package and follow garment care labels. Keep detergent dry, sealed and out of reach of children.',
            ],
            'redmi-note-13' => [
                'short' => 'A versatile Redmi smartphone with storage options for everyday apps, media, photography and communication.',
                'overview' => 'Redmi Note 13 is a practical all-round smartphone for users who want a modern display experience, dependable daily performance and a choice of storage configurations.',
                'highlights' => [
                    'Available in seeded 6/128GB and 8/256GB configurations.',
                    'Well suited to messaging, social media, streaming and everyday photography.',
                    'Large-screen smartphone format is useful for reading, video and general productivity.',
                ],
                'note' => 'Choose the storage variant that best matches how many apps, photos and videos you keep locally. Network support, battery life and camera results vary by usage and environment.',
            ],
            'storez-classic-cotton-shirt' => [
                'short' => 'A comfortable black cotton shirt with four practical size choices for everyday wear.',
                'overview' => 'StoreZ Classic Cotton Shirt is an easy everyday wardrobe staple in a versatile black finish, with Small through XL variants for a cleaner demo of size-based product options.',
                'highlights' => [
                    'Soft cotton construction is suited to regular casual and smart-casual outfits.',
                    'Black color pairs easily with jeans, chinos and layered everyday looks.',
                    'Small, Medium, Large and XL variants keep the sizing flow easy to test in the storefront.',
                ],
                'note' => 'Check the selected size before ordering and follow the garment care label when washing. Fit preference can vary by body shape and styling.',
            ],
            'nivea-soft-300ml' => [
                'short' => 'A light 300ml moisturizer for everyday face, hand and body care.',
                'overview' => 'NIVEA Soft Light Moisturizer 300ml is a versatile daily moisturizer with a lighter texture for regular skin-care routines.',
                'highlights' => [
                    'Large 300ml format is convenient for shared or frequent use.',
                    'Suitable for moisturizing common dry areas such as hands, arms and face depending on your skin routine.',
                    'Soft cream texture spreads easily, so a small amount can cover a wider area.',
                ],
                'note' => 'Skin needs differ from person to person. Patch test if you are sensitive to fragranced skin-care products and discontinue use if irritation occurs.',
            ],
            'miyako-mjk-805-kettle' => [
                'short' => 'A 1.8L electric kettle with a steel body for quick everyday hot-water preparation.',
                'overview' => 'Miyako MJK-805 Electric Kettle 1.8L is a straightforward countertop appliance for preparing hot water for tea, coffee and quick kitchen tasks.',
                'highlights' => [
                    '1.8L capacity is practical for preparing several cups at once.',
                    'Steel construction gives the kettle a simple, durable kitchen look.',
                    'Electric heating is convenient when you need hot water without using the stove.',
                ],
                'note' => 'The kettle body can become hot during use. Always handle it by the designated handle, keep the electrical base dry and follow the manufacturer safety instructions.',
            ],
            'samsung-galaxy-a15-5g' => [
                'short' => 'A Samsung 5G smartphone for everyday communication, media, apps and mobile productivity.',
                'overview' => 'Samsung Galaxy A15 5G is positioned as an accessible everyday smartphone for users who want Samsung software, 5G connectivity and a modern large-screen experience.',
                'highlights' => [
                    '5G-capable model for compatible networks and plans.',
                    'Suitable for calls, messaging, streaming, navigation and common mobile apps.',
                    'Samsung interface provides a familiar Android experience for Galaxy users.',
                ],
                'note' => '5G availability depends on your carrier and location. Real-world battery life, charging speed and camera performance depend on usage conditions.',
            ],
            'walton-wcw-comc70-cookware-set' => [
                'short' => 'A coordinated 7-in-1 cookware combo for everyday family cooking and kitchen setup.',
                'overview' => 'Walton WCW-COMC70 Cookware 7-in-1 Combo brings several matching cookware pieces together in one practical set for routine meal preparation.',
                'highlights' => [
                    'Multiple cookware sizes help cover different recipes and portion sizes.',
                    'Matching pieces create a consistent kitchen setup.',
                    'Useful for new kitchens or replacing several everyday cookware items together.',
                ],
                'note' => 'Use utensils and heat levels appropriate for the cooking surface. Allow cookware to cool before washing and follow the care guidance supplied with the set.',
            ],
            'apex-95910a47-casual-shoe' => [
                'short' => 'A casual Apex shoe designed for comfortable everyday wear, commuting and easy maintenance.',
                'overview' => 'Apex Men\'s Washable Casual Shoe 95910A47 is a practical everyday pair for users who want a simple casual profile that works with regular outfits.',
                'highlights' => [
                    'Casual styling suits commuting, errands and relaxed daily wear.',
                    'Washable design makes routine cleaning more convenient.',
                    'Light everyday silhouette pairs easily with jeans, chinos and casual trousers.',
                ],
                'note' => 'Use the Apex size guide before ordering. Allow shoes to air-dry naturally after cleaning and avoid strong direct heat.',
            ],
            'mr-noodles-magic-masala-16-pack' => [
                'short' => 'A family-size multipack of Magic Masala instant noodles for quick snacks and easy meals.',
                'overview' => 'Mr Noodles Magic Masala 16 Pack is a convenient pantry option when you want several individual noodle portions ready for quick preparation.',
                'highlights' => [
                    '16-pack format is practical for families or stocking the pantry.',
                    'Quick preparation makes it useful for busy evenings and simple snacks.',
                    'Masala seasoning provides the familiar savory flavor associated with the range.',
                ],
                'note' => 'Follow the preparation instructions printed on the pack. You can add egg, vegetables or other ingredients to make a more complete meal.',
            ],
            'ikea-taernaby-table-lamp' => [
                'short' => 'A compact vintage-inspired table lamp for warm ambient lighting on desks, shelves and bedside tables.',
                'overview' => 'IKEA TÄRNABY Table Lamp is designed as an atmospheric accent light, combining a compact footprint with a distinctive traditional-lantern-inspired appearance.',
                'highlights' => [
                    'Works well as bedside, shelf or corner accent lighting.',
                    'Decorative form adds character even when the lamp is switched off.',
                    'Best suited to warm ambient light rather than strong room-wide illumination.',
                ],
                'note' => 'Choose a compatible bulb and confirm local electrical requirements. Final brightness and warmth depend heavily on the bulb used.',
            ],
            'sony-wh-ch520' => [
                'short' => 'Lightweight wireless on-ear headphones for music, calls, study and everyday listening.',
                'overview' => 'Sony WH-CH520 Wireless Headphones focus on simple cable-free listening in a compact on-ear design that is easy to use throughout the day.',
                'highlights' => [
                    'Lightweight on-ear format is convenient for work, study and travel.',
                    'Wireless connectivity reduces cable clutter during everyday listening.',
                    'Suitable for music, podcasts, online classes and voice calls.',
                ],
                'note' => 'This model is best for users who prioritize lightweight wireless listening rather than active noise cancellation. Comfort will vary with head and ear shape.',
            ],
            'sony-srs-xb100' => [
                'short' => 'A compact portable Bluetooth speaker for personal listening, small rooms and travel.',
                'overview' => 'Sony SRS-XB100 Portable Bluetooth Speaker packs wireless audio into a small carry-friendly format for desks, trips and casual gatherings.',
                'highlights' => [
                    'Compact body is easy to move between rooms or take on the go.',
                    'Bluetooth playback works with compatible phones, tablets and computers.',
                    'Useful for podcasts, background music and casual listening where a full-size speaker is unnecessary.',
                ],
                'note' => 'A compact speaker cannot deliver the same scale as a large home speaker. Keep expectations focused on portability, convenience and near-field listening.',
            ],
            'samsung-galaxy-a25-5g' => [
                'short' => 'A mid-range Samsung 5G smartphone for streaming, photography, communication and daily apps.',
                'overview' => 'Samsung Galaxy A25 5G offers a balanced Galaxy experience for users who want a modern smartphone with 5G connectivity and enough capability for everyday work and entertainment.',
                'highlights' => [
                    '5G connectivity for supported carriers and coverage areas.',
                    'Well suited to video, social media, navigation and routine mobile productivity.',
                    'Galaxy software experience is familiar to existing Samsung users.',
                ],
                'note' => 'Actual network speed, battery endurance and camera output depend on signal quality, app usage, brightness and other settings.',
            ],
            'redmi-buds-5' => [
                'short' => 'Compact Redmi true wireless earbuds for everyday music, calls and commuting.',
                'overview' => 'Redmi Buds 5 provides a cable-free earbud option for users who want pocketable audio for daily travel, work and casual listening.',
                'highlights' => [
                    'True wireless design keeps the setup compact and portable.',
                    'Charging case makes it easy to store the earbuds between listening sessions.',
                    'Useful for music, podcasts, calls and commuting.',
                ],
                'note' => 'Earbud fit has a major effect on comfort and perceived sound. Try the supplied ear-tip sizes where available and keep the charging contacts clean.',
            ],
            'redmi-note-14' => [
                'short' => 'A modern Redmi Note smartphone aimed at everyday media, communication, apps and photography.',
                'overview' => 'Redmi Note 14 is a practical daily smartphone for users who want a large-screen Redmi experience with room for social apps, media and routine productivity.',
                'highlights' => [
                    'Suitable for messaging, streaming, navigation and everyday photography.',
                    'Large-screen format makes reading and video viewing comfortable.',
                    'Designed as an all-round device rather than a single-purpose specialist phone.',
                ],
                'note' => 'Performance varies by regional configuration and software version. Check the exact RAM, storage, network and charger details for the unit you are purchasing.',
            ],
            'wiz-smart-plug' => [
                'short' => 'A WiZ-connected smart plug for app-based control, schedules and simple home automation.',
                'overview' => 'WiZ Smart Plug lets you add connected on/off control to a compatible appliance without replacing the appliance itself.',
                'highlights' => [
                    'Useful for lamps and other compatible plug-in devices.',
                    'Schedules can automate simple daily routines.',
                    'Remote app control adds convenience when the plug is connected to your home network.',
                ],
                'note' => 'Check the plug type and electrical load rating before use. Do not connect appliances that exceed the product rating or are unsuitable for unattended switching.',
            ],
            'nivea-men-deep-face-wash' => [
                'short' => 'A 100g men’s face wash for routine cleansing after commuting, work and daily activity.',
                'overview' => 'NIVEA Men Deep Face Wash 100g is intended as a straightforward cleansing step for men who want to remove everyday oil, sweat and surface dirt.',
                'highlights' => [
                    'Convenient tube format for bathroom or travel use.',
                    'Suitable for a simple morning or evening cleansing routine.',
                    'A small amount can be worked with water before rinsing thoroughly.',
                ],
                'note' => 'Avoid the eye area and discontinue use if irritation develops. If your skin feels dry after cleansing, follow with a moisturizer suited to your skin type.',
            ],
            'nivea-creme-150ml' => [
                'short' => 'A classic rich moisturizing cream in a 150ml format for dry areas and everyday skin care.',
                'overview' => 'NIVEA Creme 150ml is a richer moisturizer suited to users who prefer a more substantial cream for hands, elbows and other dry areas.',
                'highlights' => [
                    'Rich cream texture is useful where lighter lotions do not feel sufficient.',
                    '150ml format is convenient for regular home use.',
                    'Works well as part of a simple dry-skin care routine.',
                ],
                'note' => 'Because the texture is rich, start with a small amount and add more as needed. Patch test first if you have sensitive skin.',
            ],
            'miyako-blender-bl-152' => [
                'short' => 'A countertop blender for smoothies, chutney, sauces and routine kitchen preparation.',
                'overview' => 'Miyako BL-152 Blender is a practical small appliance for everyday blending jobs where hand mixing would be slower or less consistent.',
                'highlights' => [
                    'Useful for shakes, smoothies, sauces and similar kitchen tasks.',
                    'Simple controls keep everyday operation straightforward.',
                    'Countertop format is convenient for frequent home preparation.',
                ],
                'note' => 'Do not overfill the jar, and avoid blending ingredients that are too hard or too hot unless the manufacturer specifically allows it. Unplug before cleaning.',
            ],
            'walton-rice-cooker-wrc-sgae28' => [
                'short' => 'An electric Walton rice cooker for convenient everyday rice preparation and keep-warm use.',
                'overview' => 'Walton WRC-SGAE28 Rice Cooker simplifies routine rice preparation so you can focus on the rest of the meal while the cooker handles the main cooking cycle.',
                'highlights' => [
                    'Electric cooking removes the need to monitor a stovetop pot continuously.',
                    'Useful for family meals and repeated weekly cooking.',
                    'Keep-warm functionality is convenient when serving times vary.',
                ],
                'note' => 'Use the supplied measuring guidance and avoid metal utensils that may scratch the inner pot. Keep the heating plate and exterior electrical parts dry.',
            ],
            'ikea-kallax-shelf-unit' => [
                'short' => 'A versatile IKEA cube-style shelf unit for books, display pieces, baskets and everyday organization.',
                'overview' => 'IKEA KALLAX Shelf Unit is built around simple square compartments, making it easy to use for open display or combine with compatible boxes and inserts.',
                'highlights' => [
                    'Flexible cube layout works for books, décor and storage baskets.',
                    'Clean design fits bedrooms, living rooms and work areas.',
                    'Open compartments make frequently used items easy to reach.',
                ],
                'note' => 'Follow IKEA assembly and wall-anchoring guidance for your exact configuration. Load limits depend on placement, assembly and how the unit is secured.',
            ],
            'apex-mens-sports-shoe' => [
                'short' => 'An Apex sports-style shoe for walking, commuting and light everyday activity.',
                'overview' => 'Apex Men\'s Sports Shoe is a versatile casual athletic-style option for daily wear, walking and light activity rather than specialized performance training.',
                'highlights' => [
                    'Sport-inspired shape pairs well with everyday casual clothing.',
                    'Suitable for commuting, walking and routine errands.',
                    'Black color is practical and easy to coordinate with different outfits.',
                ],
                'note' => 'Choose the correct size and allow a short break-in period if needed. For serious running or sport-specific training, use footwear designed for that activity.',
            ],
            'fresh-refined-sugar-1kg' => [
                'short' => 'A 1kg pack of refined sugar for tea, desserts, baking and routine household use.',
                'overview' => 'Fresh Refined Sugar 1kg is a straightforward pantry staple for sweetening drinks, baking and everyday recipes.',
                'highlights' => [
                    '1kg size is easy to store and suitable for normal household use.',
                    'Useful for tea, coffee, desserts and home baking.',
                    'Sealed retail packaging protects the sugar before opening.',
                ],
                'note' => 'Keep sugar in a cool, dry place. After opening, an airtight container helps prevent moisture and clumping.',
            ],
            'pran-chanachur-300g' => [
                'short' => 'A 300g pack of crunchy PRAN chanachur for tea-time snacks, sharing and casual entertaining.',
                'overview' => 'PRAN Chanachur 300g is a ready-to-eat savory snack that works well for tea-time, small gatherings and keeping in the pantry for quick serving.',
                'highlights' => [
                    '300g pack is convenient for sharing.',
                    'Crunchy mixed snack format pairs naturally with tea and soft drinks.',
                    'Ready to serve without preparation.',
                ],
                'note' => 'Seal the packet tightly after opening or transfer the contents to an airtight container to help maintain crispness.',
            ],
            'samsung-galaxy-buds-fe' => [
                'short' => 'Compact Samsung true wireless earbuds for music, calls and everyday Galaxy-device use.',
                'overview' => 'Samsung Galaxy Buds FE is a compact true wireless option for users who want easy portable audio for commuting, calls and daily listening.',
                'highlights' => [
                    'Cord-free earbud format is convenient for travel and everyday carry.',
                    'Charging case keeps the earbuds protected between sessions.',
                    'Pairs naturally with compatible Galaxy devices while also serving as general Bluetooth earbuds.',
                ],
                'note' => 'Comfort and sound depend heavily on ear-tip fit. Keep the earbuds and case contacts clean, and confirm feature compatibility with your specific phone.',
            ],
            'sony-wf-c700n' => [
                'short' => 'Compact Sony wireless noise-cancelling earbuds for commuting, calls and everyday listening.',
                'overview' => 'Sony WF-C700N Wireless Noise Cancelling Earbuds combine a pocketable true wireless format with noise-reduction features aimed at everyday travel and personal listening.',
                'highlights' => [
                    'Noise-cancelling design can help reduce common background distractions.',
                    'Compact charging case makes the set easy to carry.',
                    'Suitable for music, spoken audio and calls throughout the day.',
                ],
                'note' => 'Noise cancellation is most effective with a secure ear-tip fit. Actual battery life and call quality vary with settings, signal conditions and environment.',
            ],
            'redmi-watch-5-active' => [
                'short' => 'A Redmi smartwatch for notifications, activity tracking and convenient everyday wrist access.',
                'overview' => 'Redmi Watch 5 Active is designed for users who want a larger wrist display for notifications, basic activity tracking and everyday convenience without constantly reaching for a phone.',
                'highlights' => [
                    'Large wearable display makes notifications and basic information easy to glance at.',
                    'Useful for daily steps, simple workouts and routine activity awareness.',
                    'Light smartwatch format works for everyday wear.',
                ],
                'note' => 'Fitness and health readings are intended for general wellness tracking, not medical diagnosis. Feature availability may depend on the paired phone and app version.',
            ],
            'xiaomi-power-bank-4i-20000mah' => [
                'short' => 'A 20,000mAh Xiaomi power bank with 33W-class charging for travel and long days away from an outlet.',
                'overview' => 'Xiaomi Power Bank 4i 20000mAh 33W provides a large portable battery reserve for phones and other compatible USB-powered devices when wall charging is inconvenient.',
                'highlights' => [
                    '20,000mAh rated capacity is useful for travel and extended days outside.',
                    '33W-class charging can support faster charging with compatible devices and cables.',
                    'Portable design lets you top up phones and accessories away from a wall outlet.',
                ],
                'note' => 'The power bank is heavier than low-capacity models, and usable output is lower than the raw cell rating because of conversion losses. Use compatible cables and chargers.',
            ],
            'nivea-men-creme-75ml' => [
                'short' => 'A compact 75ml men’s moisturizing cream for face, hands and dry areas.',
                'overview' => 'NIVEA Men Creme 75ml offers a compact moisturizer for men who want one small product for everyday face, hand and body care.',
                'highlights' => [
                    '75ml size is convenient for a desk, gym bag or travel kit.',
                    'Useful for hands, face and common dry areas depending on your skin needs.',
                    'Cream format is easy to apply in small amounts.',
                ],
                'note' => 'Apply a small amount first, especially in warm or humid weather. Stop using the product if you notice persistent irritation.',
            ],
            'ikea-lack-side-table' => [
                'short' => 'A compact 55x55cm IKEA LACK side table for small rooms, bedside use and simple everyday surfaces.',
                'overview' => 'IKEA LACK Side Table 55x55cm is a lightweight, minimal table that works well where you need a straightforward surface without visually filling the room.',
                'highlights' => [
                    '55x55cm footprint is useful in smaller living areas.',
                    'Simple design works beside sofas, beds and reading chairs.',
                    'Lightweight format makes occasional repositioning easy.',
                ],
                'note' => 'Follow the assembly instructions carefully and stay within the stated load limit. Use coasters or mats to help protect the surface from heat, moisture and scratches.',
            ],
            'apex-mens-black-leather-sandal-92212a60' => [
                'short' => 'A black Apex men’s leather sandal for relaxed everyday wear and warm-weather comfort.',
                'overview' => 'Apex Men\'s Black Leather Sandal 92212A60 is a simple everyday sandal designed for casual use, family visits and warm-weather routines.',
                'highlights' => [
                    'Black finish pairs easily with casual and traditional clothing.',
                    'Open sandal design is convenient in warm weather.',
                    'Leather upper gives the pair a more refined everyday appearance.',
                ],
                'note' => 'Check the Apex size guide before ordering. Keep leather away from prolonged soaking and allow the sandals to dry naturally if they become damp.',
            ],
            'fresh-mustard-oil-1l' => [
                'short' => 'A 1L bottle of mustard oil for traditional cooking, marinades and everyday Bangladeshi recipes.',
                'overview' => 'Fresh Mustard Oil 1L is a pantry option for cooks who want the distinctive mustard-oil character used in many traditional dishes.',
                'highlights' => [
                    '1L size is convenient for regular household cooking.',
                    'Distinctive aroma works well in a variety of traditional recipes.',
                    'Bottle format is easy to store alongside other cooking oils.',
                ],
                'note' => 'Mustard oil has a strong flavor, so adjust the amount to your recipe and preference. Store the bottle sealed, away from direct sunlight and excessive heat.',
            ],
            'pran-frooto-mango-drink-1l' => [
                'short' => 'A 1L mango fruit drink for chilled serving at home, meals and casual gatherings.',
                'overview' => 'PRAN Frooto Mango Fruit Drink 1L is a ready-to-serve beverage option for families and guests who enjoy a sweet mango-flavored drink.',
                'highlights' => [
                    '1L pack is convenient for sharing.',
                    'Ready to serve chilled with meals or snacks.',
                    'Mango flavor makes it an easy crowd-friendly beverage choice.',
                ],
                'note' => 'Shake if directed on the package, refrigerate after opening and follow the label guidance for storage and consumption.',
            ],
            'samsung-galaxy-a35-5g' => [
                'short' => 'A Samsung 5G smartphone with 128GB and 256GB storage choices for everyday apps, media and photography.',
                'overview' => 'Samsung Galaxy A35 5G is a balanced mid-range Galaxy phone for users who want a bright large-screen experience, 5G connectivity and a choice of two practical storage capacities.',
                'highlights' => [
                    'Choose between seeded 8/128GB and 8/256GB storage variants.',
                    'Well suited to messaging, streaming, navigation, social apps and everyday photography.',
                    'Galaxy software and expandable-storage support make it flexible for long-term daily use.',
                ],
                'note' => 'Storage and network availability can vary by market. Usable storage is lower than the advertised capacity after system files and preinstalled software.',
            ],
            'sony-ult-wear-wh-ult900n' => [
                'short' => 'Sony wireless noise-cancelling headphones with three color choices for bass-focused everyday listening.',
                'overview' => 'Sony ULT WEAR WH-ULT900N combines active noise cancellation, a comfortable over-ear fit and bass-focused ULT sound in Black, Off White and Forest Gray variants.',
                'highlights' => [
                    'Three color variants let the storefront demonstrate image switching by option.',
                    'Noise-cancelling over-ear design is useful for commuting, travel and focused listening.',
                    'Wireless playback keeps music, podcasts and calls convenient throughout the day.',
                ],
                'note' => 'Actual battery endurance and noise reduction depend on volume, settings and environment. Color appearance may vary slightly with lighting and display calibration.',
            ],
            'aarong-black-embroidered-cotton-panjabi' => [
                'short' => 'A black embroidered cotton Aarong panjabi with four size variants for festive and smart traditional wear.',
                'overview' => 'Aarong Black Embroidered Cotton Panjabi combines a black cotton base with matching embroidery and a traditional long silhouette, seeded here with four commonly useful size choices.',
                'highlights' => [
                    'Cotton construction is suitable for traditional occasion wear and longer events.',
                    'Black embroidered styling pairs easily with white or neutral pajama and trousers.',
                    'Size variants 40, 42, 44 and 46 provide a realistic apparel selection flow.',
                ],
                'note' => 'Review the size guide before ordering because garment measurements and preferred ease can vary. Follow the care instructions supplied with the garment.',
            ],
            'apex-formal-shoe-91313a15' => [
                'short' => 'A black Apex formal shoe with five seeded size variants for office, events and polished everyday wear.',
                'overview' => 'Apex Men\'s Formal Shoe 91313A15 is a black slip-on formal style designed for workdays and dressier occasions, with five size variants to exercise the StoreZ footwear option flow.',
                'highlights' => [
                    'Black formal styling works with office trousers, suits and traditional formal outfits.',
                    'Slip-on construction keeps everyday wear straightforward.',
                    'Sizes 39 through 43 provide five realistic seeded variants without overcomplicating the demo.',
                ],
                'note' => 'Use the Apex size guide before ordering. Leather and dark finishes can show natural variation, and footwear comfort depends on foot shape as well as nominal size.',
            ],
            'ikea-kallax-77x77-shelf-unit' => [
                'short' => 'A compact IKEA KALLAX 77x77cm shelf unit in white or black-brown for flexible open storage.',
                'overview' => 'IKEA KALLAX 77x77cm Shelf Unit uses four open compartments for books, baskets and display pieces, with White and Black-Brown variants that are visually distinct enough to benefit from variant-specific images.',
                'highlights' => [
                    'Two color variants demonstrate color-based furniture selection in the StoreZ catalog.',
                    'Four open compartments work for books, baskets, decor and everyday organization.',
                    'Compact square footprint suits bedrooms, living rooms and home-office storage.',
                ],
                'note' => 'Follow the IKEA assembly and wall-anchoring guidance for your installation. Final color can look different under warm or cool room lighting.',
            ],
        ];
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
                'stock' => 0,
                'is_featured' => true,
                'categories' => ['electronics', 'phones'],
                'tags' => ['new-arrival', 'best-seller'],
                'attributes' => ['warranty' => '1-year'],
                'options' => [
                    [
                        'name' => 'Color',
                        'slug' => 'color',
                        'values' => [
                            ['label' => 'Black', 'slug' => 'black'],
                            ['label' => 'Blue', 'slug' => 'blue'],
                        ],
                    ],
                    [
                        'name' => 'Storage',
                        'slug' => 'storage',
                        'values' => [
                            ['label' => '6/128GB', 'slug' => '6-128gb'],
                            ['label' => '8/256GB', 'slug' => '8-256gb'],
                        ],
                    ],
                ],
                'variants' => [
                    'color=black|storage=6-128gb' => [
                        'sku' => 'STZ-REDMI13-BLK-6128', 'price' => 20999, 'stock' => 14,
                        'images' => ['variants/redmi-note-13-black.jpg'],
                    ],
                    'color=black|storage=8-256gb' => [
                        'sku' => 'STZ-REDMI13-BLK-8256', 'price' => 22999, 'stock' => 9,
                        'images' => ['variants/redmi-note-13-black.jpg'],
                    ],
                    'color=blue|storage=6-128gb' => [
                        'sku' => 'STZ-REDMI13-BLU-6128', 'price' => 21499, 'stock' => 11,
                        'images' => ['variants/redmi-note-13-blue.jpg'],
                    ],
                    'color=blue|storage=8-256gb' => [
                        'sku' => 'STZ-REDMI13-BLU-8256', 'price' => 23499, 'stock' => 7,
                        'images' => ['variants/redmi-note-13-blue.jpg'],
                    ],
                ],
                'image' => 'products/redmi-note-13.png',
            ],
            [
                'slug' => 'storez-classic-cotton-shirt',
                'name' => 'StoreZ Classic Cotton Shirt',
                'type' => 'variable',
                'price' => 899,
                'stock' => 0,
                'is_featured' => false,
                'categories' => ['fashion', 'mens-fashion'],
                'tags' => ['new-arrival'],
                'attributes' => ['material' => 'cotton'],
                'options' => [
                    [
                        'name' => 'Size',
                        'slug' => 'size',
                        'values' => [
                            ['label' => 'Small', 'slug' => 'small'],
                            ['label' => 'Medium', 'slug' => 'medium'],
                            ['label' => 'Large', 'slug' => 'large'],
                            ['label' => 'XL', 'slug' => 'xl'],
                        ],
                    ],
                ],
                'variants' => [
                    'size=small' => [
                        'sku' => 'STZ-SHIRT-BLK-S', 'price' => 899, 'stock' => 18,
                        'images' => ['variants/storez-classic-shirt-black.webp'],
                    ],
                    'size=medium' => [
                        'sku' => 'STZ-SHIRT-BLK-M', 'price' => 949, 'stock' => 25,
                        'images' => ['variants/storez-classic-shirt-black.webp'],
                    ],
                    'size=large' => [
                        'sku' => 'STZ-SHIRT-BLK-L', 'price' => 999, 'stock' => 12,
                        'images' => ['variants/storez-classic-shirt-black.webp'],
                    ],
                    'size=xl' => [
                        'sku' => 'STZ-SHIRT-BLK-XL', 'price' => 1049, 'stock' => 8,
                        'images' => ['variants/storez-classic-shirt-black.webp'],
                    ],
                ],
                'image' => 'variants/storez-classic-shirt-black.webp',
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
                'stock' => 48,
                'is_featured' => true,
                'categories' => ['electronics', 'phones'],
                'tags' => ['new-arrival'],
                'attributes' => ['warranty' => '1-year'],
                'image' => 'products/samsung-galaxy-a25-5g.webp',
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
                'stock' => 52,
                'is_featured' => true,
                'categories' => ['electronics', 'phones'],
                'tags' => ['new-arrival'],
                'attributes' => ['warranty' => '1-year'],
                'image' => 'products/redmi-note-14.webp',
            ],
            [
                'slug' => 'wiz-smart-plug',
                'name' => 'WiZ Smart Plug',
                'brand' => 'wiz',
                'type' => 'simple',
                'price' => 1890,
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
                'stock' => 32,
                'is_featured' => true,
                'categories' => ['home-living'],
                'tags' => ['premium'],
                'attributes' => [],
                'image' => 'products/ikea-kallax-shelf-unit.jpg',
            ],
            [
                'slug' => 'apex-mens-sports-shoe',
                'name' => 'Apex Men\'s Sports Shoe',
                'brand' => 'apex',
                'type' => 'simple',
                'price' => 3290,
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
                'stock' => 190,
                'is_featured' => false,
                'categories' => ['groceries'],
                'tags' => ['budget-friendly'],
                'attributes' => ['weight' => '1kg'],
                'image' => 'products/fresh-refined-sugar-1kg.webp',
            ],
            [
                'slug' => 'pran-chanachur-300g',
                'name' => 'PRAN Chanachur 300g',
                'brand' => 'pran',
                'type' => 'simple',
                'price' => 120,
                'stock' => 170,
                'is_featured' => false,
                'categories' => ['groceries'],
                'tags' => ['best-seller', 'budget-friendly'],
                'attributes' => [],
                'image' => 'products/pran-chanachur-300g.webp',
            ],
            [
                'slug' => 'samsung-galaxy-buds-fe',
                'name' => 'Samsung Galaxy Buds FE',
                'brand' => 'samsung',
                'type' => 'simple',
                'price' => 8999,
                'stock' => 74,
                'is_featured' => true,
                'categories' => ['electronics', 'audio'],
                'tags' => ['best-seller'],
                'attributes' => ['warranty' => '1-year', 'color' => 'white'],
                'image' => 'products/samsung-galaxy-buds-fe.jpg',
            ],
            [
                'slug' => 'sony-wf-c700n',
                'name' => 'Sony WF-C700N Wireless Noise Cancelling Earbuds',
                'brand' => 'sony',
                'type' => 'simple',
                'price' => 10990,
                'stock' => 61,
                'is_featured' => false,
                'categories' => ['electronics', 'audio'],
                'tags' => ['new-arrival'],
                'attributes' => ['warranty' => '1-year', 'color' => 'black'],
                'images' => [
                    'products/sony-wf-c700n-2.jpg',
                    'products/sony-wf-c700n-22.jpg',
                    'products/sony-wf-c700n.jpg',
                ],
            ],
            [
                'slug' => 'redmi-watch-5-active',
                'name' => 'Redmi Watch 5 Active',
                'brand' => 'xiaomi',
                'type' => 'simple',
                'price' => 4999,
                'old_price' => 5499,
                'stock' => 88,
                'is_featured' => true,
                'categories' => ['electronics'],
                'tags' => ['new-arrival', 'on-sale'],
                'attributes' => ['warranty' => '1-year', 'color' => 'black'],
                'image' => 'products/redmi-watch-5-active.jpg',
            ],
            [
                'slug' => 'xiaomi-power-bank-4i-20000mah',
                'name' => 'Xiaomi Power Bank 4i 20000mAh 33W',
                'brand' => 'xiaomi',
                'type' => 'simple',
                'price' => 3999,
                'stock' => 96,
                'is_featured' => false,
                'categories' => ['electronics'],
                'tags' => ['best-seller'],
                'attributes' => ['warranty' => '6-months', 'color' => 'black'],
                'images' => [
                    'products/xiaomi-power-bank-4i.jpg',
                    'products/xiaomi-power-bank-4i-20000mah.jpg',
                ],
            ],
            [
                'slug' => 'nivea-men-creme-75ml',
                'name' => 'NIVEA Men Creme 75ml',
                'brand' => 'nivea',
                'type' => 'simple',
                'price' => 430,
                'old_price' => 490,
                'stock' => 138,
                'is_featured' => false,
                'categories' => ['beauty'],
                'tags' => ['on-sale'],
                'attributes' => [],
                'image' => 'products/nivea-men-creme.jpg',
            ],
            [
                'slug' => 'ikea-lack-side-table',
                'name' => 'IKEA LACK Side Table 55x55cm',
                'brand' => 'ikea',
                'type' => 'simple',
                'price' => 2490,
                'stock' => 44,
                'is_featured' => false,
                'categories' => ['home-living'],
                'tags' => ['budget-friendly'],
                'attributes' => ['color' => 'white'],
                'image' => 'products/ikea-lack-side-table.jpg',
            ],
            [
                'slug' => 'apex-mens-black-leather-sandal-92212a60',
                'name' => 'Apex Men\'s Black Leather Sandal 92212A60',
                'brand' => 'apex',
                'type' => 'simple',
                'price' => 1290,
                'stock' => 77,
                'is_featured' => false,
                'categories' => ['fashion', 'mens-fashion'],
                'tags' => ['best-seller'],
                'attributes' => ['color' => 'black'],
                'image' => 'products/apex-mens-black-leather-sandal-92212a60.jpg',
            ],
            [
                'slug' => 'fresh-mustard-oil-1l',
                'name' => 'Fresh Mustard Oil 1L',
                'brand' => 'fresh',
                'type' => 'simple',
                'price' => 330,
                'stock' => 165,
                'is_featured' => false,
                'categories' => ['groceries'],
                'tags' => ['best-seller'],
                'attributes' => [],
                'image' => 'products/fresh-mustard-oil.webp',
            ],
            [
                'slug' => 'pran-frooto-mango-drink-1l',
                'name' => 'PRAN Frooto Mango Fruit Drink 1L',
                'brand' => 'pran',
                'type' => 'simple',
                'price' => 80,
                'old_price' => 90,
                'stock' => 190,
                'is_featured' => false,
                'categories' => ['groceries'],
                'tags' => ['budget-friendly', 'on-sale'],
                'attributes' => [],
                'image' => 'products/pran-frooto-mango-drink-1l.jpg',
            ],
            [
                'slug' => 'samsung-galaxy-a35-5g',
                'name' => 'Samsung Galaxy A35 5G',
                'brand' => 'samsung',
                'type' => 'variable',
                'price' => 32999,
                'stock' => 0,
                'is_featured' => true,
                'categories' => ['electronics', 'phones'],
                'tags' => ['new-arrival'],
                'attributes' => ['warranty' => '1-year'],
                'options' => [
                    [
                        'name' => 'Storage',
                        'slug' => 'storage',
                        'values' => [
                            ['label' => '8/128GB', 'slug' => '8-128gb'],
                            ['label' => '8/256GB', 'slug' => '8-256gb'],
                        ],
                    ],
                ],
                'variants' => [
                    'storage=8-128gb' => [
                        'sku' => 'STZ-A35-8128', 'price' => 32999, 'stock' => 18,
                    ],
                    'storage=8-256gb' => [
                        'sku' => 'STZ-A35-8256', 'price' => 36999, 'stock' => 12,
                    ],
                ],
                'image' => 'products/samsung-galaxy-a35-5g.jpg',
            ],
            [
                'slug' => 'sony-ult-wear-wh-ult900n',
                'name' => 'Sony ULT WEAR WH-ULT900N',
                'brand' => 'sony',
                'type' => 'variable',
                'price' => 19990,
                'stock' => 0,
                'is_featured' => true,
                'categories' => ['electronics', 'audio'],
                'tags' => ['new-arrival', 'premium'],
                'attributes' => ['warranty' => '1-year'],
                'options' => [
                    [
                        'name' => 'Color',
                        'slug' => 'color',
                        'values' => [
                            ['label' => 'Black', 'slug' => 'black'],
                            ['label' => 'Off White', 'slug' => 'off-white'],
                            ['label' => 'Forest Gray', 'slug' => 'forest-gray'],
                        ],
                    ],
                ],
                'variants' => [
                    'color=black' => [
                        'sku' => 'STZ-ULT900N-BLK', 'price' => 19990, 'stock' => 14,
                        'images' => ['variants/sony-ult-wear-black.jpg'],
                    ],
                    'color=off-white' => [
                        'sku' => 'STZ-ULT900N-WHT', 'price' => 19990, 'stock' => 10,
                        'images' => ['variants/sony-ult-wear-off-white.jpg'],
                    ],
                    'color=forest-gray' => [
                        'sku' => 'STZ-ULT900N-GRY', 'price' => 19990, 'stock' => 8,
                        'images' => ['variants/sony-ult-wear-forest-gray.jpg'],
                    ],
                ],
                'image' => 'variants/sony-ult-wear-black.jpg',
            ],
            [
                'slug' => 'aarong-black-embroidered-cotton-panjabi',
                'name' => 'Aarong Black Embroidered Cotton Panjabi',
                'brand' => 'aarong',
                'type' => 'variable',
                'price' => 2490,
                'stock' => 0,
                'is_featured' => true,
                'categories' => ['fashion', 'mens-fashion'],
                'tags' => ['premium'],
                'attributes' => ['material' => 'cotton', 'color' => 'black'],
                'options' => [
                    [
                        'name' => 'Size',
                        'slug' => 'size',
                        'values' => [
                            ['label' => '40', 'slug' => '40'],
                            ['label' => '42', 'slug' => '42'],
                            ['label' => '44', 'slug' => '44'],
                            ['label' => '46', 'slug' => '46'],
                        ],
                    ],
                ],
                'variants' => [
                    'size=40' => ['sku' => 'STZ-AAR-PAN-40', 'price' => 2490, 'stock' => 9],
                    'size=42' => ['sku' => 'STZ-AAR-PAN-42', 'price' => 2490, 'stock' => 15],
                    'size=44' => ['sku' => 'STZ-AAR-PAN-44', 'price' => 2490, 'stock' => 12],
                    'size=46' => ['sku' => 'STZ-AAR-PAN-46', 'price' => 2490, 'stock' => 7],
                ],
                'image' => 'products/aarong-black-embroidered-cotton-panjabi.jpg',
            ],
            [
                'slug' => 'apex-formal-shoe-91313a15',
                'name' => 'Apex Men\'s Formal Shoe 91313A15',
                'brand' => 'apex',
                'type' => 'variable',
                'price' => 2286,
                'old_price' => 2690,
                'stock' => 0,
                'is_featured' => false,
                'categories' => ['fashion', 'mens-fashion'],
                'tags' => ['on-sale'],
                'attributes' => ['color' => 'black'],
                'options' => [
                    [
                        'name' => 'Size',
                        'slug' => 'size',
                        'values' => [
                            ['label' => '39', 'slug' => '39'],
                            ['label' => '40', 'slug' => '40'],
                            ['label' => '41', 'slug' => '41'],
                            ['label' => '42', 'slug' => '42'],
                            ['label' => '43', 'slug' => '43'],
                        ],
                    ],
                ],
                'variants' => [
                    'size=39' => ['sku' => 'STZ-APX-91313A15-39', 'price' => 2690, 'sale_price' => 2286, 'stock' => 7],
                    'size=40' => ['sku' => 'STZ-APX-91313A15-40', 'price' => 2690, 'sale_price' => 2286, 'stock' => 11],
                    'size=41' => ['sku' => 'STZ-APX-91313A15-41', 'price' => 2690, 'sale_price' => 2286, 'stock' => 16],
                    'size=42' => ['sku' => 'STZ-APX-91313A15-42', 'price' => 2690, 'sale_price' => 2286, 'stock' => 12],
                    'size=43' => ['sku' => 'STZ-APX-91313A15-43', 'price' => 2690, 'sale_price' => 2286, 'stock' => 8],
                ],
                'image' => 'products/apex-formal-shoe-91313a15.jpg',
            ],
            [
                'slug' => 'ikea-kallax-77x77-shelf-unit',
                'name' => 'IKEA KALLAX Shelf Unit 77x77cm',
                'brand' => 'ikea',
                'type' => 'variable',
                'price' => 8990,
                'stock' => 0,
                'is_featured' => true,
                'categories' => ['home-living'],
                'tags' => ['premium'],
                'attributes' => [],
                'options' => [
                    [
                        'name' => 'Color',
                        'slug' => 'color',
                        'values' => [
                            ['label' => 'White', 'slug' => 'white'],
                            ['label' => 'Black-Brown', 'slug' => 'black-brown'],
                        ],
                    ],
                ],
                'variants' => [
                    'color=white' => [
                        'sku' => 'STZ-KALLAX-77-WHT', 'price' => 8990, 'stock' => 6,
                        'images' => ['variants/ikea-kallax-77x77-white.jpg'],
                    ],
                    'color=black-brown' => [
                        'sku' => 'STZ-KALLAX-77-BBR', 'price' => 8990, 'stock' => 5,
                        'images' => ['variants/ikea-kallax-77x77-black-brown.jpg'],
                    ],
                ],
                'image' => 'variants/ikea-kallax-77x77-white.jpg',
            ],
        ];
    }
}
