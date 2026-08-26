<?php

namespace App\View\Composers;

use App\Models\MediaAsset;
use App\Services\MenuService;
use App\Services\SiteSettingsService;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class StorefrontFooterComposer
{
    public function __construct(
        private readonly SiteSettingsService $settings,
        private readonly MenuService $menus,
    ) {}

    public function compose(View $view): void
    {
        $hasSettings = Schema::hasTable('site_settings');
        $generalLogo = $hasSettings && ($logoId = $this->settings->get('general', 'logo_media_id'))
            ? MediaAsset::find($logoId)?->url()
            : null;

        $view->with([
            'footerStoreName' => $hasSettings ? (string) $this->settings->get('general', 'store_name', 'StoreZ') : 'StoreZ',
            'footerStoreTagline' => $hasSettings ? (string) $this->settings->get('general', 'tagline', 'Shop smarter every day.') : 'Shop smarter every day.',
            'footerSupportPhone' => $hasSettings ? (string) $this->settings->get('general', 'support_phone', '') : '',
            'footerAddress' => $hasSettings ? (string) $this->settings->get('general', 'address', 'Dhaka, 1205, Bangladesh') : 'Dhaka, 1205, Bangladesh',
            'footerDescription' => $hasSettings
                ? $this->settings->get('footer', 'description', 'Your trusted online shopping destination in Bangladesh.')
                : 'Your trusted online shopping destination in Bangladesh.',
            'footerCopyright' => $hasSettings
                ? $this->settings->get('footer', 'copyright', '© '.now()->year.' StoreZ. All rights reserved.')
                : '© '.now()->year.' StoreZ. All rights reserved.',
            'footerSupportEmail' => $hasSettings
                ? $this->settings->get('footer', 'support_email') ?: $this->settings->get('general', 'support_email', 'support@storez.local')
                : 'support@storez.local',
            'footerLogo' => $hasSettings && ($logoId = $this->settings->get('footer', 'logo_media_id'))
                ? (MediaAsset::find($logoId)?->url() ?: $generalLogo)
                : $generalLogo,
            'footerSocialLinks' => $hasSettings
                ? (array) $this->settings->get('footer', 'social_links', [])
                : [],
            'footerShowNewsletter' => $hasSettings
                ? (bool) $this->settings->get('footer', 'show_newsletter', true)
                : true,
            'footerShowPayments' => $hasSettings
                ? (bool) $this->settings->get('footer', 'show_payment_methods', true)
                : true,
            'footerVisible' => $hasSettings
                ? (bool) $this->settings->get('footer', 'show_footer', true)
                : true,
            'footerMenus' => Schema::hasTable('menus') ? [
                'shop' => $this->menus->navigation((string) $this->settings->get('footer', 'shop_menu_key', 'footer-shop')),
                'help' => $this->menus->navigation((string) $this->settings->get('footer', 'help_menu_key', 'footer-help')),
                'company' => $this->menus->navigation((string) $this->settings->get('footer', 'company_menu_key', 'footer-company')),
                'legal' => $this->menus->navigation((string) $this->settings->get('footer', 'legal_menu_key', 'footer-legal')),
            ] : [],
        ]);
    }
}
