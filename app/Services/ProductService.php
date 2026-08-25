<?php

namespace App\Services;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Models\MediaAsset;
use App\Models\MediaUsage;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class ProductService
{
    public function __construct(
        private readonly ProductVariantService $variants,
        private readonly InventoryService $inventory,
        private readonly MediaService $media,
        private readonly RichTextContentService $richText,
    ) {}

    public function save(array $data, ?Product $product = null): Product
    {
        return DB::transaction(function () use ($data, $product): Product {
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
                    throw new \InvalidArgumentException('One or more selected product images are no longer available.');
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
    }
}
