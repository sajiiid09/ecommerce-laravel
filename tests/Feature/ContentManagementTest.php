<?php

use App\Livewire\Pages\Admin\Content\Announcements\Index as AnnouncementsIndex;
use App\Livewire\Pages\Admin\Content\Footer\Edit as FooterEdit;
use App\Livewire\Pages\Admin\Content\Header\Edit as HeaderEdit;
use App\Livewire\Pages\Admin\Content\Homepage\Builder;
use App\Livewire\Pages\Admin\Content\Navigation\Manager as NavigationManager;
use App\Livewire\Pages\Admin\Content\Redirects\Index as RedirectsIndex;
use App\Livewire\Pages\Admin\Reviews\Index as AdminReviewsIndex;
use App\Models\Announcement;
use App\Models\Banner;
use App\Models\Category;
use App\Models\HomepageSection;
use App\Models\MediaAsset;
use App\Models\MediaUsage;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductVariant;
use App\Models\Redirect;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\AnnouncementService;
use App\Services\BannerService;
use App\Services\HomepageService;
use App\Services\MenuService;
use App\Services\PageService;
use App\Services\RedirectService;
use App\Services\SiteSettingsService;
use App\Support\StorefrontCatalog;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;

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

it('renders product reviews with the Sheaf data table and paginator', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $product = Product::create([
        'name' => 'Review Table Product',
        'slug' => 'review-table-product',
        'product_type' => 'simple',
        'short_description' => 'Product for the review table test.',
        'description_json' => [],
        'description_html' => '<p>Product for the review table test.</p>',
        'status' => 'published',
        'visibility' => 'visible',
        'is_featured' => false,
        'taxable' => false,
        'is_indexable' => true,
        'published_at' => now(),
    ]);

    foreach (range(1, 16) as $index) {
        $customer = User::factory()->create(['name' => "Review Customer {$index}"]);
        $review = ProductReview::create([
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'rating' => 5,
            'title' => "Review title {$index}",
            'review' => "Review body {$index}.",
            'status' => 'pending',
        ]);
        $review->update(['created_at' => now()->addSeconds($index)]);
    }

    Livewire::actingAs($admin)
        ->test(AdminReviewsIndex::class)
        ->assertSee('Product Reviews')
        ->assertSee('Filter')
        ->assertSet('status', 'all')
        ->assertSee('Review Table Product')
        ->assertSee('Review title 15')
        ->assertSee('Action')
        ->assertSee('normal-case', false)
        ->assertSee('Pagination Navigation')
        ->assertSee('<table', false)
        ->assertSee('All reviews')
        ->assertSee('value="all"', false)
        ->call('gotoPage', 2)
        ->assertSee('Review title 16')
        ->set('status', 'all')
        ->assertSee('Review title 15')
        ->assertSee('Items per page')
        ->assertSee('wire:model.live="perPage"', false)
        ->set('perPage', 10)
        ->assertSet('perPage', 10)
        ->assertSee('Action')
        ->assertSee('View')
        ->assertSee('Delete')
        ->call('view', ProductReview::where('title', 'Review title 1')->value('id'))
        ->assertSet('viewingReview.id', ProductReview::where('title', 'Review title 1')->value('id'))
        ->assertSee('Review body 1')
        ->assertDispatched('open-modal', id: 'review-details');
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

it('opens redirect creation and editing in the Sheaf modal without showing CSV import', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $component = Livewire::actingAs($admin)->test(RedirectsIndex::class)
        ->assertSee('<table', false)
        ->assertSee('redirect-editor', false)
        ->assertSee('wire:click="openCreate"', false)
        ->assertSee('wire:submit="saveRedirect"', false)
        ->assertDontSee('Import CSV', false)
        ->assertDontSee('Import redirects', false)
        ->call('openCreate')
        ->assertDispatched('open-modal', id: 'redirect-editor')
        ->set('from_path', '/legacy')
        ->set('to_url', '/current')
        ->set('status_code', 301)
        ->set('enabled', true)
        ->call('saveRedirect')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal', id: 'redirect-editor');

    $redirect = Redirect::query()->where('from_path', '/legacy')->firstOrFail();

    $component
        ->call('editRedirect', $redirect->id)
        ->assertSet('editingId', $redirect->id)
        ->assertSet('from_path', '/legacy')
        ->assertDispatched('open-modal', id: 'redirect-editor');
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

it('requires signed page previews and creates redirects when a page slug changes', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $page = app(PageService::class)->save([
        'title' => 'Shipping',
        'slug' => 'shipping',
        'status' => 'published',
        'visibility' => 'public',
        'content_html' => '<p>Shipping information</p>',
    ]);

    $this->actingAs($admin)->get('/admin/content/pages/'.$page->id.'/preview')->assertForbidden();
    $signedPreview = URL::signedRoute('admin.content.pages.preview', ['page' => $page->id]);
    $this->actingAs($admin)->get($signedPreview)->assertSuccessful()->assertSee('Shipping');

    $page = app(PageService::class)->save([
        'title' => 'Shipping',
        'slug' => 'delivery',
        'status' => 'published',
        'visibility' => 'public',
        'content_html' => '<p>Delivery information</p>',
    ], $page);

    $this->get('/shipping')->assertRedirect('/delivery');
});

it('renders persisted navigation, settings, banners, and announcements on the storefront', function () {
    Menu::create(['name' => 'Header Primary', 'key' => 'header-primary', 'location' => 'header_primary', 'enabled' => true]);
    $menu = Menu::where('key', 'header-primary')->firstOrFail();
    MenuItem::create(['menu_id' => $menu->id, 'label' => 'Shipping', 'type' => 'custom_url', 'url' => '/shipping', 'enabled' => true, 'sort_order' => 0]);
    HomepageSection::create(['section_key' => 'cms-banner', 'type' => 'banners', 'title' => 'Campaigns', 'enabled' => true, 'sort_order' => 0, 'settings' => []]);
    Banner::create(['name' => 'Summer campaign', 'placement' => 'homepage', 'title' => 'Summer savings', 'description' => 'Save today', 'cta_label' => 'Shop now', 'destination_type' => 'url', 'destination_value' => '/offers', 'status' => 'published', 'sort_order' => 0]);
    Announcement::create(['internal_title' => 'Notice', 'message' => 'Free delivery this week', 'placement' => 'top_bar', 'status' => 'published']);
    Announcement::create(['internal_title' => 'Storefront notice', 'message' => 'More savings today', 'placement' => 'top_bar', 'style' => 'warning', 'status' => 'published']);
    SiteSetting::create(['group' => 'footer', 'key' => 'description', 'value' => 'Persisted footer content', 'is_public' => true]);

    $this->get('/')->assertSuccessful()
        ->assertSee('Shipping')
        ->assertSee('href="'.url('/shipping').'"', false)
        ->assertSee('Summer savings')
        ->assertSee('group relative min-h-56 overflow-hidden rounded-card bg-store-navy text-white', false)
        ->assertSee('Free delivery this week')
        ->assertSee('bg-amber-400 text-amber-950', false)
        ->assertSee('aria-live="polite"', false)
        ->assertSee('aria-label="Next announcement"', false)
        ->assertDontSee('aria-label="Dismiss announcement"', false)
        ->assertSee('Persisted footer content');

    expect(app(MenuService::class)->navigation('header-primary')[0]['url'])->toBe(url('/shipping'));
});

it('caches transformed menu navigation and invalidates it when an item changes', function () {
    Cache::flush();
    $menu = Menu::create(['name' => 'Cached navigation', 'key' => 'cached-navigation', 'enabled' => true]);
    $item = MenuItem::create([
        'menu_id' => $menu->id,
        'label' => 'Shipping',
        'type' => 'custom_url',
        'url' => '/shipping',
        'enabled' => true,
        'sort_order' => 0,
    ]);
    $menus = app(MenuService::class);

    expect($menus->navigation($menu->key)[0]['label'])->toBe('Shipping')
        ->and(Cache::has('cms:navigation:cached-navigation'))->toBeTrue();

    $menus->saveItem($item, [
        'label' => 'Delivery',
        'type' => 'custom_url',
        'url' => '/delivery',
        'parent_id' => null,
        'enabled' => true,
        'sort_order' => 0,
        'settings' => [],
    ]);

    expect(Cache::has('cms:navigation:cached-navigation'))->toBeFalse()
        ->and($menus->navigation($menu->key)[0]['label'])->toBe('Delivery');
});

it('allows administrators to edit menu settings and add or edit menu items in the Sheaf modal', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $component = Livewire::actingAs($admin)
        ->test(NavigationManager::class)
        ->set('name', 'Store navigation')
        ->set('key', 'header-primary')
        ->call('saveMenu')
        ->assertHasNoErrors()
        ->call('openAddItem')
        ->assertDispatched('open-modal', id: 'menu-item-editor')
        ->set('label', 'Offers')
        ->set('type', 'custom_url')
        ->set('url', 'https://example.com/offers')
        ->call('saveItem')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal', id: 'menu-item-editor');

    $item = MenuItem::where('label', 'Offers')->firstOrFail();

    $component->call('editItem', $item->id)
        ->assertSet('editingItemId', $item->id)
        ->set('label', 'Special offers')
        ->call('saveItem')
        ->assertHasNoErrors();

    expect($item->fresh()->label)->toBe('Special offers')
        ->and(Menu::where('key', 'header-primary')->value('name'))->toBe('Store navigation');

    $this->actingAs($admin)->get('/admin/content/navigation')
        ->assertSee('What these settings control')
        ->assertSee('Identify this menu and choose where its links appear on the storefront.')
        ->assertSee('Add a label and destination, then optionally choose a parent item')
        ->assertSee('Open item editor')
        ->assertSee('Special offers');
});

it('manages the optional WhatsApp floating action from footer settings', function () {
    Cache::flush();

    $this->get('/admin/content/footer')->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create(['is_admin' => false]))
        ->get('/admin/content/footer')
        ->assertForbidden();

    $admin = User::factory()->create(['is_admin' => true]);
    SiteSetting::create(['group' => 'footer', 'key' => 'description', 'value' => 'Existing footer content', 'is_public' => true]);

    $this->get('/')->assertSuccessful()->assertDontSee('https://wa.me/', false);

    Livewire::actingAs($admin)
        ->test(FooterEdit::class)
        ->set('whatsapp_number', '+880 1700-000-000')
        ->call('saveFooter')
        ->assertHasNoErrors();

    expect(SiteSetting::where(['group' => 'footer', 'key' => 'whatsapp_number'])->firstOrFail()->value)
        ->toBe('8801700000000')
        ->and(SiteSetting::where(['group' => 'footer', 'key' => 'description'])->firstOrFail()->value)
        ->toBe('Existing footer content');

    $this->get('/')->assertSuccessful()
        ->assertSee('https://wa.me/8801700000000?text=Hello%20StoreZ', false)
        ->assertSee('viewBox="0 0 48 48"', false)
        ->assertSee('aria-label="Scroll to top"', false);

    Livewire::actingAs($admin)
        ->test(FooterEdit::class)
        ->set('whatsapp_number', '01700000000')
        ->call('saveFooter')
        ->assertHasErrors(['whatsapp_number']);

    Livewire::actingAs($admin)
        ->test(FooterEdit::class)
        ->set('whatsapp_number', '')
        ->call('saveFooter')
        ->assertHasNoErrors();

    expect(SiteSetting::where(['group' => 'footer', 'key' => 'whatsapp_number'])->firstOrFail()->value)
        ->toBeNull();

    $this->get('/')->assertSuccessful()
        ->assertDontSee('https://wa.me/', false)
        ->assertSee('aria-label="Scroll to top"', false);
});

it('auto-saves footer and header visibility changes and dispatches Sheaf toasts', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    SiteSetting::create(['group' => 'footer', 'key' => 'show_footer', 'value' => true, 'is_public' => true]);
    SiteSetting::create(['group' => 'header', 'key' => 'show_search', 'value' => true, 'is_public' => true]);

    Livewire::actingAs($admin)
        ->test(FooterEdit::class)
        ->set('show_footer', false)
        ->assertDispatched('notify', content: 'Footer setting updated.', type: 'success');

    Livewire::actingAs($admin)
        ->test(HeaderEdit::class)
        ->set('show_search', false)
        ->assertDispatched('notify', content: 'Header setting updated.', type: 'success');

    expect(SiteSetting::where(['group' => 'footer', 'key' => 'show_footer'])->firstOrFail()->value)
        ->toBeFalse()
        ->and(SiteSetting::where(['group' => 'header', 'key' => 'show_search'])->firstOrFail()->value)
        ->toBeFalse();

    $this->actingAs($admin)->get('/admin/content/header')
        ->assertSee('role="status"', false)
        ->assertSee('data-slot="checkbox-wrapper"', false)
        ->assertDontSee('Header preview')
        ->assertDontSee('Header logo')
        ->assertDontSee('Shared Media Library');
});

it('auto-saves homepage section visibility changes and dispatches Sheaf toasts', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $section = HomepageSection::create([
        'section_key' => 'hero',
        'type' => 'hero',
        'title' => 'Hero',
        'enabled' => true,
        'sort_order' => 0,
        'settings' => [],
    ]);

    $component = Livewire::actingAs($admin)->test(Builder::class);

    $component->set('sections.0.enabled', false)
        ->assertSet('sections.0.enabled', false)
        ->assertDispatched('notify', content: 'Homepage section visibility updated.', type: 'success');

    expect($section->fresh()->enabled)->toBeFalse();

    $component->set('sections.0.enabled', true)
        ->assertSet('sections.0.enabled', true)
        ->assertDispatched('notify', content: 'Homepage section visibility updated.', type: 'success');

    expect($section->fresh()->enabled)->toBeTrue();
});

it('manages footer visibility and structured social links with the footer editor', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    SiteSetting::create([
        'group' => 'footer',
        'key' => 'social_links',
        'value' => [
            'facebook' => 'https://facebook.com/storez',
            'instagram' => 'https://instagram.com/storez',
        ],
        'is_public' => true,
    ]);

    $component = Livewire::actingAs($admin)->test(FooterEdit::class);

    $component->set('show_footer', false)
        ->set('show_newsletter', false)
        ->set('show_payment_methods', true)
        ->call('addSocialLink')
        ->set('social_links.2.name', 'TikTok')
        ->set('social_links.2.link', 'https://tiktok.com/@storez')
        ->call('removeSocialLink', 1)
        ->call('saveFooter')
        ->assertHasNoErrors();

    expect(SiteSetting::where(['group' => 'footer', 'key' => 'show_footer'])->firstOrFail()->value)
        ->toBeFalse()
        ->and(SiteSetting::where(['group' => 'footer', 'key' => 'show_newsletter'])->firstOrFail()->value)
        ->toBeFalse()
        ->and(SiteSetting::where(['group' => 'footer', 'key' => 'show_payment_methods'])->firstOrFail()->value)
        ->toBeTrue()
        ->and(SiteSetting::where(['group' => 'footer', 'key' => 'social_links'])->firstOrFail()->value)
        ->toBe([
            'facebook' => 'https://facebook.com/storez',
            'TikTok' => 'https://tiktok.com/@storez',
        ]);

    $this->actingAs($admin)->get('/admin/content/footer')
        ->assertSee('data-slot="checkbox-wrapper"', false)
        ->assertSee('Add social link')
        ->assertSee('wire:model="social_links.0.name"', false)
        ->assertDontSee('Footer preview')
        ->assertDontSee('Footer logo')
        ->assertDontSee('Shared Media Library');
});

it('allows administrators to upload and select a footer logo from the shared media library', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['is_admin' => true]);
    $libraryAsset = MediaAsset::create([
        'disk' => 'public',
        'path' => 'media/footer/existing-logo.png',
        'filename' => 'existing-logo.png',
        'mime_type' => 'image/png',
        'size' => 100,
    ]);

    $component = Livewire::actingAs($admin)
        ->test(FooterEdit::class)
        ->call('selectMedia', $libraryAsset->id, $libraryAsset->url(), 'footer_logo')
        ->assertSet('logo_media_id', $libraryAsset->id)
        ->assertDispatched('notify', content: 'Footer logo selected. Save footer to apply it.', type: 'success');

    $file = UploadedFile::fake()->image('footer-logo.png', 320, 120);

    $component->set('footer_logo_file', $file)
        ->call('uploadFooterLogo')
        ->assertHasNoErrors()
        ->assertDispatched('notify', content: 'Footer logo uploaded and selected. Save footer to apply it.', type: 'success');

    $asset = MediaAsset::where('original_filename', 'footer-logo.png')->firstOrFail();

    $component->assertSet('logo_media_id', $asset->id)
        ->assertSee('wire:model="footer_logo_file"', false);

    expect(Storage::disk('public')->exists($asset->path))->toBeTrue();

    $component->call('saveFooter')->assertHasNoErrors();

    expect(SiteSetting::where(['group' => 'footer', 'key' => 'logo_media_id'])->firstOrFail()->value)
        ->toBe($asset->id);
});

it('allows administrators to upload and select a header logo', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['is_admin' => true]);
    $file = UploadedFile::fake()->image('header-logo.png', 320, 120);

    $component = Livewire::actingAs($admin)
        ->test(HeaderEdit::class)
        ->set('header_logo_file', $file)
        ->call('uploadHeaderLogo')
        ->assertHasNoErrors()
        ->assertDispatched('notify', content: 'Header logo uploaded and selected. Save header to apply it.', type: 'success');

    $asset = MediaAsset::where('original_filename', 'header-logo.png')->firstOrFail();

    $component->assertSet('logo_media_id', $asset->id)
        ->assertSet('logo_url', $asset->url())
        ->assertSee('wire:model="header_logo_file"', false);

    expect(Storage::disk('public')->exists($asset->path))->toBeTrue();

    $component->call('saveHeader')->assertHasNoErrors();

    expect(SiteSetting::where(['group' => 'header', 'key' => 'logo_media_id'])->firstOrFail()->value)
        ->toBe($asset->id);
});

it('manages multiple announcements from the dedicated Content panel', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $component = Livewire::actingAs($admin)
        ->test(AnnouncementsIndex::class)
        ->assertSee('Announcements')
        ->assertSee('Create announcement')
        ->assertSee('announcement-editor', false)
        ->call('openCreate')
        ->assertDispatched('open-modal', id: 'announcement-editor')
        ->set('internal_title', 'Delivery notice')
        ->set('message', 'Free delivery this week.')
        ->set('style', 'info')
        ->set('form_placement', 'top_bar')
        ->set('form_status', 'published')
        ->set('priority', 'normal')
        ->set('link_label', 'Shop offers')
        ->set('link_url', '/offers')
        ->set('dismissible', true)
        ->call('saveAnnouncement')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal', id: 'announcement-editor')
        ->assertDispatched('notify', content: 'Announcement saved.', type: 'success')
        ->assertSee('Delivery notice');

    $announcement = Announcement::query()->where('internal_title', 'Delivery notice')->firstOrFail();

    $component->call('openEdit', $announcement->id)
        ->assertSet('editingId', $announcement->id)
        ->assertSet('internal_title', 'Delivery notice')
        ->assertDispatched('open-modal', id: 'announcement-editor')
        ->set('message', 'Updated delivery notice.')
        ->call('saveAnnouncement')
        ->assertHasNoErrors();

    expect($announcement->fresh()->message)->toBe('Updated delivery notice.')
        ->and($announcement->fresh()->link_url)->toBe('/offers');

    $component->call('openCreate')
        ->set('internal_title', 'Second notice')
        ->set('message', 'Second message.')
        ->set('form_placement', 'top_bar')
        ->set('form_status', 'published')
        ->call('saveAnnouncement')
        ->assertHasNoErrors();

    expect(Announcement::where('placement', 'top_bar')->count())->toBe(2);

    $component->call('deleteAnnouncement', $announcement->id)
        ->assertHasNoErrors()
        ->assertDispatched('notify', content: 'Announcement deleted.', type: 'success');

    expect(Announcement::withTrashed()->whereKey($announcement->id)->exists())->toBeFalse();
});

it('allows multiple announcements for the same placement and orders them by priority', function () {
    Announcement::create([
        'internal_title' => 'First announcement',
        'message' => 'First message',
        'placement' => 'top_bar',
        'status' => 'published',
    ]);

    Announcement::create([
        'internal_title' => 'Second announcement',
        'message' => 'Second message',
        'placement' => 'top_bar',
        'priority' => 'high',
        'status' => 'published',
    ]);

    expect(app(AnnouncementService::class)->active('top_bar')->pluck('internal_title')->all())
        ->toBe(['Second announcement', 'First announcement']);
});

it('rejects invalid footer social links without changing saved settings', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    SiteSetting::create([
        'group' => 'footer',
        'key' => 'social_links',
        'value' => ['facebook' => 'https://facebook.com/storez'],
        'is_public' => true,
    ]);

    Livewire::actingAs($admin)
        ->test(FooterEdit::class)
        ->set('social_links.0.link', 'not-a-url')
        ->call('saveFooter')
        ->assertHasErrors(['social_links.0.link']);

    expect(SiteSetting::where(['group' => 'footer', 'key' => 'social_links'])->firstOrFail()->value)
        ->toBe(['facebook' => 'https://facebook.com/storez']);
});

it('renders the homepage sections in a Sheaf data table with a modal editor', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)->get('/admin/content/homepage')
        ->assertSuccessful()
        ->assertSee('<table', false)
        ->assertSee('homepage-section-editor', false)
        ->assertSee('Add section')
        ->assertSeeInOrder(['Order', 'Section', 'Type', 'Query', 'Status', 'Actions'])
        ->assertSee('Version history')
        ->assertDontSee('Choose a section type, then add, edit, reorder, or hide it from the table below.')
        ->assertDontSee('Storefront preview');
});

it('opens Homepage Builder add and edit actions in the Sheaf modal', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    HomepageSection::create([
        'section_key' => 'hero',
        'type' => 'hero',
        'title' => 'Hero',
        'enabled' => true,
        'sort_order' => 0,
        'settings' => [],
    ]);

    $component = Livewire::actingAs($admin)->test(Builder::class)
        ->call('editSection', 0)
        ->assertSet('editingSectionIndex', 0)
        ->assertSet('isAddingSection', false)
        ->assertDispatched('open-modal', id: 'homepage-section-editor')
        ->assertSee('Update section')
        ->assertDontSee('Save section')
        ->set('sectionDraft.title', 'Updated hero')
        ->call('saveSection')
        ->assertDispatched('close-modal', id: 'homepage-section-editor')
        ->assertDispatched('notify', content: 'Homepage section updated.', type: 'success');

    expect(HomepageSection::where('section_key', 'hero')->value('title'))->toBe('Updated hero');

    $component->call('addSection')
        ->assertSet('editingSectionIndex', null)
        ->assertSet('isAddingSection', true)
        ->assertSet('sectionDraft.type', 'hero')
        ->assertDispatched('open-modal', id: 'homepage-section-editor')
        ->set('sectionDraft.type', 'newsletter')
        ->assertSet('sectionDraft.title', 'Newsletter')
        ->assertSee('Section type')
        ->assertSee('Save section')
        ->assertDontSee('Edit section')
        ->call('saveSection')
        ->assertSet('isAddingSection', false)
        ->assertSet('sectionDraft', [])
        ->assertDispatched('notify', content: 'Homepage section added.', type: 'success');
});

it('configures homepage product query settings from the builder modal', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $category = Category::create(['name' => 'Homepage Electronics', 'slug' => 'homepage-electronics', 'is_active' => true]);

    $component = Livewire::actingAs($admin)->test(Builder::class)
        ->call('addSection')
        ->set('sectionDraft.type', 'products')
        ->assertSet('sectionDraft.settings.source', 'featured')
        ->assertSee('Product source')
        ->set('sectionDraft.settings.source', 'category')
        ->set('sectionDraft.settings.category', $category->slug)
        ->set('sectionDraft.settings.sort', 'price_desc')
        ->set('sectionDraft.settings.limit', 3)
        ->call('saveSection');

    $section = HomepageSection::where('title', 'Products')->firstOrFail();

    expect($section->type)->toBe('products')
        ->and($section->settings)->toMatchArray([
            'source' => 'category',
            'category' => 'homepage-electronics',
            'sort' => 'price_desc',
            'limit' => 3,
        ])
        ->and($component->get('sectionDraft'))->toBe([]);
});

it('loads persisted homepage product query settings when editing', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    HomepageSection::create([
        'section_key' => 'brand-products',
        'type' => 'products',
        'title' => 'Brand products',
        'enabled' => true,
        'sort_order' => 0,
        'settings' => ['source' => 'brand', 'brand' => 'fresh', 'sort' => 'newest', 'limit' => 8],
    ]);

    Livewire::actingAs($admin)->test(Builder::class)
        ->call('editSection', 0)
        ->assertSet('sectionDraft.type', 'products')
        ->assertSet('sectionDraft.settings.source', 'brand')
        ->assertSet('sectionDraft.settings.brand', 'fresh')
        ->assertSet('sectionDraft.settings.sort', 'newest')
        ->assertSet('sectionDraft.settings.limit', 8)
        ->assertSee('Product source');
});

it('converts legacy homepage product sections during the data migration', function () {
    $section = HomepageSection::create([
        'section_key' => 'legacy-flash-deals',
        'type' => 'flash_deals',
        'title' => 'Legacy Flash Sale',
        'enabled' => true,
        'sort_order' => 0,
        'settings' => ['limit' => 4],
    ]);

    $migration = require base_path('database/migrations/2026_08_27_103639_normalize_homepage_product_sections.php');
    $migration->up();

    expect($section->fresh()->type)->toBe('products')
        ->and($section->fresh()->settings)->toMatchArray([
            'source' => 'on_sale',
            'sort' => 'default',
            'limit' => 4,
        ]);
});

it('keeps homepage hero slides at full width to prevent carousel clipping', function () {
    HomepageSection::create([
        'section_key' => 'hero',
        'type' => 'hero',
        'title' => 'Hero',
        'enabled' => true,
        'sort_order' => 0,
        'settings' => [],
    ]);
    Banner::create([
        'name' => 'Hero one',
        'placement' => 'hero',
        'title' => 'First hero',
        'status' => 'published',
        'sort_order' => 0,
    ]);
    Banner::create([
        'name' => 'Hero two',
        'placement' => 'hero',
        'title' => 'Second hero',
        'status' => 'published',
        'sort_order' => 1,
    ]);

    $this->get('/')
        ->assertSuccessful()
        ->assertSee('basis-full shrink-0 overflow-hidden rounded-card', false)
        ->assertSee('First hero')
        ->assertSee('Second hero');
});

it('renders homepage categories in a five-item carousel on large screens', function () {
    HomepageSection::create([
        'section_key' => 'categories',
        'type' => 'categories',
        'title' => 'Shop by Category',
        'enabled' => true,
        'sort_order' => 0,
        'settings' => [],
    ]);

    foreach (['Electronics', "Men's Fashion", 'Phones', 'Audio', 'Fashion', "Women's Fashion"] as $name) {
        Category::create([
            'name' => $name,
            'slug' => str()->slug($name),
            'is_active' => true,
        ]);
    }

    $this->get('/')
        ->assertSuccessful()
        ->assertSee('lg:basis-[calc((100%-3rem)/5)]', false)
        ->assertSee('Shop by Category')
        ->assertSee("Women's Fashion");
});

it('renders the homepage newsletter input with a visible field treatment', function () {
    HomepageSection::create([
        'section_key' => 'newsletter',
        'type' => 'newsletter',
        'title' => 'Stay in the loop',
        'enabled' => true,
        'sort_order' => 0,
        'settings' => [],
    ]);

    $this->get('/')
        ->assertSuccessful()
        ->assertSee('overflow-hidden rounded-control', false)
        ->assertSee('bg-white px-3 py-2.5 text-sm text-store-ink', false)
        ->assertSee('placeholder:text-store-muted', false)
        ->assertSee('Email address');
});

it('renders homepage product sections as five-card carousels without dots', function () {
    Cache::flush();

    foreach ([
        ['key' => 'flash-deals', 'source' => 'on_sale', 'title' => 'Flash Sale Products'],
        ['key' => 'bestsellers', 'source' => 'bestsellers', 'title' => 'Best Sellers Products'],
        ['key' => 'featured-products', 'source' => 'featured', 'title' => 'Featured Products'],
        ['key' => 'new-arrivals', 'source' => 'newest', 'title' => 'New Arrivals Products'],
    ] as $index => $productSection) {
        HomepageSection::create([
            'section_key' => $productSection['key'],
            'type' => 'products',
            'title' => $productSection['title'],
            'enabled' => true,
            'sort_order' => $index,
            'settings' => ['source' => $productSection['source'], 'sort' => 'default', 'limit' => 6],
        ]);
    }

    foreach (range(1, 6) as $index) {
        $product = Product::create([
            'name' => "Showcase Product {$index}",
            'slug' => "showcase-product-{$index}",
            'product_type' => 'simple',
            'short_description' => 'Homepage showcase product.',
            'description_json' => [],
            'description_html' => '<p>Homepage showcase product.</p>',
            'status' => 'published',
            'visibility' => 'visible',
            'is_featured' => true,
            'taxable' => false,
            'is_indexable' => true,
            'published_at' => now(),
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => "SHOWCASE-{$index}",
            'name' => 'Default',
            'combination_key' => 'default',
            'regular_price_minor' => 1000 + $index,
            'sale_price_minor' => $index <= 3 ? 900 + $index : null,
            'is_active' => true,
            'is_default' => true,
        ]);
    }

    $response = $this->get('/')->assertSuccessful();
    $content = $response->getContent();

    expect(substr_count($content, 'aria-roledescription="carousel"'))->toBe(4)
        ->and($content)->toContain('xl:basis-[calc((100%-3rem)/5)]')
        ->and($content)->toContain('Showcase Product 1')
        ->and($content)->toContain('Add to Cart')
        ->and($content)->not->toContain('role="tablist"');
});

it('archives expired scheduled content during the CMS sync command', function () {
    $banner = Banner::create([
        'name' => 'Expired campaign',
        'placement' => 'homepage',
        'status' => 'scheduled',
        'starts_at' => now()->subHour(),
        'ends_at' => now()->subMinute(),
    ]);

    $announcement = Announcement::create([
        'internal_title' => 'Expired notice',
        'message' => 'Expired',
        'placement' => 'top_bar',
        'status' => 'scheduled',
        'starts_at' => now()->subHour(),
        'ends_at' => now()->subMinute(),
    ]);

    Artisan::call('content:sync-scheduled');

    expect($banner->fresh()->status)->toBe('archived')
        ->and($announcement->fresh()->status)->toBe('archived');
});

it('applies structured storefront settings and redirect health checks', function () {
    SiteSetting::create(['group' => 'header', 'key' => 'show_announcement', 'value' => false, 'is_public' => true]);
    SiteSetting::create(['group' => 'footer', 'key' => 'show_footer', 'value' => false, 'is_public' => true]);
    SiteSetting::create(['group' => 'footer', 'key' => 'social_links', 'value' => ['facebook' => 'https://facebook.com/storez'], 'is_public' => true]);

    $redirects = app(RedirectService::class);
    $redirects->save(['from_path' => '/legacy', 'to_url' => '/current', 'status_code' => 301, 'enabled' => true]);
    $redirects->save(['from_path' => '/disabled', 'to_url' => '/current', 'status_code' => 301, 'enabled' => false]);

    $health = $redirects->health(Redirect::query()->get());
    $disabled = Redirect::where('from_path', '/disabled')->firstOrFail();
    expect($health[$disabled->id])->toContain('Disabled');

    $loopA = Redirect::create(['from_path' => '/loop-a', 'to_url' => '/loop-b', 'status_code' => 301, 'enabled' => true]);
    Redirect::create(['from_path' => '/loop-b', 'to_url' => '/loop-a', 'status_code' => 301, 'enabled' => true]);
    $broken = Redirect::create(['from_path' => '/broken', 'to_url' => '/missing', 'status_code' => 301, 'enabled' => true]);
    $health = $redirects->health(Redirect::query()->get());
    expect($health[$loopA->id])->toContain('Redirect loop')
        ->and($health[$broken->id])->toContain('Broken destination');

    $this->get('/')->assertSuccessful()->assertDontSee('Free delivery this week');
});

it('seeds idempotent demo CMS content without overwriting settings', function () {
    Artisan::call('db:seed', ['--class' => DatabaseSeeder::class, '--no-interaction' => true]);

    $menuCount = Menu::count();
    $menuItemCount = MenuItem::count();
    $sectionCount = HomepageSection::count();
    $bannerCount = Banner::count();
    $announcementCount = Announcement::count();
    $productCount = Product::count();
    $approvedReviewCount = ProductReview::where('status', 'approved')->count();

    expect(SiteSetting::where('group', 'general')->pluck('key')->all())
        ->toEqualCanonicalizing(['store_name', 'tagline', 'logo_media_id', 'favicon_media_id', 'support_email', 'support_phone', 'address', 'timezone'])
        ->and(SiteSetting::where('group', 'header')->pluck('key')->all())
        ->toEqualCanonicalizing(['logo_url', 'logo_media_id', 'support_text', 'show_search', 'sticky', 'desktop_menu_key', 'mobile_menu_key', 'show_announcement'])
        ->and(SiteSetting::where('group', 'footer')->pluck('key')->all())
        ->toEqualCanonicalizing(['description', 'copyright', 'support_email', 'whatsapp_number', 'logo_media_id', 'shop_menu_key', 'help_menu_key', 'company_menu_key', 'legal_menu_key', 'social_links', 'show_newsletter', 'show_payment_methods', 'show_footer'])
        ->and(Menu::whereIn('key', ['header-primary', 'mobile', 'footer-shop', 'footer-help', 'footer-company', 'footer-legal'])->count())->toBe(6)
        ->and($sectionCount)->toBe(12)
        ->and(Banner::where('placement', 'hero')->count())->toBe(3)
        ->and(HomepageSection::where('section_key', 'testimonials')->firstOrFail()->settings['testimonials'])->toHaveCount(5)
        ->and(Banner::where('name', 'StoreZ Demo Everyday Savings')->exists())->toBeTrue()
        ->and(Announcement::where('internal_title', 'StoreZ Demo Delivery Notice')->exists())->toBeTrue()
        ->and($productCount)->toBe(30)
        ->and($approvedReviewCount)->toBe(90);

    $this->get('/')->assertSuccessful()
        ->assertSee('Your trusted online shopping destination in Bangladesh.')
        ->assertSee('Everyday savings are here')
        ->assertSee('Free delivery is available on selected StoreZ orders this week.');
    foreach (['Nusrat Jahan', 'Rafiq Ahmed', 'Tania Rahman', 'Farhan Kabir', 'Maliha Sultana'] as $name) {
        $this->get('/')->assertSee($name);
    }

    app(SiteSettingsService::class)->set('footer', 'description', 'Custom footer description');
    $testimonialSection = HomepageSection::where('section_key', 'testimonials')->firstOrFail();
    $testimonialSection->update(['settings' => ['testimonials' => [[
        'id' => 'custom-testimonial',
        'name' => 'Administrator testimonial',
        'role' => 'VIP customer',
        'rating' => 5,
        'quote' => 'Administrator-authored feedback.',
        'avatar_media_id' => null,
        'enabled' => true,
        'sort_order' => 0,
    ]]]]);
    $heroBanner = Banner::where('placement', 'hero')->firstOrFail();
    $heroBanner->update(['title' => 'Administrator hero title']);

    Artisan::call('db:seed', ['--class' => DatabaseSeeder::class, '--no-interaction' => true]);

    expect(Menu::count())->toBe($menuCount)
        ->and(MenuItem::count())->toBe($menuItemCount)
        ->and(HomepageSection::count())->toBe($sectionCount)
        ->and(Banner::count())->toBe($bannerCount)
        ->and(Announcement::count())->toBe($announcementCount)
        ->and(SiteSetting::where(['group' => 'footer', 'key' => 'description'])->firstOrFail()->value)->toBe('Custom footer description')
        ->and(HomepageSection::where('section_key', 'testimonials')->firstOrFail()->settings['testimonials'][0]['name'])->toBe('Administrator testimonial')
        ->and(Banner::where('placement', 'hero')->firstOrFail()->title)->toBe('Administrator hero title');

    $invalidItems = MenuItem::query()->get()->filter(function (MenuItem $item): bool {
        if ($item->type === 'route') {
            return ! is_string($item->route_name) || ! Route::has($item->route_name);
        }

        return ! is_string($item->url) || ! collect(['/category', '/offers', '/search', '/account', '/orders', '/wishlist', '/brands/', '/login', '/register'])
            ->contains(fn (string $prefix): bool => str_starts_with($item->url, $prefix));
    });

    expect($invalidItems)->toBeEmpty();

    $this->get('/')->assertSuccessful()
        ->assertSee('Shop')
        ->assertSee('Custom footer description')
        ->assertSee('Everyday savings are here')
        ->assertSee('Free delivery is available on selected StoreZ orders this week.')
        ->assertSee('Back to Better Deals Every Day!');
});

it('renders hydrated hero banners and testimonial carousel data', function () {
    Banner::create([
        'name' => 'Hero with fallback theme',
        'placement' => 'hero',
        'eyebrow' => 'Limited time',
        'title' => 'Hero carousel slide',
        'description' => 'A slide without custom media.',
        'cta_label' => 'Shop now',
        'destination_type' => 'url',
        'destination_value' => '/offers',
        'status' => 'published',
        'sort_order' => 0,
        'settings' => ['theme' => 'red'],
    ]);
    HomepageSection::create([
        'section_key' => 'hero',
        'type' => 'hero',
        'title' => 'Hero',
        'enabled' => true,
        'sort_order' => 0,
        'settings' => [],
    ]);
    HomepageSection::create([
        'section_key' => 'testimonials',
        'type' => 'testimonials',
        'title' => 'Customer voices',
        'enabled' => true,
        'sort_order' => 1,
        'settings' => ['testimonials' => [[
            'id' => 'testimonial-one',
            'name' => 'Test Customer',
            'role' => 'Verified customer',
            'rating' => 4,
            'quote' => 'A structured testimonial quote.',
            'avatar_media_id' => null,
            'enabled' => true,
            'sort_order' => 0,
        ]]],
    ]);

    $sections = app(HomepageService::class)->sections(false);
    $hero = collect($sections)->firstWhere('type', 'hero');
    $testimonials = collect($sections)->firstWhere('type', 'testimonials');

    expect($hero['settings']['heroBanners'][0]['title'])->toBe('Hero carousel slide')
        ->and($hero['settings']['heroBanners'][0]['theme'])->toBe('red')
        ->and($hero['settings']['heroBanners'][0]['url'])->toBe('/offers')
        ->and($testimonials['settings']['testimonials'][0]['name'])->toBe('Test Customer');

    $this->get('/')->assertSuccessful()
        ->assertSee('Hero carousel slide')
        ->assertSee('A structured testimonial quote.')
        ->assertSee('Customer testimonials');
});

it('allows administrators to add and reorder homepage testimonials', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    HomepageSection::create([
        'section_key' => 'testimonials',
        'type' => 'testimonials',
        'title' => 'Testimonials',
        'enabled' => true,
        'sort_order' => 0,
        'settings' => ['testimonials' => []],
    ]);

    $this->actingAs($admin);

    Livewire::test(Builder::class)
        ->call('editSection', 0)
        ->call('addTestimonial')
        ->assertSet('selectedTestimonialIndex', 0)
        ->assertSet('sectionDraft.settings.testimonials.0.role', 'Verified customer')
        ->call('addTestimonial')
        ->call('moveTestimonial', 1, -1)
        ->assertSet('sectionDraft.settings.testimonials.0.id', fn (string $id): bool => str_starts_with($id, 'testimonial-'));
});

it('validates homepage testimonial settings before persistence', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin);

    expect(fn () => app(HomepageService::class)->saveSections([[
        'section_key' => 'testimonials',
        'type' => 'testimonials',
        'title' => 'Testimonials',
        'enabled' => true,
        'settings' => ['testimonials' => [[
            'id' => 'duplicate',
            'name' => '',
            'rating' => 6,
            'quote' => '',
            'enabled' => true,
            'sort_order' => 0,
        ], [
            'id' => 'duplicate',
            'name' => 'Another customer',
            'rating' => 5,
            'quote' => 'Another quote.',
            'enabled' => true,
            'sort_order' => 1,
        ]]],
    ]]))->toThrow(InvalidArgumentException::class);
});

it('hides the unused banner schedule card and separates the filter toolbar', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)->get('/admin/content/banners')
        ->assertSuccessful()
        ->assertSee('mb-5', false)
        ->assertDontSee('Upcoming schedule');
});

it('opens banner editing when optional banner fields are null', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $banner = Banner::create([
        'name' => 'Banner with optional fields',
        'placement' => 'homepage',
        'status' => 'draft',
    ]);

    $this->actingAs($admin)->get("/admin/content/banners/{$banner->id}/edit")
        ->assertSuccessful()
        ->assertSee('Edit banner')
        ->assertSee('Banner with optional fields');
});

it('rebuilds stale cached CMS model payloads before rendering storefront layouts', function () {
    Announcement::create([
        'internal_title' => 'Cached announcement',
        'message' => 'Cached announcement message',
        'placement' => 'top_bar',
        'status' => 'published',
    ]);
    Banner::create([
        'name' => 'Cached banner',
        'placement' => 'homepage',
        'title' => 'Cached banner title',
        'destination_type' => 'url',
        'destination_value' => '/offers',
        'status' => 'published',
    ]);
    $menu = Menu::create(['name' => 'Cached menu', 'key' => 'cached-menu', 'enabled' => true]);

    Cache::put('cms:announcements:top_bar', collect(['stale announcement']), 300);
    Cache::put('cms:banners:homepage', collect(['stale banner']), 300);
    Cache::put('cms:menu:cached-menu', 'stale menu', 300);

    expect(app(AnnouncementService::class)->active('top_bar')->first())->toBeInstanceOf(Announcement::class)
        ->and(app(BannerService::class)->active('homepage')->first())->toBeInstanceOf(Banner::class)
        ->and(app(MenuService::class)->menu($menu->key))->toBeInstanceOf(Menu::class);

    $this->get('/login')->assertSuccessful()->assertSee('Sign in');
});
