<?php

namespace App\Livewire\Pages\Admin\Content\Pages;

use App\Models\MediaAsset;
use App\Models\Page;
use App\Models\PageRevision;
use App\Services\PageRevisionService;
use App\Services\PageService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.admin')]
class Edit extends Component
{
    public ?Page $page = null;

    public ?int $pageId = null;

    public string $title = '';

    public string $slug = '';

    public string $page_type = 'standard';

    public string $template = 'default';

    public string $excerpt = '';

    public array|string|null $content_json = null;

    public string $content_html = '';

    public string $status = 'draft';

    public string $visibility = 'public';

    public bool $show_in_navigation = false;

    public bool $is_indexable = true;

    public string $meta_title = '';

    public string $meta_description = '';

    public string $canonical_url = '';

    public ?int $featured_media_id = null;

    public ?string $scheduled_at = null;

    protected PageService $pages;

    protected PageRevisionService $pageRevisions;

    public function boot(PageService $pages, PageRevisionService $pageRevisions): void
    {
        $this->pages = $pages;
        $this->pageRevisions = $pageRevisions;
    }

    public function mount(?Page $page = null): void
    {
        $this->page = $page;
        $this->authorize($page?->exists ? 'update' : 'create', $page?->exists ? $page : Page::class);

        if (! $page?->exists) {
            return;
        }

        $this->pageId = $page->id;

        foreach ([
            'title', 'slug', 'page_type', 'template', 'excerpt', 'content_json', 'content_html', 'status',
            'visibility', 'show_in_navigation', 'is_indexable', 'meta_title', 'meta_description',
            'canonical_url', 'featured_media_id', 'scheduled_at',
        ] as $field) {
            $this->{$field} = $page->{$field};
        }
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('pages', 'slug')->ignore($this->pageId)],
            'page_type' => ['required', 'string', 'max:80'],
            'template' => ['required', 'string', 'max:80'],
            'excerpt' => ['nullable', 'string'],
            'content_json' => ['nullable'],
            'content_html' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,scheduled,published,archived'],
            'visibility' => ['required', 'in:public,private'],
            'show_in_navigation' => ['boolean'],
            'is_indexable' => ['boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'canonical_url' => ['nullable', 'url'],
            'featured_media_id' => ['nullable', 'exists:media_assets,id'],
            'scheduled_at' => ['nullable', 'date'],
        ];
    }

    public function savePage(): void
    {
        $this->authorize($this->pageId ? 'update' : 'create', $this->pageId ? $this->page : Page::class);

        try {
            $this->page = $this->pages->save($this->validate(), $this->pageId ? Page::findOrFail($this->pageId) : null);
            $this->pageId = $this->page->id;
            session()->flash('status', 'Page saved successfully.');
        } catch (\InvalidArgumentException $exception) {
            $this->addError('slug', $exception->getMessage());
        }
    }

    public function restoreRevision(int $id): void
    {
        abort_unless($this->pageId && $this->page, 404);
        $this->authorize('restore', $this->page);
        $revision = PageRevision::where('page_id', $this->pageId)->findOrFail($id);
        $this->page = $this->pageRevisions->restore($revision);
        $this->mount($this->page);
        session()->flash('status', 'Revision restored as a draft.');
    }

    #[On('media-selected')]
    public function selectMedia(int $id, ?string $url = null, ?string $context = null): void
    {
        if ($context !== 'featured') {
            return;
        }

        $asset = MediaAsset::findOrFail($id);
        Gate::authorize('view', $asset);
        $this->featured_media_id = $asset->id;
    }

    public function render()
    {
        $this->authorize('viewAny', Page::class);

        return view('livewire.pages.admin.content.pages.edit', [
            'mediaAssets' => MediaAsset::query()->latest()->limit(20)->get(),
            'revisions' => $this->pageId
                ? PageRevision::where('page_id', $this->pageId)->latest('revision_number')->limit(10)->get()
                : collect(),
        ]);
    }
}
