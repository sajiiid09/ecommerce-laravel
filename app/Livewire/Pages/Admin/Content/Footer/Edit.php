<?php

namespace App\Livewire\Pages\Admin\Content\Footer;

use App\Enums\ImagePreset;
use App\Models\MediaAsset;
use App\Models\SiteSetting;
use App\Services\FooterColumnService;
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

    /**
     * @var array<int, array{key: string, title: string, enabled: bool, links: array<int, array{name: string, url: string}>}>
     */
    public array $footerColumns = [];

    /**
     * @var array<int, array{name: string, link: string}>
     */
    public array $social_links = [];

    public bool $show_newsletter = true;

    public bool $show_payment_methods = true;

    public bool $show_footer = true;

    protected SiteSettingsService $settings;

    protected MediaService $media;

    protected FooterColumnService $footerColumnService;

    public function boot(SiteSettingsService $settings, MediaService $media, FooterColumnService $footerColumnService): void
    {
        $this->settings = $settings;
        $this->media = $media;
        $this->footerColumnService = $footerColumnService;
    }

    public function mount(): void
    {
        $this->authorize('viewAny', SiteSetting::class);
        $this->description = (string) $this->settings->get('footer', 'description', '');
        $this->copyright = (string) $this->settings->get('footer', 'copyright', '');
        $this->support_email = (string) $this->settings->get('footer', 'support_email', '');
        $this->whatsapp_number = (string) $this->settings->get('footer', 'whatsapp_number', '');
        $this->logo_media_id = $this->settings->get('footer', 'logo_media_id');
        $this->footerColumns = $this->footerColumnService->normalize($this->settings->get('footer', 'columns', []));
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

    public function addFooterLink(int $columnIndex): void
    {
        if (! isset($this->footerColumns[$columnIndex])) {
            return;
        }

        $this->footerColumns[$columnIndex]['links'][] = ['name' => '', 'url' => ''];
    }

    public function removeFooterLink(int $columnIndex, int $linkIndex): void
    {
        if (! isset($this->footerColumns[$columnIndex]['links'][$linkIndex]) || count($this->footerColumns[$columnIndex]['links']) <= 1) {
            return;
        }

        unset($this->footerColumns[$columnIndex]['links'][$linkIndex]);
        $this->footerColumns[$columnIndex]['links'] = array_values($this->footerColumns[$columnIndex]['links']);
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
            'footerColumns' => ['required', 'array', 'size:4'],
            'footerColumns.*.key' => ['required', 'string', 'in:shop,customer-service,company,legal'],
            'footerColumns.*.title' => ['required', 'string', 'max:80'],
            'footerColumns.*.enabled' => ['required', 'boolean'],
            'footerColumns.*.links' => ['required', 'array', 'min:1'],
            'footerColumns.*.links.*.name' => ['required', 'string', 'max:80'],
            'footerColumns.*.links.*.url' => ['required', 'string', 'max:2048'],
            'social_links' => ['array'],
            'social_links.*' => ['array'],
            'social_links.*.name' => ['required', 'string', 'max:50', 'distinct'],
            'social_links.*.link' => ['required', 'url', 'max:2048'],
            'show_newsletter' => ['boolean'],
            'show_payment_methods' => ['boolean'],
            'show_footer' => ['boolean'],
        ]);

        $expectedKeys = ['shop', 'customer-service', 'company', 'legal'];
        $actualKeys = array_map(fn (array $column): ?string => $column['key'] ?? null, $this->footerColumns);

        if ($actualKeys !== $expectedKeys) {
            $this->addError('footerColumns', 'Footer columns must keep their fixed order and keys.');

            return;
        }

        foreach ($this->footerColumns as $columnIndex => $column) {
            foreach ($column['links'] as $linkIndex => $link) {
                if (! $this->footerColumnService->isAllowedUrl($link['url'])) {
                    $this->addError("footerColumns.{$columnIndex}.links.{$linkIndex}.url", 'Use an internal path or an absolute HTTP(S) URL.');
                }
            }
        }

        if ($this->getErrorBag()->any()) {
            return;
        }

        $whatsappNumber = $this->normalizeWhatsappNumber();
        if ($this->getErrorBag()->has('whatsapp_number')) {
            return;
        }
        $socialLinks = [];
        foreach ($this->social_links as $socialLink) {
            $socialLinks[trim($socialLink['name'])] = trim($socialLink['link']);
        }

        $this->footerColumns = $this->footerColumnService->normalize($this->footerColumns);
        $this->settings->set('footer', 'columns', $this->footerColumns);

        foreach (['description', 'copyright', 'support_email', 'logo_media_id', 'show_newsletter', 'show_payment_methods', 'show_footer'] as $key) {
            $this->settings->set('footer', $key, $this->{$key});
        }
        $this->settings->set('footer', 'whatsapp_number', $whatsappNumber);
        $this->whatsapp_number = $whatsappNumber ?? '';
        $this->settings->set('footer', 'social_links', $socialLinks);

        $this->dispatch('notify', content: 'Footer settings updated.', type: 'success');
    }

    public function render()
    {
        return view('livewire.pages.admin.content.footer.edit', [
            'mediaAssets' => MediaAsset::query()->latest()->limit(20)->get(),
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
}
