<?php

use App\Livewire\Pages\Admin\Settings\General;
use App\Models\MediaAsset;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\SiteSettingsService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('protects general settings and lets admins save store identity and contact details', function () {
    $this->get(route('admin.settings.general'))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create())
        ->get(route('admin.settings.general'))
        ->assertForbidden();

    $admin = User::factory()->create(['is_admin' => true]);
    $logo = MediaAsset::create([
        'disk' => 'public',
        'path' => 'media/store-logo.png',
        'filename' => 'store-logo.png',
        'mime_type' => 'image/png',
        'size' => 100,
    ]);
    $favicon = MediaAsset::create([
        'disk' => 'public',
        'path' => 'media/store-favicon.png',
        'filename' => 'store-favicon.png',
        'mime_type' => 'image/png',
        'size' => 100,
    ]);

    Livewire::actingAs($admin)
        ->test(General::class)
        ->set('storeName', 'Acme Store')
        ->set('tagline', 'Better shopping every day.')
        ->set('logoMediaId', $logo->id)
        ->set('faviconMediaId', $favicon->id)
        ->set('supportEmail', 'help@acme.test')
        ->set('supportPhone', '+880 1700-000-000')
        ->set('address', '12 Market Road, Dhaka')
        ->set('timezone', 'Asia/Dhaka')
        ->call('save')
        ->assertHasNoErrors();

    expect(SiteSetting::where('group', 'general')->pluck('value', 'key')->all())
        ->toMatchArray([
            'store_name' => 'Acme Store',
            'tagline' => 'Better shopping every day.',
            'logo_media_id' => $logo->id,
            'favicon_media_id' => $favicon->id,
            'support_email' => 'help@acme.test',
            'support_phone' => '+880 1700-000-000',
            'address' => '12 Market Road, Dhaka',
            'timezone' => 'Asia/Dhaka',
        ]);

    $this->actingAs($admin)
        ->get(route('admin.settings.general'))
        ->assertSuccessful()
        ->assertSee('General settings')
        ->assertSee('General')
        ->assertSee('Select timezone', false)
        ->assertSee('Upload a new logo')
        ->assertSee('Upload a new favicon')
        ->assertDontSee('Shared Media Library');

    $this->get('/')->assertSuccessful()
        ->assertSee('Acme Store')
        ->assertSee('Better shopping every day.')
        ->assertSee('help@acme.test')
        ->assertSee('+880 1700-000-000')
        ->assertSee('12 Market Road, Dhaka')
        ->assertSee('media/store-logo.png', false)
        ->assertSee('media/store-favicon.png', false)
        ->assertSee('<title>Acme Store - Shop Smarter</title>', false);
});

it('validates general settings fields and preserves unrelated content settings', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $nonImage = MediaAsset::create([
        'disk' => 'public',
        'path' => 'media/manual.pdf',
        'filename' => 'manual.pdf',
        'mime_type' => 'application/pdf',
        'size' => 100,
    ]);
    SiteSetting::create(['group' => 'header', 'key' => 'show_search', 'value' => false, 'is_public' => true]);
    SiteSetting::create(['group' => 'footer', 'key' => 'description', 'value' => 'Keep this footer copy', 'is_public' => true]);

    Livewire::actingAs($admin)
        ->test(General::class)
        ->set('storeName', str_repeat('x', 256))
        ->set('supportEmail', 'not-an-email')
        ->set('supportPhone', 'not a phone')
        ->set('logoMediaId', 999999)
        ->set('faviconMediaId', $nonImage->id)
        ->set('timezone', 'Mars/Olympus')
        ->call('save')
        ->assertHasErrors(['storeName', 'supportEmail', 'supportPhone', 'logoMediaId', 'faviconMediaId', 'timezone']);

    expect(SiteSetting::where(['group' => 'header', 'key' => 'show_search'])->firstOrFail()->value)->toBeFalse()
        ->and(SiteSetting::where(['group' => 'footer', 'key' => 'description'])->firstOrFail()->value)->toBe('Keep this footer copy');
});

it('invalidates general settings cache immediately after saving', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $settings = app(SiteSettingsService::class);
    $settings->set('general', 'store_name', 'Cached Store');
    Cache::remember('cms:settings:general:store_name', 300, fn (): string => 'stale value');

    Livewire::actingAs($admin)
        ->test(General::class)
        ->set('storeName', 'Fresh Store')
        ->call('save')
        ->assertHasNoErrors();

    expect($settings->get('general', 'store_name'))->toBe('Fresh Store');
});

it('caches missing settings instead of querying them on every request', function () {
    Cache::flush();
    $settings = app(SiteSettingsService::class);

    expect($settings->get('header', 'unset_setting'))->toBeNull()
        ->and(Cache::has('cms:settings:v2:header:unset_setting'))->toBeTrue();

    DB::enableQueryLog();
    DB::flushQueryLog();
    expect($settings->get('header', 'unset_setting'))->toBeNull()
        ->and(DB::getQueryLog())->toBeEmpty();
});

it('allows administrators to upload and select a store logo and favicon', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['is_admin' => true]);
    $logoFile = UploadedFile::fake()->image('uploaded-store-logo.png', 320, 120);
    $faviconFile = UploadedFile::fake()->image('uploaded-store-favicon.png', 160, 80);

    $component = Livewire::actingAs($admin)->test(General::class)
        ->assertSee('wire:model="logoFile"', false)
        ->assertSee('wire:model="faviconFile"', false)
        ->set('logoFile', $logoFile)
        ->call('uploadStoreLogo')
        ->assertHasNoErrors()
        ->assertDispatched('notify', content: 'Store logo uploaded and selected. Save settings to apply it.', type: 'success');

    $logo = MediaAsset::where('original_filename', 'uploaded-store-logo.png')->firstOrFail();

    $component->assertSet('logoMediaId', $logo->id)
        ->set('faviconFile', $faviconFile)
        ->call('uploadStoreFavicon')
        ->assertHasNoErrors()
        ->assertDispatched('notify', content: 'Store favicon uploaded and selected. Save settings to apply it.', type: 'success');

    $favicon = MediaAsset::where('original_filename', 'uploaded-store-favicon.png')->firstOrFail();

    $component->assertSet('faviconMediaId', $favicon->id);
    expect($logo->extension)->toBe('webp')
        ->and($logo->mime_type)->toBe('image/webp')
        ->and($favicon->extension)->toBe('webp')
        ->and($favicon->mime_type)->toBe('image/webp')
        ->and($favicon->width)->toBe(64)
        ->and($favicon->height)->toBe(64);
    Storage::disk('public')->assertExists($logo->path);
    Storage::disk('public')->assertExists($favicon->path);
});

it('rejects non-image files for store branding uploads', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['is_admin' => true]);
    $file = UploadedFile::fake()->create('store-branding.pdf', 100, 'application/pdf');

    Livewire::actingAs($admin)->test(General::class)
        ->set('logoFile', $file)
        ->call('uploadStoreLogo')
        ->assertHasErrors(['logoFile']);

    expect(MediaAsset::where('filename', 'store-branding.pdf')->exists())->toBeFalse();
});
