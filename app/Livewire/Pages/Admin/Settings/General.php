<?php

namespace App\Livewire\Pages\Admin\Settings;

use App\Models\MediaAsset;
use App\Models\SiteSetting;
use App\Services\SiteSettingsService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.admin')]
class General extends Component
{
    public string $storeName = 'StoreZ';

    public string $tagline = 'Shop smarter every day.';

    public ?int $logoMediaId = null;

    public ?int $faviconMediaId = null;

    public string $supportEmail = '';

    public string $supportPhone = '';

    public string $address = '';

    public string $timezone = 'Asia/Dhaka';

    protected SiteSettingsService $settings;

    public function boot(SiteSettingsService $settings): void
    {
        $this->settings = $settings;
    }

    public function mount(): void
    {
        $this->authorize('viewAny', SiteSetting::class);

        $this->storeName = (string) $this->settings->get('general', 'store_name', 'StoreZ');
        $this->tagline = (string) $this->settings->get('general', 'tagline', 'Shop smarter every day.');
        $this->logoMediaId = $this->nullableInteger($this->settings->get('general', 'logo_media_id'));
        $this->faviconMediaId = $this->nullableInteger($this->settings->get('general', 'favicon_media_id'));
        $this->supportEmail = (string) $this->settings->get('general', 'support_email', 'support@storez.local');
        $this->supportPhone = (string) $this->settings->get('general', 'support_phone', '');
        $this->address = (string) $this->settings->get('general', 'address', 'Dhaka, 1205, Bangladesh');
        $this->timezone = (string) $this->settings->get('general', 'timezone', 'Asia/Dhaka');
    }

    #[On('media-selected')]
    public function selectMedia(int $id, ?string $url = null, ?string $context = null): void
    {
        if (! in_array($context, ['general_logo', 'general_favicon'], true)) {
            return;
        }

        $asset = MediaAsset::findOrFail($id);
        Gate::authorize('view', $asset);

        if ($context === 'general_logo') {
            $this->logoMediaId = $asset->id;
        } else {
            $this->faviconMediaId = $asset->id;
        }
    }

    public function save(): void
    {
        $this->authorize('update', SiteSetting::class);

        $validated = $this->validate([
            'storeName' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'logoMediaId' => [
                'nullable',
                'integer',
                Rule::exists('media_assets', 'id')->where(fn ($query) => $query->where('mime_type', 'like', 'image/%')),
            ],
            'faviconMediaId' => [
                'nullable',
                'integer',
                Rule::exists('media_assets', 'id')->where(fn ($query) => $query->where('mime_type', 'like', 'image/%')),
            ],
            'supportEmail' => ['nullable', 'email', 'max:255'],
            'supportPhone' => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9][0-9\s().-]*$/'],
            'address' => ['nullable', 'string', 'max:500'],
            'timezone' => ['required', 'string', Rule::in(\DateTimeZone::listIdentifiers())],
        ]);

        foreach ([
            'store_name' => 'storeName',
            'tagline' => 'tagline',
            'logo_media_id' => 'logoMediaId',
            'favicon_media_id' => 'faviconMediaId',
            'support_email' => 'supportEmail',
            'support_phone' => 'supportPhone',
            'address' => 'address',
            'timezone' => 'timezone',
        ] as $key => $property) {
            $value = $validated[$property];
            $this->settings->set('general', $key, is_string($value) ? trim($value) : $value);
        }

        session()->flash('status', 'General settings saved.');
    }

    public function render()
    {
        return view('livewire.pages.admin.settings.general', [
            'mediaAssets' => MediaAsset::query()
                ->where('mime_type', 'like', 'image/%')
                ->latest()
                ->limit(20)
                ->get(),
            'timezones' => \DateTimeZone::listIdentifiers(),
        ]);
    }

    private function nullableInteger(mixed $value): ?int
    {
        return filled($value) ? (int) $value : null;
    }
}
