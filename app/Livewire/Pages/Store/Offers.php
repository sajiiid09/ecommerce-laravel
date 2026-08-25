<?php

namespace App\Livewire\Pages\Store;

use App\Services\BannerService;
use App\Services\CatalogQueryService;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Offers extends Component
{
    protected CatalogQueryService $catalog;

    protected BannerService $banners;

    public function boot(CatalogQueryService $catalog, BannerService $banners): void
    {
        $this->catalog = $catalog;
        $this->banners = $banners;
    }

    public function render()
    {
        return view('pages.store.offers-content', [
            'products' => $this->catalog->homepageProducts('flash_deals', 6),
            'banners' => Schema::hasTable('banners')
                ? $this->banners->active('offers')->map(fn ($banner): array => $this->banners->present($banner))->all()
                : [],
        ]);
    }
}
