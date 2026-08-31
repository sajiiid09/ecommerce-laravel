<?php

namespace App\View\Composers;

use App\Models\MediaAsset;
use App\Services\FooterColumnService;
use App\Services\SiteSettingsService;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class StorefrontFooterComposer
{
    public function __construct(
        private readonly SiteSettingsService $settings,
        private readonly FooterColumnService $footerColumns,
    ) {}

    public function compose(View $view): void
    {
        $hasSettings = Schema::hasTable('site_settings');
        $generalLogo = $hasSettings && ($generalLogoId = $this->settings->get('general', 'logo_media_id'))
            ? MediaAsset::find($generalLogoId)?->url()
            : null;
        $footerLogo = $hasSettings && ($footerLogoId = $this->settings->get('footer', 'logo_media_id'))
            ? MediaAsset::find($footerLogoId)?->url()
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
            'footerLogo' => $footerLogo ?: $generalLogo,
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
            'footerColumns' => $hasSettings
                ? $this->footerColumns->forStorefront($this->settings->get('footer', 'columns', []))
                : $this->footerColumns->forStorefront([]),
        ]);
    }
}
