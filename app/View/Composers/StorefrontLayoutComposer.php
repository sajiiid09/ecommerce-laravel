<?php

namespace App\View\Composers;

use App\Models\MediaAsset;
use App\Services\AnnouncementService;
use App\Services\CartService;
use App\Services\CatalogQueryService;
use App\Services\SiteSettingsService;
use App\Services\WishlistService;
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
        $hasSettings = Schema::hasTable('site_settings');
        $storeName = $hasSettings ? (string) $this->settings->get('general', 'store_name', 'StoreZ') : 'StoreZ';
        $tagline = $hasSettings ? (string) $this->settings->get('general', 'tagline', 'Shop smarter every day.') : 'Shop smarter every day.';

        $view->with([
            'cartItems' => Schema::hasTable('carts') ? app(CartService::class)->present() : StorefrontDemoData::cartItems(),
            'wishlistIds' => app(WishlistService::class)->ids(),
            'wishlistAuthenticated' => auth()->check(),
            'wishlistStorageKey' => auth()->check() ? 'storez-wishlist-user-'.auth()->id() : 'storez-wishlist-guest',
            'categories' => $this->catalog->categoryOptions(),
            'storeName' => $storeName,
            'storeTagline' => $tagline,
            'storeSupportEmail' => $hasSettings ? (string) $this->settings->get('general', 'support_email', 'support@storez.local') : 'support@storez.local',
            'storeSupportPhone' => $hasSettings ? (string) $this->settings->get('general', 'support_phone', '') : '',
            'storeAddress' => $hasSettings ? (string) $this->settings->get('general', 'address', 'Dhaka, 1205, Bangladesh') : 'Dhaka, 1205, Bangladesh',
            'faviconUrl' => $this->generalMediaUrl('favicon_media_id'),
            'whatsappNumber' => $hasSettings
                ? $this->settings->get('footer', 'whatsapp_number')
                : null,
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

    private function generalMediaUrl(string $key): ?string
    {
        if (! Schema::hasTable('site_settings') || ! ($mediaId = $this->settings->get('general', $key))) {
            return null;
        }

        return MediaAsset::find($mediaId)?->url();
    }
}
