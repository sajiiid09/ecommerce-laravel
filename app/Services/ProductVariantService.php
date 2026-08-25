<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class ProductVariantService
{
    public function ensureDefault(Product $product): ProductVariant
    {
        return DB::transaction(function () use ($product) {
            $variant = $product->variants()->where('is_default', true)->first() ?: $product->variants()->first();
            if (! $variant) {
                $variant = $product->variants()->create(['sku' => 'STZ-'.$product->id, 'combination_key' => 'default', 'regular_price_minor' => 0, 'is_default' => true, 'is_active' => true]);
            } elseif (! $variant->is_default) {
                $variant->update(['is_default' => true]);
            } app(InventoryService::class)->initialize($variant);

            return $variant->fresh();
        });
    }

    public function generate(Product $product): array
    {
        $options = $product->options()->with('values')->get();
        if ($options->isEmpty() || $options->contains(fn ($o) => $o->values->isEmpty())) {
            return [];
        } $combinations = [[]];
        foreach ($options as $option) {
            $next = [];
            foreach ($combinations as $base) {
                foreach ($option->values as $value) {
                    $next[] = $base + [$option->slug ?: str($option->name)->slug()->toString() => $value];
                }
            } $combinations = $next;
        } $created = [];
        foreach ($combinations as $combination) {
            $ids = collect($combination)->pluck('id')->sort()->values()->all();
            $key = implode('-', $ids);
            $variant = $product->variants()->firstOrCreate(['combination_key' => $key], ['sku' => 'STZ-'.$product->id.'-'.strtoupper(substr(md5($key), 0, 6)), 'name' => collect($combination)->pluck('value')->implode(' / '), 'regular_price_minor' => 0, 'is_active' => true]);
            $variant->optionValues()->sync($ids);
            app(InventoryService::class)->initialize($variant);
            $created[] = $variant;
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

            $inventory = app(InventoryService::class)->initialize($variant);
            if (array_key_exists('quantity_on_hand', $data)) {
                $delta = (int) $data['quantity_on_hand'] - (int) $inventory->quantity_on_hand;
                if ($delta !== 0) {
                    $inventory = app(InventoryService::class)->adjust($variant, $delta, 'correction', 'Variant editor stock update');
                }
            }
            $inventory->fill([
                'low_stock_threshold' => (int) ($data['low_stock_threshold'] ?? $inventory->low_stock_threshold),
                'track_quantity' => (bool) ($data['track_quantity'] ?? $inventory->track_quantity),
                'allow_backorders' => (bool) ($data['allow_backorders'] ?? $inventory->allow_backorders),
            ])->save();

            return $variant->fresh(['inventory', 'optionValues']);
        });
    }
}
