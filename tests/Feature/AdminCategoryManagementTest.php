<?php

use App\Livewire\Pages\Admin\Catalog\Categories\Index;
use App\Models\Category;
use App\Models\MediaAsset;
use App\Models\MediaUsage;
use App\Models\User;
use App\Services\CategoryExportService;
use App\Services\CategoryImportService;
use App\Services\CategoryService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('exposes the category editor parent property to Livewire', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    Livewire::test(Index::class)->set('parent_id', null)->assertSet('parent_id', null);
});

it('saves category media and rejects circular ancestry', function () {
    $asset = MediaAsset::create([
        'disk' => 'public', 'path' => 'media/category.jpg', 'filename' => 'category.jpg',
        'mime_type' => 'image/jpeg', 'size' => 100,
    ]);
    $root = app(CategoryService::class)->save(['name' => 'Electronics', 'slug' => 'electronics', 'media_asset_id' => $asset->id]);
    $child = app(CategoryService::class)->save(['name' => 'Audio', 'slug' => 'audio', 'parent_id' => $root->id]);

    expect($root->fresh()->media_asset_id)->toBe($asset->id)
        ->and($child->parent_id)->toBe($root->id);
    expect(MediaUsage::where('media_asset_id', $asset->id)->where('usable_id', $root->id)->where('role', 'category.featured_media')->exists())->toBeTrue();

    expect(fn () => app(CategoryService::class)->save([
        'name' => 'Electronics', 'slug' => 'electronics', 'parent_id' => $child->id,
    ], $root))->toThrow(InvalidArgumentException::class);
});

it('atomically validates and imports category hierarchy', function () {
    Storage::fake('local');
    $file = UploadedFile::fake()->createWithContent('categories.csv', "name,slug,parent_slug,is_active,sort_order\nElectronics,electronics,,1,1\nAudio,audio,electronics,true,2\n");
    $service = app(CategoryImportService::class);
    $import = $service->upload($file);

    expect($service->validate($import)['errors'])->toBe(0)
        ->and($service->import($import))->toBe(['created' => 2, 'updated' => 0]);

    $this->assertDatabaseHas('categories', ['slug' => 'electronics', 'parent_id' => null]);
    $this->assertDatabaseHas('categories', ['slug' => 'audio', 'parent_id' => Category::where('slug', 'electronics')->value('id')]);
});

it('rejects duplicate and missing category parents before writing', function () {
    Storage::fake('local');
    $file = UploadedFile::fake()->createWithContent('categories.csv', "name,slug,parent_slug\nAudio,audio,missing\nAudio Copy,audio,\n");
    $summary = app(CategoryImportService::class)->validate(app(CategoryImportService::class)->upload($file));

    expect($summary['errors'])->toBe(2);
    $this->assertDatabaseCount('categories', 0);
});

it('updates categories by slug and exports hierarchy fields', function () {
    Storage::fake('local');
    Category::create(['name' => 'Old Name', 'slug' => 'electronics', 'is_active' => true]);
    $service = app(CategoryImportService::class);
    $import = $service->upload(UploadedFile::fake()->createWithContent('categories.csv', "name,slug,description,is_active,sort_order\nNew Name,electronics,Updated description,0,4\n"), 'update_by_slug');

    expect($service->validate($import)['errors'])->toBe(0)
        ->and($service->import($import))->toBe(['created' => 0, 'updated' => 1]);

    $export = app(CategoryExportService::class)->export();
    Storage::disk('local')->assertExists($export->path);
    expect(Storage::disk('local')->get($export->path))->toContain('parent_slug', 'electronics')
        ->and($export->record_count)->toBe(1);
});

it('rejects categories deeper than the supported hierarchy', function () {
    $parentId = null;

    for ($level = 1; $level <= CategoryService::MAX_DEPTH; $level++) {
        $category = app(CategoryService::class)->save(['name' => 'Level '.$level, 'slug' => 'level-'.$level, 'parent_id' => $parentId]);
        $parentId = $category->id;
    }

    expect(fn () => app(CategoryService::class)->save([
        'name' => 'Too Deep', 'slug' => 'too-deep', 'parent_id' => $parentId,
    ]))->toThrow(InvalidArgumentException::class);
});
