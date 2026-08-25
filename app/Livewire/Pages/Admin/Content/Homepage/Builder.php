<?php

namespace App\Livewire\Pages\Admin\Content\Homepage;

use App\Models\HomepageRevision;
use App\Models\HomepageSection;
use App\Models\MediaAsset;
use App\Services\HomepageService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.admin')]
class Builder extends Component
{
    public array $sections = [];

    public int $selectedSectionIndex = 0;

    public string $sectionType = 'hero';

    public array $sectionTypes = [
        'hero', 'trust', 'categories', 'flash_deals', 'bestsellers', 'featured_products',
        'brands', 'new_arrivals', 'banners', 'shop_by_need', 'testimonials', 'newsletter',
    ];

    protected HomepageService $homepage;

    public function boot(HomepageService $homepage): void
    {
        $this->homepage = $homepage;
    }

    public function mount(): void
    {
        $this->authorize('viewAny', HomepageSection::class);
        $this->sections = HomepageSection::ordered()->get()->map(fn (HomepageSection $section): array => [
            'section_key' => $section->section_key,
            'type' => $section->type,
            'title' => $section->title,
            'eyebrow' => $section->eyebrow,
            'subtitle' => $section->subtitle,
            'enabled' => $section->enabled,
            'settings' => $section->settings ?? [],
        ])->all();

        if ($this->sections === []) {
            $this->sections = $this->homepage->fallback();
        }

        $this->selectedSectionIndex = min($this->selectedSectionIndex, max(count($this->sections) - 1, 0));
    }

    public function addSection(): void
    {
        $this->authorize('create', HomepageSection::class);
        $type = $this->sectionType;
        $this->sections[] = [
            'section_key' => 'section-'.uniqid(),
            'type' => $type,
            'title' => ucwords(str_replace('_', ' ', $type)),
            'enabled' => true,
            'settings' => [],
        ];
        $this->selectedSectionIndex = count($this->sections) - 1;
    }

    public function removeSection(int $index): void
    {
        $this->authorize('update', HomepageSection::class);
        unset($this->sections[$index]);
        $this->sections = array_values($this->sections);
        $this->selectedSectionIndex = min($this->selectedSectionIndex, max(count($this->sections) - 1, 0));
    }

    public function move(int $index, int $direction): void
    {
        $this->authorize('update', HomepageSection::class);
        $target = $index + $direction;

        if (! isset($this->sections[$target])) {
            return;
        }

        [$this->sections[$index], $this->sections[$target]] = [$this->sections[$target], $this->sections[$index]];
        $this->selectedSectionIndex = $target;
    }

    public function selectSection(int $index): void
    {
        abort_unless(isset($this->sections[$index]), 404);
        $this->selectedSectionIndex = $index;
    }

    #[On('media-selected')]
    public function selectMedia(int $id, ?string $url = null, ?string $context = null): void
    {
        if (! in_array($context, ['homepage_desktop', 'homepage_mobile'], true)) {
            return;
        }

        $asset = MediaAsset::findOrFail($id);
        Gate::authorize('view', $asset);
        $key = $context === 'homepage_mobile' ? 'mobile_media_id' : 'desktop_media_id';
        $this->sections[$this->selectedSectionIndex]['settings'][$key] = $asset->id;
    }

    public function saveHomepage(): void
    {
        $this->authorize('update', HomepageSection::class);
        $this->homepage->saveSections($this->sections);
        session()->flash('status', 'Homepage layout saved.');
    }

    public function restoreRevision(int $id): void
    {
        $this->authorize('restore', HomepageSection::class);
        $this->homepage->restoreRevision(HomepageRevision::findOrFail($id));
        $this->mount();
        session()->flash('status', 'Homepage revision restored.');
    }

    public function publishRevision(int $id): void
    {
        $this->authorize('publish', HomepageSection::class);
        $this->homepage->publishRevision(HomepageRevision::findOrFail($id));
        $this->mount();
        session()->flash('status', 'Homepage revision published.');
    }

    public function render()
    {
        return view('livewire.pages.admin.content.homepage.builder', [
            'revisions' => HomepageRevision::latest('version')->limit(10)->get(),
            'mediaAssets' => MediaAsset::query()->latest()->limit(20)->get(),
        ]);
    }
}
