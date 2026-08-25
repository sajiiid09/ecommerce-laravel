<?php

namespace App\Livewire\Pages\Admin\Content\Banners;

use App\Models\Banner;
use App\Models\MediaAsset;
use App\Services\BannerService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.admin')]
class Edit extends Component
{
    public ?Banner $banner = null;

    public ?int $bannerId = null;

    public string $name = '';

    public string $placement = 'homepage';

    public string $title = '';

    public string $description = '';

    public string $cta_label = '';

    public string $destination_type = 'url';

    public string $destination_value = '';

    public ?int $desktop_media_id = null;

    public ?int $mobile_media_id = null;

    public string $status = 'draft';

    public ?string $starts_at = null;

    public ?string $ends_at = null;

    public int $sort_order = 0;

    protected BannerService $banners;

    public function boot(BannerService $banners): void
    {
        $this->banners = $banners;
    }

    public function mount(?Banner $banner = null): void
    {
        $this->banner = $banner;
        $this->authorize($banner?->exists ? 'update' : 'create', $banner?->exists ? $banner : Banner::class);

        if (! $banner?->exists) {
            return;
        }

        $this->bannerId = $banner->id;

        foreach ([
            'name', 'placement', 'title', 'description', 'cta_label', 'destination_type', 'destination_value',
            'desktop_media_id', 'mobile_media_id', 'status', 'starts_at', 'ends_at', 'sort_order',
        ] as $field) {
            $this->{$field} = $banner->{$field};
        }
    }

    public function saveBanner(): void
    {
        $this->authorize($this->bannerId ? 'update' : 'create', $this->bannerId ? $this->banner : Banner::class);
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'placement' => ['required', 'string', 'max:80'],
            'title' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'cta_label' => ['nullable', 'string'],
            'destination_type' => ['nullable', 'string', 'max:40'],
            'destination_value' => ['nullable', 'string', 'max:2048'],
            'desktop_media_id' => ['nullable', 'exists:media_assets,id'],
            'mobile_media_id' => ['nullable', 'exists:media_assets,id'],
            'status' => ['required', 'in:draft,published,scheduled,archived'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'sort_order' => ['integer', 'min:0'],
        ]);

        $this->banner = $this->banners->save($data, $this->bannerId ? Banner::findOrFail($this->bannerId) : null);
        $this->bannerId = $this->banner->id;
        session()->flash('status', 'Banner saved successfully.');
    }

    #[On('media-selected')]
    public function selectMedia(int $id, ?string $url = null, ?string $context = null): void
    {
        if (! in_array($context, ['desktop', 'mobile'], true)) {
            return;
        }

        $asset = MediaAsset::findOrFail($id);
        Gate::authorize('view', $asset);

        if ($context === 'mobile') {
            $this->mobile_media_id = $asset->id;
        } else {
            $this->desktop_media_id = $asset->id;
        }
    }

    public function render()
    {
        $this->authorize('viewAny', Banner::class);

        return view('livewire.pages.admin.content.banners.edit', [
            'mediaAssets' => MediaAsset::query()->latest()->limit(20)->get(),
        ]);
    }
}
