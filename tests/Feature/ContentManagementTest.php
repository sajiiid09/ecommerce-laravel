<?php

use App\Models\MediaAsset;
use App\Models\MediaUsage;
use App\Models\Page;
use App\Models\User;
use App\Services\PageService;
use App\Services\RedirectService;
use App\Support\StorefrontCatalog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('allows an administrator to create and publish a page', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $page = app(PageService::class)->save([
        'title' => 'About StoreZ',
        'slug' => 'about-storez',
        'page_type' => 'standard',
        'template' => 'default',
        'content_json' => ['type' => 'doc'],
        'content_html' => '<p>Hello</p>',
        'status' => 'published',
        'visibility' => 'public',
    ]);

    expect($page->status)->toBe('published');

    $this->actingAs($admin)->get('/about-storez')->assertOk()->assertSee('About StoreZ');
    $this->actingAs($admin)->get('/admin/content/pages')->assertOk()->assertSee('About StoreZ');
});

it('rejects duplicate and reserved page slugs', function () {
    Page::create(['title' => 'First', 'slug' => 'same', 'status' => 'draft', 'visibility' => 'public']);

    expect(fn () => app(PageService::class)->save([
        'title' => 'Second', 'slug' => 'same', 'status' => 'draft', 'visibility' => 'public',
    ]))->toThrow(InvalidArgumentException::class);

    expect(fn () => app(PageService::class)->save([
        'title' => 'Admin', 'slug' => 'admin', 'status' => 'draft', 'visibility' => 'public',
    ]))->toThrow(InvalidArgumentException::class);
});

it('requires administrators for CMS routes', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)->get('/admin/content/pages')->assertForbidden();
});

it('rejects unsafe redirect targets and resolves valid redirects', function () {
    expect(fn () => app(RedirectService::class)->save([
        'from_path' => '/old', 'to_url' => 'javascript:alert(1)', 'status_code' => 301, 'enabled' => true,
    ]))->toThrow(InvalidArgumentException::class);

    app(RedirectService::class)->save([
        'from_path' => '/old', 'to_url' => '/new', 'status_code' => 302, 'enabled' => true,
    ]);

    $this->get('/old')->assertRedirect('/new');
});

it('rejects redirect chains and cycles before persistence', function () {
    $redirects = app(RedirectService::class);
    $redirects->save(['from_path' => '/existing', 'to_url' => '/final', 'status_code' => 301, 'enabled' => true]);

    expect(fn () => $redirects->save([
        'from_path' => '/source', 'to_url' => '/existing', 'status_code' => 301, 'enabled' => true,
    ]))->toThrow(InvalidArgumentException::class);

    $file = UploadedFile::fake()->createWithContent(
        'redirects.csv', "from_path,to_url,status_code,enabled\n/a,/b,301,1\n/b,/a,301,1\n"
    );
    expect(fn () => $redirects->import($file))->toThrow(InvalidArgumentException::class);
});

it('only keeps authorized shared media in rich text and synchronizes usage', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin);
    $asset = MediaAsset::create([
        'disk' => 'public', 'path' => 'media/page.jpg', 'filename' => 'page.jpg',
        'mime_type' => 'image/jpeg', 'size' => 100,
    ]);

    $page = app(PageService::class)->save([
        'title' => 'Media Page',
        'slug' => 'media-page',
        'status' => 'draft',
        'visibility' => 'public',
        'content_json' => [
            'type' => 'doc',
            'content' => [['type' => 'image', 'attrs' => ['mediaId' => $asset->id]]],
        ],
        'content_html' => '<p>Text</p><img src="javascript:alert(1)">',
    ]);

    // The service is authorized against the current administrator during the save.
    expect($page->content_json['content'][0]['attrs']['mediaId'])->toBe($asset->id)
        ->and($page->content_html)->not->toContain('javascript:')
        ->and(MediaUsage::where('usable_type', Page::class)->where('usable_id', $page->id)->where('role', 'page.content')->count())->toBe(1);
});

it('keeps StorefrontCatalog as the catalog query boundary', function () {
    expect(StorefrontCatalog::categories())->toBeArray();
});
