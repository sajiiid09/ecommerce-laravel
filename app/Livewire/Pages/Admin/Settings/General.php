<?php

namespace App\Livewire\Pages\Admin\Settings;

use App\Enums\ImagePreset;
use App\Models\District;
use App\Models\MediaAsset;
use App\Models\SiteSetting;
use App\Models\UserAddress;
use App\Services\MediaService;
use App\Services\SiteSettingsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class General extends Component
{
    use WithFileUploads, WithPagination;

    public string $storeName = 'StoreZ';

    public string $tagline = 'Shop smarter every day.';

    public ?int $logoMediaId = null;

    public ?int $faviconMediaId = null;

    public $logoFile;

    public $faviconFile;

    public string $supportEmail = '';

    public string $supportPhone = '';

    public string $address = '';

    public string $timezone = 'Asia/Dhaka';

    public ?int $editingDistrictId = null;

    public string $districtName = '';

    public string $districtFee = '0.00';

    public bool $districtIsActive = true;

    public int $districtSortOrder = 0;

    public int $perPage = 10;

    protected SiteSettingsService $settings;

    protected MediaService $media;

    public function boot(SiteSettingsService $settings, MediaService $media): void
    {
        $this->settings = $settings;
        $this->media = $media;
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

    public function uploadStoreLogo(): void
    {
        $this->authorize('create', MediaAsset::class);
        $this->validate([
            'logoFile' => ['required', 'image', 'max:10240'],
        ]);

        $asset = $this->media->upload($this->logoFile, 'general', ImagePreset::Logo);
        $this->logoMediaId = $asset->id;
        $this->reset('logoFile');
        $this->dispatch('notify', content: 'Store logo uploaded and selected. Save settings to apply it.', type: 'success');
    }

    public function uploadStoreFavicon(): void
    {
        $this->authorize('create', MediaAsset::class);
        $this->validate([
            'faviconFile' => ['required', 'image', 'max:10240'],
        ]);

        $asset = $this->media->upload($this->faviconFile, 'general', ImagePreset::Favicon);
        $this->faviconMediaId = $asset->id;
        $this->reset('faviconFile');
        $this->dispatch('notify', content: 'Store favicon uploaded and selected. Save settings to apply it.', type: 'success');
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

    public function openCreateDistrict(): void
    {
        $this->authorize('create', District::class);
        $this->resetDistrictForm();
        $this->resetPage();
        $this->dispatch('open-modal', id: 'district-editor');
    }

    public function openEditDistrict(int $districtId): void
    {
        $district = District::query()->findOrFail($districtId);
        $this->authorize('update', $district);
        $this->editingDistrictId = $district->id;
        $this->districtName = $district->name;
        $this->districtFee = number_format($district->delivery_fee_minor / 100, 2, '.', '');
        $this->districtIsActive = $district->is_active;
        $this->districtSortOrder = $district->sort_order;
        $this->resetValidation();
        $this->dispatch('open-modal', id: 'district-editor');
    }

    public function saveDistrict(): void
    {
        $district = $this->editingDistrictId === null
            ? null
            : District::query()->findOrFail($this->editingDistrictId);

        $this->authorize($district ? 'update' : 'create', $district ?? District::class);
        $this->districtName = trim($this->districtName);

        $validated = $this->validate([
            'districtName' => ['required', 'string', 'max:100', Rule::unique('districts', 'name')->ignore($district?->id)],
            'districtFee' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:999999999.99'],
            'districtIsActive' => ['boolean'],
            'districtSortOrder' => ['required', 'integer', 'min:0', 'max:2147483647'],
        ]);

        $attributes = [
            'name' => trim($validated['districtName']),
            'delivery_fee_minor' => $this->toMinorUnits((string) $validated['districtFee']),
            'is_active' => (bool) $validated['districtIsActive'],
            'sort_order' => (int) $validated['districtSortOrder'],
        ];

        DB::transaction(function () use ($district, $attributes): void {
            $district ??= new District;
            $oldName = $district->exists ? $district->name : null;
            $district->fill($attributes)->save();

            if ($oldName !== null && $oldName !== $district->name) {
                UserAddress::query()->where('district_id', $district->id)->update(['district' => $district->name]);
            }
        });

        $this->resetDistrictForm();
        $this->dispatch('close-modal', id: 'district-editor');
        session()->flash('status', 'Delivery district saved.');
    }

    public function toggleDistrict(int $districtId): void
    {
        $district = District::query()->findOrFail($districtId);
        $this->authorize('update', $district);
        $district->update(['is_active' => ! $district->is_active]);
        session()->flash('status', 'Delivery district status updated.');
    }

    public function deleteDistrict(int $districtId): void
    {
        $district = District::query()->findOrFail($districtId);
        $this->authorize('delete', $district);

        if ($district->userAddresses()->exists()) {
            $this->addError('districtDelete', 'This district is used by a saved address and cannot be deleted.');

            return;
        }

        $district->delete();
        $this->resetPage();
        session()->flash('status', 'Delivery district deleted.');
    }

    public function render()
    {
        return view('livewire.pages.admin.settings.general', [
            'mediaAssets' => MediaAsset::query()
                ->where('mime_type', 'like', 'image/%')
                ->latest()
                ->limit(20)
                ->get(),
            'logoAsset' => $this->logoMediaId ? MediaAsset::query()->find($this->logoMediaId) : null,
            'faviconAsset' => $this->faviconMediaId ? MediaAsset::query()->find($this->faviconMediaId) : null,
            'timezones' => \DateTimeZone::listIdentifiers(),
            'districts' => District::query()->orderBy('sort_order')->orderBy('name')->paginate($this->perPage),
        ]);
    }

    private function resetDistrictForm(): void
    {
        $this->editingDistrictId = null;
        $this->districtName = '';
        $this->districtFee = '0.00';
        $this->districtIsActive = true;
        $this->districtSortOrder = 0;
        $this->resetValidation(['districtName', 'districtFee', 'districtIsActive', 'districtSortOrder', 'districtDelete']);
    }

    private function toMinorUnits(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', number_format((float) $amount, 2, '.', '')), 2, '00');

        return ((int) $whole * 100) + (int) $fraction;
    }

    private function nullableInteger(mixed $value): ?int
    {
        return filled($value) ? (int) $value : null;
    }
}
