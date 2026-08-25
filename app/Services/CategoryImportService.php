<?php

namespace App\Services;

use App\Models\Category;
use App\Models\CategoryImport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CategoryImportService
{
    public function __construct(private readonly CategoryService $categories) {}

    public function upload(UploadedFile $file, string $mode = 'create'): CategoryImport
    {
        $import = CategoryImport::create([
            'filename' => $file->getClientOriginalName(),
            'disk' => 'local',
            'path' => $file->store('category-imports', 'local'),
            'mode' => $mode,
            'status' => 'uploaded',
            'uploaded_by' => auth()->id(),
        ]);

        $import->forceFill(['uploaded_by' => auth()->id()])->save();

        return $import;
    }

    public function validate(CategoryImport $import): array
    {
        $rawRows = $this->readRows($import);
        $existing = Category::with('parent')->get()->keyBy('slug');
        $parentMap = $existing->mapWithKeys(fn (Category $category) => [$category->slug => $category->parent?->slug])->all();
        $rows = [];
        $errors = [];
        $seen = [];
        $csvSlugs = collect($rawRows)->map(fn (array $row) => Str::slug(trim((string) ($row['slug'] ?? $row['name'] ?? ''))))->filter()->flip()->all();

        foreach ($rawRows as $index => $raw) {
            $line = $index + 2;
            $row = $this->normalize($raw);
            $rowErrors = [];

            if ($row['name'] === '') {
                $rowErrors[] = 'Name is required.';
            }
            if ($row['slug'] === '') {
                $rowErrors[] = 'Slug cannot be empty.';
            }
            if ($row['slug'] !== '' && isset($seen[$row['slug']])) {
                $rowErrors[] = 'Duplicate slug in CSV (also used on row '.$seen[$row['slug']].').';
            }
            if ($row['slug'] !== '') {
                $seen[$row['slug']] = $line;
            }

            if ($import->mode === 'create' && $row['slug'] !== '' && $existing->has($row['slug'])) {
                $rowErrors[] = 'Slug already exists.';
            }
            if ($import->mode === 'update_by_slug' && ($raw['slug'] ?? '') === '') {
                $rowErrors[] = 'Slug is required for update-by-slug mode.';
            } elseif ($import->mode === 'update_by_slug' && $row['slug'] !== '' && ! $existing->has($row['slug'])) {
                $rowErrors[] = 'No existing category matches this slug.';
            }
            if ($row['parent_slug'] === $row['slug'] && $row['slug'] !== '') {
                $rowErrors[] = 'A category cannot be its own parent.';
            }
            if ($row['parent_slug'] !== '' && ! $existing->has($row['parent_slug']) && ! array_key_exists($row['parent_slug'], $csvSlugs)) {
                $rowErrors[] = 'Parent slug does not exist in the CSV or catalog.';
            }
            if ($row['is_active'] === null) {
                $rowErrors[] = 'is_active must be 1/0, true/false, active/inactive, or blank.';
            }
            $sortOrder = trim((string) ($raw['sort_order'] ?? ''));
            if (($sortOrder !== '' && ! preg_match('/^\d+$/', $sortOrder)) || (int) ($raw['sort_order'] ?? 0) < 0) {
                $rowErrors[] = 'sort_order must be a non-negative integer.';
            }

            $rows[$line] = $row;
            if ($rowErrors) {
                $errors[$line] = $rowErrors;
            }
        }

        foreach ($rows as $line => $row) {
            if ($row['parent_slug'] !== '' && $row['parent_slug'] !== $row['slug']) {
                $parentMap[$row['slug']] = $row['parent_slug'];
            } elseif ($import->mode === 'create') {
                $parentMap[$row['slug']] = null;
            }

            $depth = $this->depthFor($row['slug'], $parentMap);
            if ($depth === null) {
                $errors[$line][] = 'Category hierarchy contains a circular or unresolved parent reference.';
            } elseif ($depth > CategoryService::MAX_DEPTH) {
                $errors[$line][] = 'Category hierarchy cannot exceed '.CategoryService::MAX_DEPTH.' levels.';
            }
        }

        $summary = [
            'rows' => count($rawRows),
            'valid' => count($rawRows) - count($errors),
            'errors' => count($errors),
            'error_rows' => $errors,
        ];

        $import->update([
            'total_rows' => $summary['rows'],
            'valid_rows' => max(0, $summary['valid']),
            'error_rows' => $summary['errors'],
            'validation_summary' => $summary,
            'status' => 'validated',
        ]);

        return $summary;
    }

    public function import(CategoryImport $import): array
    {
        $summary = $this->validate($import);
        if ($summary['errors'] > 0) {
            throw new InvalidArgumentException('Fix the validation errors before importing categories.');
        }

        $normalizedRows = collect($this->readRows($import))->map(fn (array $row) => $this->normalize($row))->values();
        $parentMap = Category::with('parent')->get()->mapWithKeys(fn (Category $category) => [$category->slug => $category->parent?->slug])->all();
        foreach ($normalizedRows as $row) {
            $parentMap[$row['slug']] = $row['parent_slug'] ?: ($parentMap[$row['slug']] ?? null);
        }
        $depths = [];
        foreach ($normalizedRows as $row) {
            $depths[$row['slug']] = $this->depthFor($row['slug'], $parentMap) ?? 1;
        }
        $rows = $normalizedRows->sortBy(fn (array $row) => $depths[$row['slug']] ?? 1)->values();
        $created = 0;
        $updated = 0;

        DB::transaction(function () use ($rows, $import, &$created, &$updated): void {
            foreach ($rows as $row) {
                $category = $import->mode === 'update_by_slug'
                    ? Category::where('slug', $row['slug'])->firstOrFail()
                    : null;
                $parentId = $row['parent_slug'] !== '' ? Category::where('slug', $row['parent_slug'])->value('id') : null;

                $this->categories->save([
                    'name' => $row['name'],
                    'slug' => $row['slug'],
                    'parent_id' => $parentId,
                    'description' => $row['description'],
                    'is_active' => $row['is_active'],
                    'sort_order' => $row['sort_order'],
                ], $category);

                $category ? $updated++ : $created++;
            }
        });

        $import->update(['created_count' => $created, 'updated_count' => $updated, 'status' => 'success', 'completed_at' => now()]);
        $import->forceFill(['imported_by' => auth()->id()])->save();

        return compact('created', 'updated');
    }

    private function readRows(CategoryImport $import): array
    {
        $handle = fopen(Storage::disk($import->disk)->path($import->path), 'rb');
        $headers = array_map(fn ($header) => Str::snake(trim((string) $header)), fgetcsv($handle) ?: []);
        if (! in_array('name', $headers, true)) {
            fclose($handle);
            throw new InvalidArgumentException('The CSV must contain a name column.');
        }

        $rows = [];
        while (($values = fgetcsv($handle)) !== false) {
            if (count($values) === 1 && trim((string) $values[0]) === '') {
                continue;
            }
            if (count($values) !== count($headers)) {
                $values = array_pad(array_slice($values, 0, count($headers)), count($headers), '');
            }
            $rows[] = array_combine($headers, $values);
        }
        fclose($handle);

        return $rows;
    }

    private function normalize(array $row): array
    {
        $active = trim((string) ($row['is_active'] ?? ''));
        $isActive = $active === '' ? true : match (strtolower($active)) {
            '1', 'true', 'yes', 'active', 'on' => true,
            '0', 'false', 'no', 'inactive', 'off' => false,
            default => null,
        };

        return [
            'name' => trim((string) ($row['name'] ?? '')),
            'slug' => Str::slug(trim((string) ($row['slug'] ?? $row['name'] ?? ''))),
            'parent_slug' => Str::slug(trim((string) ($row['parent_slug'] ?? ''))),
            'description' => trim((string) ($row['description'] ?? '')) ?: null,
            'is_active' => $isActive,
            'sort_order' => (int) ($row['sort_order'] ?? 0),
        ];
    }

    private function depthFor(string $slug, array $parentMap, array $seen = []): ?int
    {
        if ($slug === '') {
            return null;
        }
        if (in_array($slug, $seen, true)) {
            return null;
        }
        if (! array_key_exists($slug, $parentMap) || ! $parentMap[$slug]) {
            return 1;
        }

        $seen[] = $slug;
        $parentDepth = $this->depthFor($parentMap[$slug], $parentMap, $seen);

        return $parentDepth === null ? null : $parentDepth + 1;
    }
}
