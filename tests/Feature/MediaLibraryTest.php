<?php

use App\Models\Page;
use App\Services\MediaService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('soft deletes unused media and protects used media', function () {
    Storage::fake('public');
    $media = app(MediaService::class);
    $asset = $media->upload(UploadedFile::fake()->image('hero.jpg'));
    $media->delete($asset);

    $this->assertSoftDeleted('media_assets', ['id' => $asset->id]);

    $used = $media->upload(UploadedFile::fake()->image('page.jpg'));
    $page = Page::create(['title' => 'Page', 'slug' => 'page', 'status' => 'draft', 'visibility' => 'public']);
    $media->attach($used, $page, 'page.featured_media');

    expect(fn () => $media->delete($used))->toThrow(RuntimeException::class);
});
