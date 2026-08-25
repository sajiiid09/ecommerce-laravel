<?php

namespace App\View\Composers;

use App\Services\SiteSettingsService;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class StorefrontFooterComposer
{
    public function __construct(private readonly SiteSettingsService $settings) {}

    public function compose(View $view): void
    {
        $view->with([
            'footerDescription' => Schema::hasTable('site_settings')
                ? $this->settings->get('footer', 'description', 'Your trusted online shopping destination in Bangladesh.')
                : 'Your trusted online shopping destination in Bangladesh.',
            'footerCopyright' => Schema::hasTable('site_settings')
                ? $this->settings->get('footer', 'copyright', '© '.now()->year.' StoreZ. All rights reserved.')
                : '© '.now()->year.' StoreZ. All rights reserved.',
        ]);
    }
}
