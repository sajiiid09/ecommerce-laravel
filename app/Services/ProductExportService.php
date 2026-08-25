<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductExport;
use Illuminate\Support\Facades\Storage;

class ProductExportService
{
    public function export(array $fields = ['id', 'name', 'slug', 'status', 'sku', 'regular_price_minor', 'sale_price_minor', 'stock'], ?array $ids = null, bool $includeVariants = false): ProductExport
    {
        $export = ProductExport::create(['type' => 'products', 'format' => 'csv', 'scope' => $ids ? 'selected' : 'all', 'fields' => $fields, 'include_variants' => $includeVariants, 'disk' => 'local', 'status' => 'processing']);
        $export->forceFill(['requested_by' => auth()->id()])->save();
        $filename = 'exports/products-'.now()->format('Ymd-His').'.csv';
        Storage::disk('local')->makeDirectory('exports');
        $handle = fopen(Storage::disk('local')->path($filename), 'wb');
        fputcsv($handle, $fields);
        $query = Product::with(['brand', 'primaryCategory', 'variants.inventory']);
        if ($ids) {
            $query->whereKey($ids);
        }
        $count = 0;
        foreach ($query->get() as $product) {
            $variant = $product->variants->sortByDesc('is_default')->first();
            fputcsv($handle, array_map(fn ($field) => match ($field) {
                'sku' => $variant?->sku,
                'regular_price_minor' => $variant?->regular_price_minor,
                'sale_price_minor' => $variant?->sale_price_minor,
                'stock' => $variant?->availableQuantity() ?? 0,
                'brand' => $product->brand?->name,
                'category' => $product->primaryCategory?->name,
                default => is_object($product->{$field} ?? null) && property_exists($product->{$field}, 'value') ? $product->{$field}->value : ($product->{$field} ?? null),
            }, $fields));
            $count++;
        }
        fclose($handle);
        $export->update(['path' => $filename, 'filename' => basename($filename), 'record_count' => $count, 'status' => 'ready', 'completed_at' => now()]);

        return $export;
    }
}
