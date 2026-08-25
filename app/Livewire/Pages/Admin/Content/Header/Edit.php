<?php

namespace App\Livewire\Pages\Admin\Content\Header;

use App\Models\MediaAsset;
use App\Models\Menu;
use App\Models\SiteSetting;
use App\Services\SiteSettingsService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.admin')]
class Edit extends Component
{
    public string $logo_url = '';

    public ?int $logo_media_id = null;

    public string $support_text = '';

    public bool $show_search = true;

    public bool $sticky = true;

    public string $desktop_menu_key = 'header-primary';

    public string $mobile_menu_key = 'mobile';

    public bool $show_announcement = true;

    protected SiteSettingsService $settings;

    public function boot(SiteSettingsService $settings): void
    {
        $this->settings = $settings;
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
    }

    public function saveHeader(): void
    {
        $this->authorize('update', SiteSetting::class);
        $this->validate([
            'logo_url' => ['nullable', 'url'],
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
        ]);
    }
}
