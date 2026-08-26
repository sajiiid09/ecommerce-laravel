<?php

namespace App\Livewire\Pages\Admin\Content\Footer;

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
    public string $description = '';

    public string $copyright = '';

    public string $support_email = '';

    public string $whatsapp_number = '';

    public ?int $logo_media_id = null;

    public string $shop_menu_key = 'footer-shop';

    public string $help_menu_key = 'footer-help';

    public string $company_menu_key = 'footer-company';

    public string $legal_menu_key = 'footer-legal';

    public string $social_links_json = '{}';

    public bool $show_newsletter = true;

    public bool $show_payment_methods = true;

    public bool $show_footer = true;

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
        $this->whatsapp_number = (string) $this->settings->get('footer', 'whatsapp_number', '');
        $this->logo_media_id = $this->settings->get('footer', 'logo_media_id');
        $this->shop_menu_key = (string) $this->settings->get('footer', 'shop_menu_key', 'footer-shop');
        $this->help_menu_key = (string) $this->settings->get('footer', 'help_menu_key', 'footer-help');
        $this->company_menu_key = (string) $this->settings->get('footer', 'company_menu_key', 'footer-company');
        $this->legal_menu_key = (string) $this->settings->get('footer', 'legal_menu_key', 'footer-legal');
        $this->social_links_json = json_encode($this->settings->get('footer', 'social_links', []), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}';
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
            'social_links_json' => ['nullable', 'json'],
            'show_newsletter' => ['boolean'],
            'show_payment_methods' => ['boolean'],
            'show_footer' => ['boolean'],
        ]);

        $whatsappNumber = $this->normalizeWhatsappNumber();
        if ($this->getErrorBag()->has('whatsapp_number')) {
            return;
        }
        $socialLinks = json_decode($this->social_links_json ?: '{}', true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($socialLinks)) {
            $this->addError('social_links_json', 'Social links must be a JSON object.');

            return;
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
}
