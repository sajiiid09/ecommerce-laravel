<?php

namespace App\View\Composers;

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
        $view->with([
            'configuredLogo' => Schema::hasTable('site_settings')
                ? $this->settings->get('header', 'logo_url', asset('images/brand/storez-logo.png'))
                : asset('images/brand/storez-logo.png'),
            'headerSupportText' => Schema::hasTable('site_settings')
                ? $this->settings->get('header', 'support_text', '')
                : '',
            'headerShowSearch' => Schema::hasTable('site_settings')
                ? (bool) $this->settings->get('header', 'show_search', true)
                : true,
            'headerSticky' => Schema::hasTable('site_settings')
                ? (bool) $this->settings->get('header', 'sticky', true)
                : true,
            'headerMenu' => Schema::hasTable('menus')
                ? $this->menus->navigation((string) $this->settings->get('header', 'desktop_menu_key', 'header-primary'))
                : [],
            'mobileMenu' => Schema::hasTable('menus')
                ? $this->menus->navigation((string) $this->settings->get('header', 'mobile_menu_key', 'mobile'))
                : [],
            'headerShowAnnouncement' => Schema::hasTable('site_settings')
                ? (bool) $this->settings->get('header', 'show_announcement', true)
                : true,
        ]);
    }
}
