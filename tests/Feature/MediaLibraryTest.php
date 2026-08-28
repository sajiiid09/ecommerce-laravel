<?php

use App\Enums\ImagePreset;
use App\Models\MediaAsset;
use App\Models\Page;
use App\Services\MediaService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('soft deletes unused media and protects used media', function () {
    Storage::fake('public');
    $media = app(MediaService::class);
    $asset = $media->upload(UploadedFile::fake()->image('hero.jpg'), 'general', ImagePreset::General);
    $media->delete($asset);

    $this->assertSoftDeleted('media_assets', ['id' => $asset->id]);

    $used = $media->upload(UploadedFile::fake()->image('page.jpg'), 'general', ImagePreset::General);
    $page = Page::create(['title' => 'Page', 'slug' => 'page', 'status' => 'draft', 'visibility' => 'public']);
    $media->attach($used, $page, 'page.featured_media');

    expect(fn () => $media->delete($used))->toThrow(RuntimeException::class);
});

it('stores uploaded images as resized webp files with original metadata', function () {
    Storage::fake('public');

    $asset = app(MediaService::class)->upload(
        UploadedFile::fake()->image('wide-product.png', 2400, 1200),
        'products',
        ImagePreset::Product,
    );

    expect($asset->original_filename)->toBe('wide-product.png')
        ->and($asset->extension)->toBe('webp')
        ->and($asset->mime_type)->toBe('image/webp')
        ->and($asset->width)->toBe(1200)
        ->and($asset->height)->toBe(600)
        ->and($asset->size)->toBeGreaterThan(0)
        ->and($asset->checksum)->toHaveLength(64)
        ->and(Storage::disk('public')->exists($asset->path))->toBeTrue();

    $imageInfo = getimagesizefromstring(Storage::disk('public')->get($asset->path));

    expect($imageInfo['mime'])->toBe('image/webp');
});

it('uses the server-owned maximum dimension for non-product image presets', function () {
    Storage::fake('public');

    foreach ([ImagePreset::Logo, ImagePreset::Banner, ImagePreset::Category, ImagePreset::General] as $preset) {
        $asset = app(MediaService::class)->upload(
            UploadedFile::fake()->image($preset->value.'.jpg', 2400, 1200),
            'general',
            $preset,
        );

        expect($asset->width)->toBe(1600)
            ->and($asset->height)->toBe(800)
            ->and($asset->extension)->toBe('webp');
    }
});

it('does not create an asset when the image cannot be decoded', function () {
    Storage::fake('public');

    expect(fn () => app(MediaService::class)->upload(
        UploadedFile::fake()->createWithContent('broken.png', 'not an image'),
        'products',
        ImagePreset::Product,
    ))->toThrow(RuntimeException::class);

    expect(MediaAsset::query()->count())->toBe(0)
        ->and(Storage::disk('public')->allFiles())->toBe([]);
});
