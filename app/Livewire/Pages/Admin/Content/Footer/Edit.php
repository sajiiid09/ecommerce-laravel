<?php

namespace App\Livewire\Pages\Admin\Content\Footer;

use App\Models\MediaAsset;
use App\Models\SiteSetting;
use App\Services\SiteSettingsService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.admin')]
class Edit extends Component
{
    public string $description = '';

    public string $copyright = '';

    public string $support_email = '';

    public ?int $logo_media_id = null;

    protected SiteSettingsService $settings;

    public function boot(SiteSettingsService $settings): void
    {
        $this->settings = $settings;
    }

    public function mount(): void
    {
        $this->authorize('viewAny', SiteSetting::class);
        $this->description = (string) $this->settings->get('footer', 'description', '');
        $this->copyright = (string) $this->settings->get('footer', 'copyright', '');
        $this->support_email = (string) $this->settings->get('footer', 'support_email', '');
        $this->logo_media_id = $this->settings->get('footer', 'logo_media_id');
    }

    #[On('media-selected')]
    public function selectMedia(int $id, ?string $url = null, ?string $context = null): void
    {
        if ($context !== 'footer_logo') {
            return;
        }

        $asset = MediaAsset::findOrFail($id);
        Gate::authorize('view', $asset);
        $this->logo_media_id = $asset->id;
    }

    public function saveFooter(): void
    {
        $this->authorize('update', SiteSetting::class);
        $this->validate([
            'description' => ['nullable', 'string'],
            'copyright' => ['nullable', 'string', 'max:255'],
            'support_email' => ['nullable', 'email'],
            'logo_media_id' => ['nullable', 'exists:media_assets,id'],
        ]);

        foreach (['description', 'copyright', 'support_email', 'logo_media_id'] as $key) {
            $this->settings->set('footer', $key, $this->{$key});
        }

        session()->flash('status', 'Footer settings saved.');
    }

    public function render()
    {
        return view('livewire.pages.admin.content.footer.edit', [
            'mediaAssets' => MediaAsset::query()->latest()->limit(20)->get(),
        ]);
    }
}
