<?php

namespace Database\Seeders\Concerns;

use App\Enums\ImagePreset;
use App\Traits\ImageHandler;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

trait SeedsDemoMedia
{
    use ImageHandler;

    /**
     * Copy an image from database/seeders/images to the public disk and
     * create/reuse the corresponding media_assets row.
     *
     * Returns ['id' => int, 'path' => string] or null when the source image
     * is missing.
     */
    protected function seedLocalImage(
        string $relativeSourcePath,
        ImagePreset $preset,
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

        $legacyPath = ltrim(str_replace('\\', '/', $targetPath ?? 'seeded/catalog/'.$relativeSourcePath), '/');
        $targetPath = $this->webpTargetPath($legacyPath);

        try {
            $processed = $this->processAndStoreImage($sourcePath, $targetPath, $preset, basename($relativeSourcePath));
        } catch (Throwable $exception) {
            $this->command?->warn("Seed image processing failed: {$relativeSourcePath} ({$exception->getMessage()})");

            return null;
        }

        $existing = DB::table('media_assets')
            ->where('disk', 'public')
            ->whereIn('path', array_values(array_unique([$targetPath, $legacyPath])))
            ->first();

        $payload = [
            'folder_id' => null,
            'filename' => $processed['filename'],
            'original_filename' => $processed['original_filename'],
            'extension' => $processed['extension'],
            'mime_type' => $processed['mime_type'],
            'size' => $processed['size'],
            'width' => $processed['width'],
            'height' => $processed['height'],
            'checksum' => $processed['checksum'],
            'updated_at' => now(),
        ];

        if ($existing) {
            DB::table('media_assets')
                ->where('id', $existing->id)
                ->update([...$payload, 'path' => $targetPath]);

            $id = (int) $existing->id;
        } else {
            $id = (int) DB::table('media_assets')->insertGetId([
                ...$payload,
                'disk' => 'public',
                'path' => $targetPath,
                'created_at' => now(),
            ]);
        }

        if ($legacyPath !== $targetPath) {
            Storage::disk('public')->delete($legacyPath);
        }

        return [
            'id' => $id,
            'path' => $targetPath,
        ];
    }

    private function webpTargetPath(string $path): string
    {
        $directory = dirname($path);
        $filename = pathinfo($path, PATHINFO_FILENAME).'.webp';

        return ($directory === '.' ? '' : $directory.'/').$filename;
    }
}
