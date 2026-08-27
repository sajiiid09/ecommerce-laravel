<?php

namespace App\Livewire\Pages\Admin\Content\Header;

use App\Models\Announcement;
use App\Models\MediaAsset;
use App\Models\Menu;
use App\Models\SiteSetting;
use App\Services\AnnouncementService;
use App\Services\MediaService;
use App\Services\SiteSettingsService;
use Closure;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class Edit extends Component
{
    use WithFileUploads;

    public string $logo_url = '';

    public ?int $logo_media_id = null;

    public $header_logo_file;

    public string $support_text = '';

    public bool $show_search = true;

    public bool $sticky = true;

    public string $desktop_menu_key = 'header-primary';

    public string $mobile_menu_key = 'mobile';

    public bool $show_announcement = true;

    public ?int $announcement_id = null;

    public string $announcement_internal_title = '';

    public string $announcement_message = '';

    public string $announcement_style = 'info';

    public string $announcement_placement = 'top_bar';

    public string $announcement_status = 'draft';

    public string $announcement_priority = 'normal';

    public string $announcement_link_label = '';

    public string $announcement_link_url = '';

    public ?string $announcement_starts_at = null;

    public ?string $announcement_ends_at = null;

    public bool $announcement_dismissible = true;

    protected SiteSettingsService $settings;

    protected MediaService $media;

    protected AnnouncementService $announcements;

    public function boot(SiteSettingsService $settings, MediaService $media, AnnouncementService $announcements): void
    {
        $this->settings = $settings;
        $this->media = $media;
        $this->announcements = $announcements;
    }

    public function mount(): void
    {
        $this->authorize('viewAny', SiteSetting::class);
        $this->logo_url = (string) $this->settings->get('header', 'logo_url', '');
        $this->logo_media_id = $this->settings->get('header', 'logo_media_id');
        $this->support_text = (string) $this->settings->get('header', 'support_text', '');
        $this->show_search = (bool) $this->settings->get('header', 'show_search', true);
        $this->sticky = (bool) $this->settings->get('header', 'sticky', true);
        $this->desktop_menu_key = (string) $this->settings->get('header', 'desktop_menu_key', 'header-primary');
        $this->mobile_menu_key = (string) $this->settings->get('header', 'mobile_menu_key', 'mobile');
        $this->show_announcement = (bool) $this->settings->get('header', 'show_announcement', true);
    }

    #[On('media-selected')]
    public function selectMedia(int $id, ?string $url = null, ?string $context = null): void
    {
        if ($context !== 'header_logo') {
            return;
        }

        $asset = MediaAsset::findOrFail($id);
        Gate::authorize('view', $asset);
        $this->logo_media_id = $asset->id;
        $this->logo_url = $asset->url();
        $this->dispatch('notify', content: 'Header logo selected. Save header to apply it.', type: 'success');
    }

    public function uploadHeaderLogo(): void
    {
        $this->authorize('create', MediaAsset::class);
        $this->validate([
            'header_logo_file' => ['required', 'image', 'max:10240'],
        ]);

        $asset = $this->media->upload($this->header_logo_file, 'header');
        $this->logo_media_id = $asset->id;
        $this->logo_url = $asset->url();
        $this->reset('header_logo_file');
        $this->dispatch('notify', content: 'Header logo uploaded and selected. Save header to apply it.', type: 'success');
    }

    public function openAnnouncementCreate(): void
    {
        $this->authorize('create', Announcement::class);
        $this->resetAnnouncementEditor();
        $this->dispatch('open-modal', id: 'header-announcement-editor');
    }

    public function editAnnouncement(int $id): void
    {
        $announcement = Announcement::findOrFail($id);
        $this->authorize('update', $announcement);

        $this->announcement_id = $announcement->id;
        $this->announcement_internal_title = (string) $announcement->internal_title;
        $this->announcement_message = (string) $announcement->message;
        $this->announcement_style = (string) $announcement->style;
        $this->announcement_placement = (string) $announcement->placement;
        $this->announcement_status = (string) $announcement->status;
        $this->announcement_priority = (string) $announcement->priority;
        $this->announcement_link_label = (string) ($announcement->link_label ?? '');
        $this->announcement_link_url = (string) ($announcement->link_url ?? '');
        $this->announcement_starts_at = $announcement->starts_at?->format('Y-m-d\TH:i');
        $this->announcement_ends_at = $announcement->ends_at?->format('Y-m-d\TH:i');
        $this->announcement_dismissible = (bool) $announcement->dismissible;
        $this->resetValidation();
        $this->dispatch('open-modal', id: 'header-announcement-editor');
    }

    public function saveAnnouncement(): void
    {
        $announcement = $this->announcement_id ? Announcement::findOrFail($this->announcement_id) : null;
        $this->authorize($announcement ? 'update' : 'create', $announcement ?? Announcement::class);

        $validated = $this->validate([
            'announcement_internal_title' => ['required', 'string', 'max:255'],
            'announcement_message' => ['required', 'string'],
            'announcement_style' => ['required', 'in:info,success,warning,danger'],
            'announcement_placement' => ['required', 'string', 'max:80'],
            'announcement_status' => ['required', 'in:draft,published,scheduled,archived'],
            'announcement_priority' => ['required', 'in:low,normal,high'],
            'announcement_dismissible' => ['boolean'],
            'announcement_link_label' => ['nullable', 'string', 'max:255'],
            'announcement_link_url' => ['nullable', 'url'],
            'announcement_starts_at' => ['nullable', 'date'],
            'announcement_ends_at' => ['nullable', 'date'],
        ]);

        try {
            $this->announcements->save([
                'internal_title' => $validated['announcement_internal_title'],
                'message' => $validated['announcement_message'],
                'style' => $validated['announcement_style'],
                'placement' => $validated['announcement_placement'],
                'status' => $validated['announcement_status'],
                'priority' => $validated['announcement_priority'],
                'dismissible' => $validated['announcement_dismissible'],
                'link_label' => $validated['announcement_link_label'],
                'link_url' => $validated['announcement_link_url'],
                'starts_at' => $validated['announcement_starts_at'],
                'ends_at' => $validated['announcement_ends_at'],
            ], $announcement);
        } catch (\InvalidArgumentException $exception) {
            $this->addError('announcement_ends_at', $exception->getMessage());

            return;
        }

        $this->resetAnnouncementEditor();
        session()->flash('status', 'Announcement saved.');
        $this->dispatch('close-modal', id: 'header-announcement-editor');
        $this->dispatch('notify', content: 'Announcement saved.', type: 'success');
    }

    public function deleteAnnouncement(int $id): void
    {
        $announcement = Announcement::findOrFail($id);
        $this->authorize('delete', $announcement);
        $this->announcements->delete($announcement);
        $this->resetAnnouncementEditor();
        session()->flash('status', 'Announcement deleted.');
        $this->dispatch('notify', content: 'Announcement deleted.', type: 'success');
    }

    public function cancelAnnouncementEdit(): void
    {
        $this->resetAnnouncementEditor();
        $this->dispatch('close-modal', id: 'header-announcement-editor');
    }

    public function updatedShowSearch(bool $value): void
    {
        $this->persistBehaviorSetting('show_search', $value);
    }

    public function updatedSticky(bool $value): void
    {
        $this->persistBehaviorSetting('sticky', $value);
    }

    public function updatedShowAnnouncement(bool $value): void
    {
        $this->persistBehaviorSetting('show_announcement', $value);
    }

    public function saveHeader(): void
    {
        $this->authorize('update', SiteSetting::class);
        $this->validate([
            'logo_url' => [
                'nullable',
                'string',
                'max:2048',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $parsedUrl = is_string($value) ? parse_url($value) : false;
                    $isRelativePath = is_string($value) && str_starts_with($value, '/');
                    $isHttpUrl = is_array($parsedUrl) && in_array($parsedUrl['scheme'] ?? null, ['http', 'https'], true) && filled($parsedUrl['host'] ?? null);

                    if (! $isRelativePath && ! $isHttpUrl) {
                        $fail('The logo URL must be an HTTP(S) URL or an absolute path.');
                    }
                },
            ],
            'logo_media_id' => ['nullable', 'exists:media_assets,id'],
            'support_text' => ['nullable', 'string', 'max:255'],
            'show_search' => ['boolean'],
            'sticky' => ['boolean'],
            'desktop_menu_key' => ['required', 'string', 'max:80'],
            'mobile_menu_key' => ['required', 'string', 'max:80'],
            'show_announcement' => ['boolean'],
        ]);

        foreach (['logo_url', 'logo_media_id', 'support_text', 'show_search', 'sticky', 'desktop_menu_key', 'mobile_menu_key', 'show_announcement'] as $key) {
            $this->settings->set('header', $key, $this->{$key});
        }

        session()->flash('status', 'Header settings saved.');
    }

    public function render()
    {
        return view('livewire.pages.admin.content.header.edit', [
            'mediaAssets' => MediaAsset::query()->latest()->limit(20)->get(),
            'menus' => Menu::enabled()->orderBy('name')->get(['key', 'name']),
            'announcement' => Announcement::query()->first(),
        ]);
    }

    private function resetAnnouncementEditor(): void
    {
        $this->reset([
            'announcement_id',
            'announcement_internal_title',
            'announcement_message',
            'announcement_link_label',
            'announcement_link_url',
            'announcement_starts_at',
            'announcement_ends_at',
        ]);
        $this->announcement_style = 'info';
        $this->announcement_placement = 'top_bar';
        $this->announcement_status = 'draft';
        $this->announcement_priority = 'normal';
        $this->announcement_dismissible = true;
        $this->resetValidation();
    }

    private function persistBehaviorSetting(string $key, bool $value): void
    {
        $this->authorize('update', SiteSetting::class);
        $this->settings->set('header', $key, $value);
        $this->dispatch('notify', content: 'Header setting updated.', type: 'success');
    }
}
