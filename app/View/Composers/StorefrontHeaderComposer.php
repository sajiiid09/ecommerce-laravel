<?php

namespace App\View\Composers;

use App\Models\MediaAsset;
use App\Services\MenuService;
use App\Services\SiteSettingsService;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class StorefrontHeaderComposer
{
    public function __construct(
        private readonly SiteSettingsService $settings,
        private readonly MenuService $menus,
    ) {}

    public function compose(View $view): void
    {
        $hasSettings = Schema::hasTable('site_settings');
        $generalLogo = $this->generalMediaUrl('logo_media_id');
        $headerLogo = $hasSettings ? $this->settings->get('header', 'logo_url') : null;

        $view->with([
            'storeName' => $hasSettings ? (string) $this->settings->get('general', 'store_name', 'StoreZ') : 'StoreZ',
            'configuredLogo' => filled($headerLogo) && ! in_array($headerLogo, ['/images/brand/storez-logo.png', asset('images/brand/storez-logo.png')], true)
                ? $headerLogo
                : ($generalLogo ?: asset('images/brand/storez-logo.png')),
            'headerSupportText' => $hasSettings
                ? $this->settings->get('header', 'support_text', '')
                : '',
            'headerShowSearch' => $hasSettings
                ? (bool) $this->settings->get('header', 'show_search', true)
                : true,
            'headerSticky' => $hasSettings
                ? (bool) $this->settings->get('header', 'sticky', true)
                : true,
            'headerMenu' => Schema::hasTable('menus')
                ? $this->menus->navigation((string) $this->settings->get('header', 'desktop_menu_key', 'header-primary'))
                : [],
            'mobileMenu' => Schema::hasTable('menus')
                ? $this->menus->navigation((string) $this->settings->get('header', 'mobile_menu_key', 'mobile'))
                : [],
            'headerShowAnnouncement' => $hasSettings
                ? (bool) $this->settings->get('header', 'show_announcement', true)
                : true,
        ]);
    }

    private function generalMediaUrl(string $key): ?string
    {
        if (! Schema::hasTable('site_settings') || ! ($mediaId = $this->settings->get('general', $key))) {
            return null;
        }

        return MediaAsset::find($mediaId)?->url();
    }
}
