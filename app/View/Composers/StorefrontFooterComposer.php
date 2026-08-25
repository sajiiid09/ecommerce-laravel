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
        $view->with([
            'footerDescription' => Schema::hasTable('site_settings')
                ? $this->settings->get('footer', 'description', 'Your trusted online shopping destination in Bangladesh.')
                : 'Your trusted online shopping destination in Bangladesh.',
            'footerCopyright' => Schema::hasTable('site_settings')
                ? $this->settings->get('footer', 'copyright', '© '.now()->year.' StoreZ. All rights reserved.')
                : '© '.now()->year.' StoreZ. All rights reserved.',
            'footerSupportEmail' => Schema::hasTable('site_settings')
                ? $this->settings->get('footer', 'support_email', '')
                : '',
            'footerLogo' => Schema::hasTable('site_settings') && ($logoId = $this->settings->get('footer', 'logo_media_id'))
                ? MediaAsset::find($logoId)?->url()
                : null,
            'footerSocialLinks' => Schema::hasTable('site_settings')
                ? (array) $this->settings->get('footer', 'social_links', [])
                : [],
            'footerShowNewsletter' => Schema::hasTable('site_settings')
                ? (bool) $this->settings->get('footer', 'show_newsletter', true)
                : true,
            'footerShowPayments' => Schema::hasTable('site_settings')
                ? (bool) $this->settings->get('footer', 'show_payment_methods', true)
                : true,
            'footerVisible' => Schema::hasTable('site_settings')
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
