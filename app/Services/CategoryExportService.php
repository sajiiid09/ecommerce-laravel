<?php

namespace App\Services;

use App\Models\Category;
use App\Models\CategoryExport;
use Illuminate\Support\Facades\Storage;

class CategoryExportService
{
    public const FIELDS = [
        'id', 'name', 'slug', 'parent_slug', 'parent_name', 'description',
        'is_active', 'sort_order', 'product_count', 'created_at', 'updated_at',
    ];

    public function export(?array $ids = null): CategoryExport
    {
        $export = CategoryExport::create([
            'fields' => self::FIELDS,
            'scope' => $ids ? 'selected' : 'all',
            'status' => 'processing',
            'requested_by' => auth()->id(),
        ]);
        $export->forceFill(['requested_by' => auth()->id()])->save();

        $filename = 'exports/categories-'.now()->format('Ymd-His').'.csv';
        Storage::disk('local')->makeDirectory('exports');
        $handle = fopen(Storage::disk('local')->path($filename), 'wb');
        fputcsv($handle, self::FIELDS);

        $query = Category::with('parent')->withCount('products')->orderBy('parent_id')->orderBy('sort_order')->orderBy('name');
        if ($ids) {
            $query->whereKey($ids);
        }

        $count = 0;
        foreach ($query->get() as $category) {
            fputcsv($handle, [
                $category->id,
                $category->name,
                $category->slug,
                $category->parent?->slug,
                $category->parent?->name,
                $category->description,
                $category->is_active ? 1 : 0,
                $category->sort_order,
                $category->products_count,
                $category->created_at?->toIso8601String(),
                $category->updated_at?->toIso8601String(),
            ]);
            $count++;
        }

        fclose($handle);
        $export->update([
            'path' => $filename,
            'filename' => basename($filename),
            'record_count' => $count,
            'status' => 'ready',
            'completed_at' => now(),
        ]);

        return $export->fresh();
    }
}
