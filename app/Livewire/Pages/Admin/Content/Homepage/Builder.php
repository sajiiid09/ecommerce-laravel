<?php

namespace App\Livewire\Pages\Admin\Content\Homepage;

use App\Models\HomepageRevision;
use App\Models\HomepageSection;
use App\Models\MediaAsset;
use App\Services\CatalogQueryService;
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

    public int $selectedTestimonialIndex = 0;

    public ?int $editingSectionIndex = null;

    public bool $isAddingSection = false;

    public array $sectionDraft = [];

    public array $sectionTypes = [
        'hero', 'trust', 'categories', 'products', 'brands', 'banners', 'shop_by_need',
        'testimonials', 'newsletter',
    ];

    protected HomepageService $homepage;

    protected CatalogQueryService $catalog;

    public function boot(HomepageService $homepage, CatalogQueryService $catalog): void
    {
        $this->homepage = $homepage;
        $this->catalog = $catalog;
    }

    public function mount(): void
    {
        $this->authorize('viewAny', HomepageSection::class);
        $this->sections = HomepageSection::ordered()->get()->map(fn (HomepageSection $section): array => $this->normalizeSectionForEditor([
            'section_key' => $section->section_key,
            'type' => $section->type,
            'title' => $section->title,
            'eyebrow' => $section->eyebrow,
            'subtitle' => $section->subtitle,
            'enabled' => $section->enabled,
            'settings' => $section->settings ?? [],
        ]))->all();

        if ($this->sections === []) {
            $this->sections = $this->homepage->fallback();
        }

        $this->selectedSectionIndex = min($this->selectedSectionIndex, max(count($this->sections) - 1, 0));
    }

    public function addSection(): void
    {
        $this->authorize('create', HomepageSection::class);
        $this->editingSectionIndex = null;
        $this->isAddingSection = true;
        $this->sectionDraft = [
            'section_key' => 'section-'.uniqid(),
            'type' => 'hero',
            'title' => 'Hero',
            'eyebrow' => '',
            'subtitle' => '',
            'enabled' => true,
            'settings' => [],
        ];
        $this->selectedTestimonialIndex = 0;
        $this->resetValidation();
        $this->dispatch('open-modal', id: 'homepage-section-editor');
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
        $this->selectedTestimonialIndex = 0;
    }

    public function editSection(int $index): void
    {
        $this->authorize('update', HomepageSection::class);
        abort_unless(isset($this->sections[$index]), 404);

        $this->selectedSectionIndex = $index;
        $this->editingSectionIndex = $index;
        $this->isAddingSection = false;
        $this->sectionDraft = $this->normalizeSectionForEditor($this->sections[$index]);
        $this->selectedTestimonialIndex = 0;
        $this->resetValidation();
        $this->dispatch('open-modal', id: 'homepage-section-editor');
    }

    public function updatedSectionDraftType(string $type): void
    {
        if (! $this->isAddingSection || ! in_array($type, $this->sectionTypes, true)) {
            return;
        }

        $this->sectionDraft['title'] = ucwords(str_replace('_', ' ', $type));
        $this->sectionDraft['settings'] = $type === 'products'
            ? ['source' => 'featured', 'sort' => 'default', 'limit' => 6]
            : [];
    }

    public function saveSection(): void
    {
        $this->authorize($this->isAddingSection ? 'create' : 'update', HomepageSection::class);
        abort_unless($this->sectionDraft !== [], 404);
        $this->sectionDraft = $this->normalizeSectionForEditor($this->sectionDraft);

        if ($this->isAddingSection) {
            $this->sections[] = $this->sectionDraft;
            $this->selectedSectionIndex = count($this->sections) - 1;
            $message = 'Homepage section added.';
        } else {
            abort_unless($this->editingSectionIndex !== null && isset($this->sections[$this->editingSectionIndex]), 404);
            $this->sections[$this->editingSectionIndex] = $this->sectionDraft;
            $this->selectedSectionIndex = $this->editingSectionIndex;
            $message = 'Homepage section updated.';
        }

        $this->persistHomepage($message);
        $this->dispatch('close-modal', id: 'homepage-section-editor');
        $this->resetSectionEditor();
    }

    public function updatedSections(mixed $value, ?string $key = null): void
    {
        if (! is_string($key) || ! str_ends_with($key, '.enabled')) {
            return;
        }

        $this->persistHomepage('Homepage section visibility updated.');
    }

    public function addTestimonial(): void
    {
        $this->authorize('update', HomepageSection::class);
        $section = &$this->editableSection();
        abort_unless(($section['type'] ?? null) === 'testimonials', 422);

        $testimonials = $section['settings']['testimonials'] ?? [];
        $testimonials[] = [
            'id' => 'testimonial-'.uniqid(),
            'name' => '',
            'role' => 'Verified customer',
            'rating' => 5,
            'quote' => '',
            'avatar_media_id' => null,
            'enabled' => true,
            'sort_order' => count($testimonials),
        ];
        $section['settings']['testimonials'] = $testimonials;
        $this->selectedTestimonialIndex = count($testimonials) - 1;
        unset($section);
    }

    public function removeTestimonial(int $index): void
    {
        $this->authorize('update', HomepageSection::class);
        $section = &$this->editableSection();
        abort_unless(($section['type'] ?? null) === 'testimonials', 422);

        $testimonials = $section['settings']['testimonials'] ?? [];
        unset($testimonials[$index]);
        $testimonials = array_values($testimonials);

        foreach ($testimonials as $sortOrder => &$testimonial) {
            $testimonial['sort_order'] = $sortOrder;
        }
        unset($testimonial);

        $section['settings']['testimonials'] = $testimonials;
        $this->selectedTestimonialIndex = min($this->selectedTestimonialIndex, max(count($testimonials) - 1, 0));
        unset($section);
    }

    public function moveTestimonial(int $index, int $direction): void
    {
        $this->authorize('update', HomepageSection::class);
        $section = &$this->editableSection();
        abort_unless(($section['type'] ?? null) === 'testimonials', 422);

        $testimonials = $section['settings']['testimonials'] ?? [];
        $target = $index + $direction;

        if (! isset($testimonials[$target])) {
            return;
        }

        [$testimonials[$index], $testimonials[$target]] = [$testimonials[$target], $testimonials[$index]];

        foreach ($testimonials as $sortOrder => &$testimonial) {
            $testimonial['sort_order'] = $sortOrder;
        }
        unset($testimonial);

        $section['settings']['testimonials'] = $testimonials;
        $this->selectedTestimonialIndex = $target;
        unset($section);
    }

    #[On('media-selected')]
    public function selectMedia(int $id, ?string $url = null, ?string $context = null): void
    {
        if (! in_array($context, ['homepage_desktop', 'homepage_mobile', 'homepage_testimonial_avatar'], true)) {
            return;
        }

        $asset = MediaAsset::findOrFail($id);
        Gate::authorize('view', $asset);
        if ($context === 'homepage_testimonial_avatar') {
            $section = &$this->editableSection();
            abort_unless(($section['type'] ?? null) === 'testimonials', 422);
            $testimonials = $section['settings']['testimonials'] ?? [];
            abort_unless(isset($testimonials[$this->selectedTestimonialIndex]), 404);
            $testimonials[$this->selectedTestimonialIndex]['avatar_media_id'] = $asset->id;
            $section['settings']['testimonials'] = $testimonials;
            unset($section);

            return;
        }

        $key = $context === 'homepage_mobile' ? 'mobile_media_id' : 'desktop_media_id';
        $section = &$this->editableSection();
        $section['settings'][$key] = $asset->id;
        unset($section);
    }

    public function saveHomepage(): void
    {
        $this->persistHomepage('Homepage layout saved.');
    }

    private function persistHomepage(string $message): void
    {
        $this->authorize('update', HomepageSection::class);
        $this->homepage->saveSections($this->sections);
        session()->flash('status', $message);
        $this->dispatch('notify', content: $message, type: 'success');
    }

    private function resetSectionEditor(): void
    {
        $this->editingSectionIndex = null;
        $this->isAddingSection = false;
        $this->sectionDraft = [];
    }

    public function sectionQueryLabel(array $section): string
    {
        if (($section['type'] ?? null) !== 'products') {
            return '—';
        }

        return [
            'featured' => 'Featured',
            'newest' => 'Newest',
            'bestsellers' => 'Best sellers',
            'on_sale' => 'On sale',
            'category' => 'Category: '.($section['settings']['category'] ?? 'Not selected'),
            'brand' => 'Brand: '.($section['settings']['brand'] ?? 'Not selected'),
        ][$section['settings']['source'] ?? 'featured'] ?? 'Featured';
    }

    private function normalizeSectionForEditor(array $section): array
    {
        $legacySources = [
            'featured_products' => 'featured',
            'new_arrivals' => 'newest',
            'bestsellers' => 'bestsellers',
            'flash_deals' => 'on_sale',
        ];

        if (isset($legacySources[$section['type'] ?? ''])) {
            $section['settings']['source'] ??= $legacySources[$section['type']];
            $section['type'] = 'products';
        }

        if (($section['type'] ?? null) === 'products') {
            $section['settings'] = $this->catalog->normalizeHomepageProductSettings($section['settings'] ?? []);
        }

        return $section;
    }

    private function &editableSection(): array
    {
        if ($this->sectionDraft !== []) {
            return $this->sectionDraft;
        }

        return $this->sections[$this->selectedSectionIndex];
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
            'categoryOptions' => $this->catalog->categoryOptions(),
            'brandOptions' => $this->catalog->brandOptions(),
        ]);
    }
}
