<?php

namespace App\Services;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Models\Attribute;
use App\Models\MediaAsset;
use App\Models\MediaUsage;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ProductService
{
    public function __construct(
        private readonly ProductVariantService $variants,
        private readonly InventoryService $inventory,
        private readonly MediaService $media,
        private readonly RichTextContentService $richText,
        private readonly CatalogCache $catalogCache,
    ) {}

    public function save(array $data, ?Product $product = null): Product
    {
        $saved = DB::transaction(function () use ($data, $product): Product {
            $product ??= new Product;
            $content = $this->richText->prepare($data['description_json'] ?? null, (string) ($data['description_html'] ?? ''));

            $product->fill([
                'name' => $data['name'],
                'slug' => Str::slug($data['slug'] ?? $data['name']),
                'product_type' => $data['product_type'] ?? ProductType::Simple->value,
                'brand_id' => $data['brand_id'] ?? null,
                'primary_category_id' => $data['primary_category_id'] ?? null,
                'short_description' => $data['short_description'] ?? null,
                'description_json' => $content['content_json'],
                'description_html' => $content['content_html'],
                'status' => $data['status'] ?? ProductStatus::Draft->value,
                'visibility' => $data['visibility'] ?? 'visible',
                'is_featured' => (bool) ($data['is_featured'] ?? false),
                'taxable' => (bool) ($data['taxable'] ?? true),
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'canonical_url' => $data['canonical_url'] ?? null,
                'is_indexable' => (bool) ($data['is_indexable'] ?? true),
                'published_at' => $data['published_at'] ?? null,
            ]);
            $product->save();
            $this->richText->syncUsages($product, $content['media_ids'], 'product.content');

            if (array_key_exists('category_ids', $data)) {
                $categoryIds = array_values(array_unique(array_filter(array_map('intval', $data['category_ids'] ?? []))));
                if ($product->primary_category_id && ! in_array((int) $product->primary_category_id, $categoryIds, true)) {
                    $categoryIds[] = (int) $product->primary_category_id;
                }
                $product->categories()->sync($categoryIds);
            }

            if (array_key_exists('tag_ids', $data)) {
                $product->tags()->sync($data['tag_ids'] ?? []);
            }

            if (array_key_exists('attribute_values', $data)) {
                $this->syncAttributeValues($product, (array) $data['attribute_values']);
            }

            if ($product->product_type === ProductType::Simple) {
                $variant = $this->variants->ensureDefault($product);
                $variant->update([
                    'regular_price_minor' => $data['regular_price_minor'] ?? $variant->regular_price_minor,
                    'sale_price_minor' => $data['sale_price_minor'] ?? null,
                    'compare_at_price_minor' => $data['compare_at_price_minor'] ?? null,
                    'cost_price_minor' => $data['cost_price_minor'] ?? null,
                ]);

                $inventory = $this->inventory->initialize($variant);
                if (array_key_exists('inventory_quantity', $data)) {
                    $delta = (int) $data['inventory_quantity'] - (int) $inventory->quantity_on_hand;
                    if ($delta !== 0) {
                        $inventory = $this->inventory->adjust($variant, $delta, 'correction', 'Product editor stock update');
                    }
                }
                $inventory->fill([
                    'low_stock_threshold' => (int) ($data['low_stock_threshold'] ?? $inventory->low_stock_threshold),
                    'track_quantity' => (bool) ($data['track_quantity'] ?? $inventory->track_quantity),
                    'allow_backorders' => (bool) ($data['allow_backorders'] ?? $inventory->allow_backorders),
                ])->save();
            }

            if (array_key_exists('media_ids', $data)) {
                $mediaIds = array_values(array_unique(array_filter(array_map('intval', $data['media_ids'] ?? []))));
                $assets = MediaAsset::query()->whereKey($mediaIds)->get()->keyBy('id');

                if ($assets->count() !== count($mediaIds)) {
                    throw new InvalidArgumentException('One or more selected product images are no longer available.');
                }

                foreach ($assets as $asset) {
                    Gate::authorize('view', $asset);
                }

                MediaUsage::query()
                    ->where('usable_type', Product::class)
                    ->where('usable_id', $product->id)
                    ->where('role', 'product.image')
                    ->delete();
                $product->media()->delete();

                foreach ($mediaIds as $sortOrder => $mediaId) {
                    $asset = $assets->get($mediaId);
                    $product->media()->create([
                        'media_asset_id' => $asset->id,
                        'role' => $sortOrder === 0 ? 'main' : 'gallery',
                        'sort_order' => $sortOrder,
                        'alt_text' => $asset->alt_text,
                    ]);
                    $this->media->attach($asset, $product, 'product.image');
                }
            }

            return $product->fresh(['variants', 'brand', 'primaryCategory']);
        });

        $this->catalogCache->forgetAll();
        $this->catalogCache->forgetProduct($saved->slug);

        return $saved;
    }

    private function syncAttributeValues(Product $product, array $attributeValues): void
    {
        $attributes = Attribute::query()
            ->with('values')
            ->whereIn('id', array_keys($attributeValues))
            ->get()
            ->keyBy('id');

        $product->attributeValues()->delete();

        foreach ($attributes as $attribute) {
            if (! $attribute->is_active) {
                throw new InvalidArgumentException("The {$attribute->name} attribute is inactive.");
            }

            $rawValue = $attributeValues[$attribute->id] ?? null;
            $values = $attribute->type === 'multi_select'
                ? array_values(array_filter((array) $rawValue))
                : [$rawValue];

            if ($attribute->is_required && collect($values)->filter(fn ($value): bool => filled($value))->isEmpty()) {
                throw new InvalidArgumentException("The {$attribute->name} attribute is required.");
            }

            foreach ($values as $sortOrder => $value) {
                if (! filled($value)) {
                    continue;
                }

                $row = [
                    'attribute_id' => $attribute->id,
                    'sort_order' => $sortOrder,
                ];

                match ($attribute->type) {
                    'select', 'multi_select' => $this->fillSelectAttribute($row, $attribute, (int) $value),
                    'number' => $row['number_value'] = (float) $value,
                    'boolean' => $row['boolean_value'] = filter_var($value, FILTER_VALIDATE_BOOLEAN),
                    default => $row['text_value'] = (string) $value,
                };

                $product->attributeValues()->create($row);
            }
        }
    }

    private function fillSelectAttribute(array &$row, Attribute $attribute, int $valueId): void
    {
        if (! $attribute->values->contains('id', $valueId)) {
            throw new InvalidArgumentException("The selected value is invalid for {$attribute->name}.");
        }

        $row['attribute_value_id'] = $valueId;
    }
}
