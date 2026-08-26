<?php

use App\Livewire\Pages\Admin\Settings\General;
use App\Models\MediaAsset;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\SiteSettingsService;
use Illuminate\Support\Facades\Cache;
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
        ->assertSee('General');

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
