<?php

namespace App\Traits;

use App\Enums\ImagePreset;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

trait ImageHandler
{
    /**
     * @return array{path: string, filename: string, extension: string, mime_type: string, size: int, width: int, height: int, checksum: string, original_filename: string}
     */
    protected function processAndStoreImage(
        UploadedFile|string $source,
        string $targetPath,
        ImagePreset $preset,
        ?string $originalFilename = null,
    ): array {
        if (! extension_loaded('gd') || ! function_exists('imagewebp')) {
            throw new RuntimeException('The GD extension with WebP support is required to process images.');
        }

        $sourcePath = $source instanceof UploadedFile ? $source->getRealPath() : $source;
        $originalFilename ??= $source instanceof UploadedFile ? $source->getClientOriginalName() : basename((string) $sourcePath);

        if (! is_string($sourcePath) || ! is_file($sourcePath)) {
            throw new RuntimeException('The image source could not be found.');
        }

        $sourceBytes = file_get_contents($sourcePath);
        $sourceInfo = @getimagesize($sourcePath);

        if ($sourceBytes === false || $sourceInfo === false || ! isset($sourceInfo[0], $sourceInfo[1])) {
            throw new RuntimeException('The image source is invalid or unreadable.');
        }

        $sourceImage = @imagecreatefromstring($sourceBytes);

        if ($sourceImage === false) {
            throw new RuntimeException('The image source could not be decoded.');
        }

        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);
        [$cropX, $cropY, $cropWidth, $cropHeight] = $this->imageCrop($sourceWidth, $sourceHeight, $preset);
        [$targetWidth, $targetHeight] = $this->imageDimensions($cropWidth, $cropHeight, $preset);
        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);

        if ($canvas === false) {
            imagedestroy($sourceImage);
            throw new RuntimeException('The image canvas could not be created.');
        }

        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
        imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $transparent);

        if (! imagecopyresampled(
            $canvas,
            $sourceImage,
            0,
            0,
            $cropX,
            $cropY,
            $targetWidth,
            $targetHeight,
            $cropWidth,
            $cropHeight,
        )) {
            imagedestroy($sourceImage);
            imagedestroy($canvas);
            throw new RuntimeException('The image could not be resized.');
        }

        imagedestroy($sourceImage);
        ob_start();
        $encoded = imagewebp($canvas, null, $preset->quality()) ? ob_get_clean() : false;
        imagedestroy($canvas);

        if (! is_string($encoded) || $encoded === '') {
            if (ob_get_level() > 0) {
                ob_end_clean();
            }

            throw new RuntimeException('The image could not be encoded as WebP.');
        }

        $targetPath = ltrim(str_replace('\\', '/', $targetPath), '/');
        $temporaryPath = $targetPath.'.'.Str::uuid()->toString().'.tmp';
        $disk = Storage::disk('public');

        try {
            if (! $disk->put($temporaryPath, $encoded)) {
                throw new RuntimeException('The processed image could not be stored.');
            }

            if ($disk->exists($targetPath)) {
                $disk->delete($targetPath);
            }

            if (! $disk->move($temporaryPath, $targetPath)) {
                throw new RuntimeException('The processed image could not be finalized.');
            }
        } catch (Throwable $exception) {
            $disk->delete($temporaryPath);
            throw $exception;
        }

        return [
            'path' => $targetPath,
            'filename' => basename($targetPath),
            'extension' => 'webp',
            'mime_type' => 'image/webp',
            'size' => strlen($encoded),
            'width' => $targetWidth,
            'height' => $targetHeight,
            'checksum' => hash('sha256', $encoded),
            'original_filename' => basename($originalFilename),
        ];
    }

    /**
     * @return array{0: int, 1: int, 2: int, 3: int}
     */
    private function imageCrop(int $width, int $height, ImagePreset $preset): array
    {
        if (! $preset->cropsToSquare()) {
            return [0, 0, $width, $height];
        }

        $size = min($width, $height);

        return [intdiv($width - $size, 2), intdiv($height - $size, 2), $size, $size];
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function imageDimensions(int $width, int $height, ImagePreset $preset): array
    {
        $maxWidth = $preset->maxWidth();
        $maxHeight = $preset->maxHeight();
        $scale = min(1, $maxWidth / $width, $maxHeight / $height);

        return [max(1, (int) round($width * $scale)), max(1, (int) round($height * $scale))];
    }
}
