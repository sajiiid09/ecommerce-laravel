<?php

use App\Livewire\Pages\Admin\Settings\General;
use App\Models\District;
use App\Models\MediaAsset;
use App\Models\SiteSetting;
use App\Models\User;
use App\Models\UserAddress;
use App\Services\SiteSettingsService;
use Database\Seeders\DistrictSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(DistrictSeeder::class);
});

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
        ->assertSee('Current logo', false)
        ->assertSee('Current favicon', false)
        ->assertSee($logo->url(), false)
        ->assertSee($favicon->url(), false)
        ->assertSee('>Upload<', false)
        ->assertDontSee('Upload and select', false)
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

it('allows administrators to manage delivery districts from general settings', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $component = Livewire::actingAs($admin)->test(General::class);

    $component
        ->set('districtName', 'Test District')
        ->set('districtFee', '125.50')
        ->set('districtSortOrder', 999)
        ->call('saveDistrict')
        ->assertHasNoErrors();

    $district = District::query()->where('name', 'Test District')->firstOrFail();
    expect($district->delivery_fee_minor)->toBe(12550)
        ->and($district->is_active)->toBeTrue()
        ->and($district->sort_order)->toBe(999);

    $component
        ->call('openEditDistrict', $district->id)
        ->set('districtName', 'Renamed District')
        ->set('districtIsActive', false)
        ->call('saveDistrict')
        ->assertHasNoErrors();

    expect($district->fresh()->name)->toBe('Renamed District')
        ->and($district->fresh()->is_active)->toBeFalse();
});

it('validates district values and prevents deleting districts used by addresses', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $district = District::query()->where('name', 'Dhaka')->firstOrFail();
    $customer = User::factory()->create();
    UserAddress::factory()->for($customer)->create(['district_id' => $district->id, 'district' => $district->name]);

    $component = Livewire::actingAs($admin)->test(General::class);
    $component
        ->set('districtName', 'Dhaka')
        ->set('districtFee', '-1')
        ->set('districtSortOrder', -1)
        ->call('saveDistrict')
        ->assertHasErrors(['districtName', 'districtFee', 'districtSortOrder']);

    $component->call('deleteDistrict', $district->id)->assertHasErrors('districtDelete');
    expect($district->fresh())->not->toBeNull();
});

it('seeds the 64 Bangladesh districts idempotently', function () {
    $customDistrict = District::factory()->create([
        'name' => 'Custom Delivery Area',
        'delivery_fee_minor' => 4500,
        'sort_order' => 100,
    ]);

    District::query()->where('name', 'Dhaka')->update(['delivery_fee_minor' => 1]);
    District::query()->where('name', 'Bagerhat')->update(['delivery_fee_minor' => 1]);
    $this->seed(DistrictSeeder::class);

    expect(District::query()->count())->toBe(65)
        ->and(District::query()->where('name', 'Dhaka')->value('delivery_fee_minor'))->toBe(8000)
        ->and(District::query()->where('name', 'Bagerhat')->value('delivery_fee_minor'))->toBe(12000)
        ->and($customDistrict->fresh()->delivery_fee_minor)->toBe(4500);
});

it('renders delivery districts with the Sheaf table pagination and action menu', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    Livewire::actingAs($admin)
        ->test(General::class)
        ->assertSet('perPage', 10)
        ->assertSee('Bagerhat')
        ->assertSee('Actions for Bagerhat', false)
        ->assertDontSee('>Sort<', false)
        ->call('gotoPage', 7)
        ->assertSee('Thakurgaon');
});
