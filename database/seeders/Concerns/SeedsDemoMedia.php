<?php

namespace Database\Seeders\Concerns;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

trait SeedsDemoMedia
{
    /**
     * Copy an image from database/seeders/images to the public disk and
     * create/reuse the corresponding media_assets row.
     *
     * Returns ['id' => int, 'path' => string] or null when the source image
     * is missing.
     */
    protected function seedLocalImage(
        string $relativeSourcePath,
        ?string $targetPath = null
    ): ?array {
        $relativeSourcePath = ltrim(str_replace('\\', '/', $relativeSourcePath), '/');
        $sourcePath = database_path('seeders/images/'.$relativeSourcePath);

        if (! is_file($sourcePath)) {
            $this->command?->warn("Seed image missing: {$relativeSourcePath}");

            return null;
        }

        if (! Schema::hasTable('media_assets')) {
            $this->command?->warn('media_assets table does not exist; skipping seeded media.');

            return null;
        }

        $targetPath ??= 'seeded/catalog/'.$relativeSourcePath;
        $targetPath = ltrim(str_replace('\\', '/', $targetPath), '/');

        Storage::disk('public')->put($targetPath, file_get_contents($sourcePath));

        $imageInfo = @getimagesize($sourcePath);
        $mimeType = $imageInfo['mime'] ?? $this->detectMimeType($sourcePath);
        $width = $imageInfo[0] ?? null;
        $height = $imageInfo[1] ?? null;

        $existing = DB::table('media_assets')
            ->where('disk', 'public')
            ->where('path', $targetPath)
            ->first();

        $payload = [
            'folder_id' => null,
            'filename' => basename($targetPath),
            'mime_type' => $mimeType,
            'size' => filesize($sourcePath) ?: null,
            'width' => $width,
            'height' => $height,
            'updated_at' => now(),
        ];

        if ($existing) {
            DB::table('media_assets')
                ->where('id', $existing->id)
                ->update($payload);

            $id = (int) $existing->id;
        } else {
            $id = (int) DB::table('media_assets')->insertGetId([
                ...$payload,
                'disk' => 'public',
                'path' => $targetPath,
                'created_at' => now(),
            ]);
        }

        return [
            'id' => $id,
            'path' => $targetPath,
        ];
    }

    private function detectMimeType(string $path): ?string
    {
        if (function_exists('mime_content_type')) {
            return mime_content_type($path) ?: null;
        }

        return null;
    }
}
