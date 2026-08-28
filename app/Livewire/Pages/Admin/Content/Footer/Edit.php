<?php

namespace App\Livewire\Pages\Admin\Content\Footer;

use App\Enums\ImagePreset;
use App\Models\MediaAsset;
use App\Models\Menu;
use App\Models\SiteSetting;
use App\Services\MediaService;
use App\Services\SiteSettingsService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class Edit extends Component
{
    use WithFileUploads;

    public string $description = '';

    public string $copyright = '';

    public string $support_email = '';

    public string $whatsapp_number = '';

    public ?int $logo_media_id = null;

    public $footer_logo_file;

    public string $shop_menu_key = 'footer-shop';

    public string $help_menu_key = 'footer-help';

    public string $company_menu_key = 'footer-company';

    public string $legal_menu_key = 'footer-legal';

    /**
     * @var array<int, array{name: string, link: string}>
     */
    public array $social_links = [];

    public bool $show_newsletter = true;

    public bool $show_payment_methods = true;

    public bool $show_footer = true;

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
        $this->description = (string) $this->settings->get('footer', 'description', '');
        $this->copyright = (string) $this->settings->get('footer', 'copyright', '');
        $this->support_email = (string) $this->settings->get('footer', 'support_email', '');
        $this->whatsapp_number = (string) $this->settings->get('footer', 'whatsapp_number', '');
        $this->logo_media_id = $this->settings->get('footer', 'logo_media_id');
        $this->shop_menu_key = (string) $this->settings->get('footer', 'shop_menu_key', 'footer-shop');
        $this->help_menu_key = (string) $this->settings->get('footer', 'help_menu_key', 'footer-help');
        $this->company_menu_key = (string) $this->settings->get('footer', 'company_menu_key', 'footer-company');
        $this->legal_menu_key = (string) $this->settings->get('footer', 'legal_menu_key', 'footer-legal');
        $this->social_links = $this->normalizeSocialLinks($this->settings->get('footer', 'social_links', []));
        $this->show_newsletter = (bool) $this->settings->get('footer', 'show_newsletter', true);
        $this->show_payment_methods = (bool) $this->settings->get('footer', 'show_payment_methods', true);
        $this->show_footer = (bool) $this->settings->get('footer', 'show_footer', true);
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
        $this->dispatch('notify', content: 'Footer logo selected. Save footer to apply it.', type: 'success');
    }

    public function uploadFooterLogo(): void
    {
        $this->authorize('create', MediaAsset::class);
        $this->validate([
            'footer_logo_file' => ['required', 'image', 'max:10240'],
        ]);

        $asset = $this->media->upload($this->footer_logo_file, 'footer', ImagePreset::Logo);
        $this->logo_media_id = $asset->id;
        $this->reset('footer_logo_file');
        $this->dispatch('notify', content: 'Footer logo uploaded and selected. Save footer to apply it.', type: 'success');
    }

    public function addSocialLink(): void
    {
        $this->social_links[] = ['name' => '', 'link' => ''];
    }

    public function removeSocialLink(int $index): void
    {
        if (! array_key_exists($index, $this->social_links)) {
            return;
        }

        unset($this->social_links[$index]);
        $this->social_links = array_values($this->social_links);
    }

    public function updatedShowFooter(bool $value): void
    {
        $this->persistVisibilitySetting('show_footer', $value);
    }

    public function updatedShowNewsletter(bool $value): void
    {
        $this->persistVisibilitySetting('show_newsletter', $value);
    }

    public function updatedShowPaymentMethods(bool $value): void
    {
        $this->persistVisibilitySetting('show_payment_methods', $value);
    }

    public function saveFooter(): void
    {
        $this->authorize('update', SiteSetting::class);
        $this->validate([
            'description' => ['nullable', 'string'],
            'copyright' => ['nullable', 'string', 'max:255'],
            'support_email' => ['nullable', 'email'],
            'whatsapp_number' => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9][0-9\s().-]*$/'],
            'logo_media_id' => ['nullable', 'exists:media_assets,id'],
            'shop_menu_key' => ['required', 'string', 'max:80'],
            'help_menu_key' => ['required', 'string', 'max:80'],
            'company_menu_key' => ['required', 'string', 'max:80'],
            'legal_menu_key' => ['required', 'string', 'max:80'],
            'social_links' => ['array'],
            'social_links.*' => ['array'],
            'social_links.*.name' => ['required', 'string', 'max:50', 'distinct'],
            'social_links.*.link' => ['required', 'url', 'max:2048'],
            'show_newsletter' => ['boolean'],
            'show_payment_methods' => ['boolean'],
            'show_footer' => ['boolean'],
        ]);

        $whatsappNumber = $this->normalizeWhatsappNumber();
        if ($this->getErrorBag()->has('whatsapp_number')) {
            return;
        }
        $socialLinks = [];
        foreach ($this->social_links as $socialLink) {
            $socialLinks[trim($socialLink['name'])] = trim($socialLink['link']);
        }

        foreach (['description', 'copyright', 'support_email', 'logo_media_id', 'shop_menu_key', 'help_menu_key', 'company_menu_key', 'legal_menu_key', 'show_newsletter', 'show_payment_methods', 'show_footer'] as $key) {
            $this->settings->set('footer', $key, $this->{$key});
        }
        $this->settings->set('footer', 'whatsapp_number', $whatsappNumber);
        $this->whatsapp_number = $whatsappNumber ?? '';
        $this->settings->set('footer', 'social_links', $socialLinks);

        session()->flash('status', 'Footer settings saved.');
    }

    public function render()
    {
        return view('livewire.pages.admin.content.footer.edit', [
            'mediaAssets' => MediaAsset::query()->latest()->limit(20)->get(),
            'menus' => Menu::enabled()->orderBy('name')->get(['key', 'name']),
        ]);
    }

    private function normalizeWhatsappNumber(): ?string
    {
        $value = trim($this->whatsapp_number);

        if ($value === '') {
            return null;
        }

        $normalized = preg_replace('/\D+/', '', $value);

        if (! is_string($normalized) || ! preg_match('/^[1-9][0-9]{7,14}$/', $normalized)) {
            $this->addError('whatsapp_number', 'Enter an international WhatsApp number with country code, such as 8801XXXXXXXXX.');

            return null;
        }

        return $normalized;
    }

    /**
     * @return array<int, array{name: string, link: string}>
     */
    private function normalizeSocialLinks(mixed $socialLinks): array
    {
        if (! is_array($socialLinks)) {
            return [];
        }

        $normalized = [];
        foreach ($socialLinks as $name => $link) {
            if (! is_scalar($link)) {
                continue;
            }

            $normalized[] = [
                'name' => (string) $name,
                'link' => (string) $link,
            ];
        }

        return $normalized;
    }

    private function persistVisibilitySetting(string $key, bool $value): void
    {
        $this->authorize('update', SiteSetting::class);
        $this->settings->set('footer', $key, $value);
        $this->dispatch('notify', content: 'Footer setting updated.', type: 'success');
    }
}
