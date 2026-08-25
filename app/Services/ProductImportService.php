<?php

namespace App\Services;

use App\Models\ProductImport;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Storage;

class ProductImportService
{
    public function __construct(private readonly ProductService $products) {}

    public function upload($file, string $mode = 'create'): ProductImport
    {
        $import = ProductImport::create([
            'filename' => $file->getClientOriginalName(),
            'disk' => 'local',
            'path' => $file->store('imports', 'local'),
            'mode' => $mode,
            'status' => 'uploaded',
        ]);
        $import->forceFill(['uploaded_by' => auth()->id()])->save();

        return $import;
    }

    public function validate(ProductImport $import, array $mapping = []): array
    {
        $rows = $this->rows($import);
        $errors = [];
        $valid = [];

        foreach ($rows as $index => $row) {
            $name = trim((string) ($row[$mapping['name'] ?? 'Product Name'] ?? ''));
            $sku = trim((string) ($row[$mapping['sku'] ?? 'SKU'] ?? ''));
            $price = $row[$mapping['regular_price'] ?? 'Regular Price'] ?? null;
            $rowErrors = [];

            if ($name === '') {
                $rowErrors[] = 'Product name is required';
            }
            if ($import->mode === 'update_by_sku' && $sku === '') {
                $rowErrors[] = 'SKU is required for updates';
            }
            if ($price !== null && ! is_numeric($price)) {
                $rowErrors[] = 'Regular price must be numeric';
            }

            if ($rowErrors) {
                $errors[$index + 2] = $rowErrors;
            } else {
                $valid[] = $row;
            }
        }

        $summary = ['valid' => count($valid), 'errors' => count($errors), 'rows' => count($rows), 'error_rows' => $errors];
        $import->update([
            'mapping' => $mapping,
            'validation_summary' => $summary,
            'total_rows' => count($rows),
            'valid_rows' => count($valid),
            'error_rows' => count($errors),
            'status' => 'validated',
        ]);

        return $summary;
    }

    public function import(ProductImport $import, array $mapping = []): array
    {
        $rows = $this->rows($import);
        $created = 0;
        $updated = 0;

        foreach ($rows as $row) {
            $name = trim((string) ($row[$mapping['name'] ?? 'Product Name'] ?? ''));
            $sku = trim((string) ($row[$mapping['sku'] ?? 'SKU'] ?? ''));
            $price = (int) round(((float) ($row[$mapping['regular_price'] ?? 'Regular Price'] ?? 0)) * 100);
            $variant = $sku ? ProductVariant::where('sku', $sku)->first() : null;
            $product = $variant?->product;

            if (! $product && $import->mode === 'update_by_sku') {
                continue;
            }

            if (! $product) {
                $product = $this->products->save(['name' => $name, 'product_type' => 'simple', 'status' => 'draft', 'regular_price_minor' => $price]);
                $product->defaultVariant()->update(['sku' => $sku ?: 'STZ-'.$product->id, 'regular_price_minor' => $price]);
                $created++;
            } else {
                $product->update(['name' => $name]);
                $variant?->update(['regular_price_minor' => $price]);
                $updated++;
            }
        }

        $import->update(['created_count' => $created, 'updated_count' => $updated, 'status' => 'success', 'completed_at' => now()]);

        return compact('created', 'updated');
    }

    private function rows(ProductImport $import): array
    {
        $handle = fopen(Storage::disk($import->disk)->path($import->path), 'rb');
        $headers = fgetcsv($handle) ?: [];
        $rows = [];

        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = array_combine($headers, $row);
        }
        fclose($handle);

        return $rows;
    }
}
