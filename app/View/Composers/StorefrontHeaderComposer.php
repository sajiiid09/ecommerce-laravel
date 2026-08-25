<?php

namespace App\View\Composers;

use App\Services\SiteSettingsService;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class StorefrontHeaderComposer
{
    public function __construct(private readonly SiteSettingsService $settings) {}

    public function compose(View $view): void
    {
        $view->with('configuredLogo', Schema::hasTable('site_settings')
            ? $this->settings->get('header', 'logo_url', asset('images/brand/storez-logo.png'))
            : asset('images/brand/storez-logo.png'));
    }
}
