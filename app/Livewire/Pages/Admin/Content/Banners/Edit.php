<?php

namespace App\Livewire\Pages\Admin\Content\Banners;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\MediaAsset;
use App\Models\Page;
use App\Models\Product;
use App\Services\BannerService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
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

    public string $eyebrow = '';

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

    public string $theme = 'blue';

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

        $this->name = (string) $banner->name;
        $this->placement = (string) $banner->placement;
        $this->eyebrow = (string) ($banner->eyebrow ?? '');
        $this->title = (string) ($banner->title ?? '');
        $this->description = (string) ($banner->description ?? '');
        $this->cta_label = (string) ($banner->cta_label ?? '');
        $this->destination_type = (string) ($banner->destination_type ?? 'url');
        $this->destination_value = (string) ($banner->destination_value ?? '');
        $this->desktop_media_id = $banner->desktop_media_id ? (int) $banner->desktop_media_id : null;
        $this->mobile_media_id = $banner->mobile_media_id ? (int) $banner->mobile_media_id : null;
        $this->status = (string) $banner->status;
        $this->starts_at = $banner->starts_at?->format('Y-m-d\\TH:i');
        $this->ends_at = $banner->ends_at?->format('Y-m-d\\TH:i');
        $this->sort_order = (int) $banner->sort_order;

        $this->theme = (string) ($banner->settings['theme'] ?? 'blue');
    }

    public function saveBanner(): void
    {
        $this->authorize($this->bannerId ? 'update' : 'create', $this->bannerId ? $this->banner : Banner::class);
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'placement' => ['required', Rule::in(['hero', 'homepage', 'category', 'offers', 'global'])],
            'eyebrow' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'cta_label' => ['nullable', 'string'],
            'destination_type' => ['required', Rule::in(['url', 'page', 'category', 'brand', 'product'])],
            'destination_value' => [
                'nullable',
                'string',
                'max:2048',
                Rule::requiredIf(fn (): bool => $this->destination_type !== 'url'),
            ],
            'desktop_media_id' => ['nullable', 'exists:media_assets,id'],
            'mobile_media_id' => ['nullable', 'exists:media_assets,id'],
            'status' => ['required', 'in:draft,published,scheduled,archived'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'sort_order' => ['integer', 'min:0'],
            'theme' => ['required', Rule::in(['blue', 'red', 'green', 'amber'])],
        ]);

        $data['settings'] = array_merge($this->banner?->settings ?? [], ['theme' => $this->theme]);

        if ($this->status === 'scheduled' && blank($this->starts_at)) {
            $this->addError('starts_at', 'Scheduled banners require a start time.');

            return;
        }

        try {
            $this->banner = $this->banners->save($data, $this->bannerId ? Banner::findOrFail($this->bannerId) : null);
        } catch (\InvalidArgumentException $exception) {
            $this->addError('starts_at', $exception->getMessage());

            return;
        }
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
            'previewDesktopUrl' => $this->desktop_media_id ? MediaAsset::find($this->desktop_media_id)?->url() : null,
            'previewMobileUrl' => $this->mobile_media_id ? MediaAsset::find($this->mobile_media_id)?->url() : null,
            'previewUrl' => $this->destination_type === 'url' ? ($this->destination_value ?: '#') : '#',
            'targetOptions' => [
                'page' => Page::query()->where('status', 'published')->orderBy('title')->get(['id', 'title']),
                'category' => Category::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
                'brand' => Brand::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
                'product' => Product::query()->where('status', 'published')->orderBy('name')->get(['id', 'name']),
            ],
        ]);
    }
}
