<?php

namespace App\View\Composers;

use App\Services\AnnouncementService;
use App\Services\CatalogQueryService;
use App\Services\SiteSettingsService;
use App\Support\StorefrontDemoData;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class StorefrontLayoutComposer
{
    public function __construct(
        private readonly AnnouncementService $announcements,
        private readonly CatalogQueryService $catalog,
        private readonly SiteSettingsService $settings,
    ) {}

    public function compose(View $view): void
    {
        $view->with([
            'cartItems' => StorefrontDemoData::cartItems(),
            'categories' => $this->catalog->categoryOptions(),
            'trustItems' => [
                ['title' => 'Secure payments', 'description' => 'Protected checkout options.'],
                ['title' => 'Fast delivery', 'description' => 'Reliable delivery across Bangladesh.'],
                ['title' => 'Easy returns', 'description' => 'Helpful support when you need it.'],
            ],
            'announcements' => Schema::hasTable('announcements') && (! Schema::hasTable('site_settings') || (bool) $this->settings->get('header', 'show_announcement', true))
                ? $this->announcements->active('top_bar')
                : collect(),
        ]);
    }
}
