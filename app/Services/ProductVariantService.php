<?php

namespace App\Services;

use App\Models\MediaAsset;
use App\Models\MediaUsage;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ProductVariantService
{
    public function __construct(
        private readonly InventoryService $inventory,
        private readonly MediaService $media,
    ) {}

    public function ensureDefault(Product $product): ProductVariant
    {
        return DB::transaction(function () use ($product): ProductVariant {
            $variant = $product->variants()->where('is_default', true)->first()
                ?: $product->variants()->first();

            if (! $variant) {
                $variant = $product->variants()->create([
                    'sku' => 'STZ-'.$product->id,
                    'combination_key' => 'default',
                    'regular_price_minor' => 0,
                    'is_default' => true,
                    'is_active' => true,
                ]);
            } elseif (! $variant->is_default) {
                $variant->update(['is_default' => true]);
            }

            $product->variants()->whereKeyNot($variant->id)->update(['is_default' => false]);
            $this->inventory->initialize($variant);

            return $variant->fresh();
        });
    }

    public function generate(Product $product): array
    {
        $options = $product->options()->with('values')->orderBy('sort_order')->get();

        if ($options->isEmpty() || $options->contains(fn ($option): bool => $option->values->isEmpty())) {
            return [];
        }

        $combinations = [[]];

        foreach ($options as $option) {
            $next = [];

            foreach ($combinations as $base) {
                foreach ($option->values as $value) {
                    $next[] = $base + [$option->slug ?: str($option->name)->slug()->toString() => $value];
                }
            }

            $combinations = $next;
        }

        $created = [];

        foreach ($combinations as $combination) {
            $ids = collect($combination)->pluck('id')->sort()->values()->all();
            $key = implode('-', $ids);
            $variant = $product->variants()->firstOrCreate(
                ['combination_key' => $key],
                [
                    'sku' => 'STZ-'.$product->id.'-'.strtoupper(substr(md5($key), 0, 6)),
                    'name' => collect($combination)->pluck('value')->implode(' / '),
                    'regular_price_minor' => 0,
                    'is_active' => true,
                    'is_default' => false,
                ],
            );
            $variant->optionValues()->sync($ids);
            $this->inventory->initialize($variant);
            $created[] = $variant;
        }

        if (! $product->variants()->where('is_default', true)->exists()) {
            $product->variants()->whereKey($created[0]->id)->update(['is_default' => true]);
        }

        return $created;
    }

    public function save(ProductVariant $variant, array $data): ProductVariant
    {
        return DB::transaction(function () use ($variant, $data): ProductVariant {
            $variant->fill([
                'sku' => $data['sku'],
                'barcode' => $data['barcode'] ?? null,
                'regular_price_minor' => $data['regular_price_minor'] ?? 0,
                'sale_price_minor' => $data['sale_price_minor'] ?? null,
                'compare_at_price_minor' => $data['compare_at_price_minor'] ?? null,
                'cost_price_minor' => $data['cost_price_minor'] ?? null,
                'weight_grams' => $data['weight_grams'] ?? null,
                'is_active' => (bool) ($data['is_active'] ?? true),
                'is_default' => (bool) ($data['is_default'] ?? false),
            ])->save();

            if ($variant->is_default) {
                $variant->product->variants()->whereKeyNot($variant->id)->update(['is_default' => false]);
            }

            $inventory = $this->inventory->initialize($variant);
            if (array_key_exists('quantity_on_hand', $data)) {
                $delta = (int) $data['quantity_on_hand'] - (int) $inventory->quantity_on_hand;
                if ($delta !== 0) {
                    $inventory = $this->inventory->adjust($variant, $delta, 'correction', 'Variant editor stock update');
                }
            }
            $inventory->fill([
                'low_stock_threshold' => (int) ($data['low_stock_threshold'] ?? $inventory->low_stock_threshold),
                'track_quantity' => (bool) ($data['track_quantity'] ?? $inventory->track_quantity),
                'allow_backorders' => (bool) ($data['allow_backorders'] ?? $inventory->allow_backorders),
            ])->save();

            if (array_key_exists('media_ids', $data)) {
                $this->syncMedia($variant, (array) $data['media_ids']);
            }

            return $variant->fresh(['inventory', 'optionValues.option', 'media.asset']);
        });
    }

    private function syncMedia(ProductVariant $variant, array $mediaIds): void
    {
        $mediaIds = array_values(array_unique(array_filter(array_map('intval', $mediaIds))));
        $assets = MediaAsset::query()->whereKey($mediaIds)->get()->keyBy('id');

        if ($assets->count() !== count($mediaIds)) {
            throw new \InvalidArgumentException('One or more selected variant images are no longer available.');
        }

        foreach ($assets as $asset) {
            Gate::authorize('view', $asset);
        }

        MediaUsage::query()
            ->where('usable_type', ProductVariant::class)
            ->where('usable_id', $variant->id)
            ->where('role', 'variant.image')
            ->delete();
        $variant->media()->delete();

        foreach ($mediaIds as $sortOrder => $mediaId) {
            $asset = $assets->get($mediaId);
            $variant->media()->create([
                'product_id' => $variant->product_id,
                'media_asset_id' => $asset->id,
                'role' => $sortOrder === 0 ? 'main' : 'gallery',
                'sort_order' => $sortOrder,
                'alt_text' => $asset->alt_text,
            ]);
            $this->media->attach($asset, $variant, 'variant.image');
        }
    }
}
