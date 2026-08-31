<?php

namespace App\Services;

use App\Models\MediaAsset;
use App\Models\MediaUsage;
use App\Models\Product;
use App\Models\ProductOptionValue;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ProductVariantService
{
    public function __construct(
        private readonly InventoryService $inventory,
        private readonly MediaService $media,
        private readonly CatalogCache $catalogCache,
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
        $generated = DB::transaction(function () use ($product): array {
            $options = $product->options()->with('values')->orderBy('sort_order')->get();
            $existing = ProductVariant::withTrashed()
                ->whereBelongsTo($product)
                ->with('optionValues.option')
                ->get();

            foreach ($existing as $variant) {
                $key = $variant->optionValues->count() === $options->count()
                    ? $this->keyForVariant($variant)
                    : null;

                if ($key !== null && $key !== $variant->combination_key) {
                    $variant->forceFill(['combination_key' => $key])->save();
                }
            }

            $combinations = $this->combinations($options);
            $validKeys = collect($combinations)
                ->map(fn (array $combination): string => $this->combinationKey($combination))
                ->all();
            $generated = [];

            foreach ($combinations as $combination) {
                $ids = collect($combination)->pluck('id')->values()->all();
                $key = $this->combinationKey($combination);
                $variant = $existing->firstWhere('combination_key', $key);

                if (! $variant) {
                    $variant = $product->variants()->create([
                        'sku' => 'STZ-'.$product->id.'-'.strtoupper(substr(md5($key), 0, 6)),
                        'name' => collect($combination)->pluck('value')->implode(' / '),
                        'combination_key' => $key,
                        'regular_price_minor' => 0,
                        'is_active' => true,
                        'is_default' => false,
                    ]);
                    $existing->push($variant);
                } elseif ($variant->trashed()) {
                    $variant->restore();
                }

                $variant->forceFill([
                    'name' => collect($combination)->pluck('value')->implode(' / '),
                    'combination_key' => $key,
                ])->save();
                $variant->optionValues()->sync($ids);
                $this->inventory->initialize($variant);
                $generated[] = $variant->fresh(['inventory', 'optionValues.option', 'media.asset']);
            }

            foreach ($existing as $variant) {
                if (! in_array($variant->combination_key, $validKeys, true) && ! $variant->trashed()) {
                    $variant->delete();
                }
            }

            $active = collect($generated)->filter(fn (ProductVariant $variant): bool => $variant->is_active);
            $default = $active->firstWhere('is_default', true) ?? $active->first();
            $product->variants()->where('is_default', true)->update(['is_default' => false]);

            if ($default) {
                $default->forceFill(['is_default' => true])->save();
            }

            return collect($generated)
                ->map(fn (ProductVariant $variant): ProductVariant => $variant->fresh(['inventory', 'optionValues.option', 'media.asset']))
                ->all();
        });

        $this->catalogCache->forgetProduct($product->slug);

        return $generated;
    }

    public function save(ProductVariant $variant, array $data): ProductVariant
    {
        $regularPriceMinor = (int) ($data['regular_price_minor'] ?? 0);
        $salePriceMinor = $data['sale_price_minor'] ?? null;

        if ($regularPriceMinor < 0 || ($salePriceMinor !== null && ((int) $salePriceMinor < 0 || (int) $salePriceMinor > $regularPriceMinor))) {
            throw new InvalidArgumentException('Sale price must not exceed the regular price.');
        }

        $saved = DB::transaction(function () use ($variant, $data, $regularPriceMinor, $salePriceMinor): ProductVariant {
            $product = $variant->product()->firstOrFail();
            $variant->fill([
                'sku' => $data['sku'],
                'barcode' => $data['barcode'] ?? null,
                'regular_price_minor' => $regularPriceMinor,
                'sale_price_minor' => $salePriceMinor,
                'compare_at_price_minor' => $salePriceMinor !== null && $salePriceMinor < $regularPriceMinor
                    ? $regularPriceMinor
                    : null,
                'cost_price_minor' => $data['cost_price_minor'] ?? null,
                'weight_grams' => $data['weight_grams'] ?? null,
                'is_active' => (bool) ($data['is_active'] ?? true),
                'is_default' => (bool) ($data['is_default'] ?? false),
            ])->save();

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

            $this->normalizeDefault($product);

            return $variant->fresh(['inventory', 'optionValues.option', 'media.asset']);
        });

        $this->catalogCache->forgetProduct($saved->product()->value('slug'));

        return $saved;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\ProductOption>  $options
     * @return array<int, array<string, ProductOptionValue>>
     */
    private function combinations($options): array
    {
        if ($options->isEmpty() || $options->contains(fn ($option): bool => $option->values->isEmpty())) {
            return [];
        }

        $combinations = [[]];

        foreach ($options as $option) {
            $optionSlug = $option->slug ?: Str::slug($option->name);
            $next = [];

            foreach ($combinations as $base) {
                foreach ($option->values as $value) {
                    $next[] = $base + [$optionSlug => $value];
                }
            }

            $combinations = $next;
        }

        return $combinations;
    }

    /**
     * @param  array<string, ProductOptionValue>  $combination
     */
    private function combinationKey(array $combination): string
    {
        return collect($combination)
            ->mapWithKeys(fn (ProductOptionValue $value, string $option): array => [
                $option => $value->slug ?: Str::slug($value->value),
            ])
            ->sortKeys()
            ->map(fn (string $value, string $option): string => $option.'='.$value)
            ->implode('|');
    }

    private function keyForVariant(ProductVariant $variant): ?string
    {
        if ($variant->optionValues->isEmpty()) {
            return null;
        }

        $combination = $variant->optionValues->mapWithKeys(function (ProductOptionValue $value): array {
            $option = $value->option?->slug ?: Str::slug((string) $value->option?->name);

            return $option === '' ? [] : [$option => $value];
        })->all();

        return count($combination) === $variant->optionValues->count()
            ? $this->combinationKey($combination)
            : null;
    }

    private function normalizeDefault(Product $product): void
    {
        $activeVariants = $product->variants()->where('is_active', true)->get();
        $default = $activeVariants->firstWhere('is_default', true) ?? $activeVariants->first();
        $product->variants()->where('is_default', true)->update(['is_default' => false]);

        if ($default) {
            $default->forceFill(['is_default' => true])->save();
        }
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
