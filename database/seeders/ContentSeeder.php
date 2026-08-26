<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Banner;
use App\Models\HomepageSection;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\SiteSetting;
use App\Services\ContentPublishingService;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    public function __construct(private readonly ContentPublishingService $publishing) {}

    public function run(): void
    {
        $this->seedSettings();
        $this->seedMenus();
        $this->seedHomepageSections();
        $this->seedBanner();
        $this->seedHeroBanners();
        $this->seedAnnouncement();
    }

    private function seedSettings(): void
    {
        $settings = [
            'header' => [
                'logo_url' => '/images/brand/storez-logo.png',
                'logo_media_id' => null,
                'support_text' => 'Dhaka, 1205',
                'show_search' => true,
                'sticky' => true,
                'desktop_menu_key' => 'header-primary',
                'mobile_menu_key' => 'mobile',
                'show_announcement' => true,
            ],
            'footer' => [
                'description' => 'Your trusted online shopping destination in Bangladesh.',
                'copyright' => '© '.now()->year.' StoreZ. All rights reserved.',
                'support_email' => 'support@storez.local',
                'whatsapp_number' => null,
                'logo_media_id' => null,
                'shop_menu_key' => 'footer-shop',
                'help_menu_key' => 'footer-help',
                'company_menu_key' => 'footer-company',
                'legal_menu_key' => 'footer-legal',
                'social_links' => [
                    'facebook' => 'https://facebook.com/storez',
                    'instagram' => 'https://instagram.com/storez',
                ],
                'show_newsletter' => true,
                'show_payment_methods' => true,
                'show_footer' => true,
            ],
        ];

        foreach ($settings as $group => $groupSettings) {
            foreach ($groupSettings as $key => $value) {
                $setting = SiteSetting::firstOrCreate(
                    ['group' => $group, 'key' => $key],
                    ['value' => $value, 'is_public' => true],
                );

                if ($setting->wasRecentlyCreated) {
                    $this->publishing->invalidate('settings', $group.':'.$key);
                }
            }
        }
    }

    private function seedMenus(): void
    {
        $menus = [
            'header-primary' => [
                'name' => 'Header Primary',
                'location' => 'header_primary',
                'items' => [
                    ['label' => 'Home', 'type' => 'route', 'route_name' => 'store.home'],
                    ['label' => 'Shop', 'url' => '/category', 'type' => 'custom_url'],
                    ['label' => 'Offers', 'url' => '/offers', 'type' => 'custom_url'],
                    ['label' => 'New Arrivals', 'url' => '/search?sort=newest', 'type' => 'custom_url'],
                ],
            ],
            'mobile' => [
                'name' => 'Mobile Menu',
                'location' => 'mobile',
                'items' => [
                    ['label' => 'Home', 'type' => 'route', 'route_name' => 'store.home'],
                    ['label' => 'Shop', 'url' => '/category', 'type' => 'custom_url'],
                    ['label' => 'Offers', 'url' => '/offers', 'type' => 'custom_url'],
                    ['label' => 'Account', 'url' => '/account', 'type' => 'custom_url'],
                ],
            ],
            'footer-shop' => [
                'name' => 'Footer Shop',
                'location' => 'footer_shop',
                'items' => [
                    ['label' => 'All Categories', 'url' => '/category', 'type' => 'custom_url'],
                    ['label' => 'Offers', 'url' => '/offers', 'type' => 'custom_url'],
                    ['label' => 'Search Products', 'url' => '/search', 'type' => 'custom_url'],
                ],
            ],
            'footer-help' => [
                'name' => 'Footer Help',
                'location' => 'footer_help',
                'items' => [
                    ['label' => 'My Account', 'url' => '/account', 'type' => 'custom_url'],
                    ['label' => 'Orders', 'url' => '/orders', 'type' => 'custom_url'],
                    ['label' => 'Wishlist', 'url' => '/wishlist', 'type' => 'custom_url'],
                ],
            ],
            'footer-company' => [
                'name' => 'Footer Company',
                'location' => 'footer_company',
                'items' => [
                    ['label' => 'Featured Brands', 'url' => '/brands/soundmax', 'type' => 'custom_url'],
                    ['label' => 'Electronics', 'url' => '/category/electronics', 'type' => 'custom_url'],
                    ['label' => 'Contact StoreZ', 'url' => '/offers', 'type' => 'custom_url'],
                ],
            ],
            'footer-legal' => [
                'name' => 'Footer Legal',
                'location' => 'footer_legal',
                'items' => [
                    ['label' => 'Sign In', 'url' => '/login', 'type' => 'custom_url'],
                    ['label' => 'Create Account', 'url' => '/register', 'type' => 'custom_url'],
                    ['label' => 'StoreZ Home', 'type' => 'route', 'route_name' => 'store.home'],
                ],
            ],
        ];

        foreach ($menus as $key => $data) {
            $menu = Menu::firstOrCreate(
                ['key' => $key],
                ['name' => $data['name'], 'location' => $data['location'], 'enabled' => true],
            );
            $menuChanged = $menu->wasRecentlyCreated;

            foreach ($data['items'] as $sortOrder => $item) {
                $menuItem = MenuItem::firstOrCreate(
                    ['menu_id' => $menu->id, 'label' => $item['label']],
                    [
                        'type' => $item['type'],
                        'url' => $item['url'] ?? null,
                        'route_name' => $item['route_name'] ?? null,
                        'enabled' => true,
                        'sort_order' => $sortOrder,
                        'settings' => [],
                    ],
                );
                $menuChanged = $menuChanged || $menuItem->wasRecentlyCreated;
            }

            if ($menuChanged) {
                $this->publishing->invalidate('menu', $key);
            }
        }
    }

    private function seedHomepageSections(): void
    {
        $sections = [
            ['section_key' => 'hero', 'type' => 'hero', 'title' => 'Back to Better Deals Every Day!', 'eyebrow' => 'StoreZ everyday value', 'subtitle' => 'Groceries, fashion, electronics and more at unbeatable prices.', 'settings' => ['cta' => 'Shop Now', 'url' => '/offers']],
            ['section_key' => 'trust', 'type' => 'trust', 'title' => 'Why Shop with StoreZ?', 'settings' => []],
            ['section_key' => 'categories', 'type' => 'categories', 'title' => 'Shop by Category', 'settings' => ['limit' => 12]],
            ['section_key' => 'flash-deals', 'type' => 'flash_deals', 'title' => 'Flash Sale', 'settings' => ['limit' => 6]],
            ['section_key' => 'bestsellers', 'type' => 'bestsellers', 'title' => 'Best Sellers', 'settings' => ['limit' => 6]],
            ['section_key' => 'featured-products', 'type' => 'featured_products', 'title' => 'Fresh Picks for You', 'settings' => ['limit' => 6]],
            ['section_key' => 'brands', 'type' => 'brands', 'title' => 'Top Brands You Trust', 'settings' => ['limit' => 12]],
            ['section_key' => 'new-arrivals', 'type' => 'new_arrivals', 'title' => 'New Arrivals', 'settings' => ['limit' => 6]],
            ['section_key' => 'banners', 'type' => 'banners', 'title' => 'Featured Promotions', 'settings' => ['placement' => 'homepage', 'limit' => 6]],
            ['section_key' => 'shop-by-need', 'type' => 'shop_by_need', 'title' => 'Shop by Need', 'subtitle' => 'Find practical picks for every part of your day.', 'settings' => ['content_json' => "Daily essentials\nHome upgrades\nPersonal care"]],
            ['section_key' => 'testimonials', 'type' => 'testimonials', 'title' => 'What Our Customers Say', 'subtitle' => 'Real value, delivered with care.', 'settings' => ['content_json' => '“Great value and fast delivery.” — A StoreZ customer']],
            ['section_key' => 'newsletter', 'type' => 'newsletter', 'title' => 'Stay in the loop', 'subtitle' => 'Get offers and product updates in your inbox.', 'settings' => []],
        ];

        foreach ($sections as $sortOrder => $section) {
            $section = HomepageSection::firstOrCreate(
                ['section_key' => $section['section_key']],
                [
                    'type' => $section['type'],
                    'title' => $section['title'],
                    'eyebrow' => $section['eyebrow'] ?? null,
                    'subtitle' => $section['subtitle'] ?? null,
                    'enabled' => true,
                    'sort_order' => $sortOrder,
                    'settings' => $section['settings'],
                ],
            );

            if ($section->section_key === 'testimonials' && $this->isLegacyDemoTestimonials($section->settings)) {
                $section->forceFill(['settings' => ['testimonials' => $this->demoTestimonials()]])->save();
                $this->publishing->invalidate('homepage');
            }

            if ($section->wasRecentlyCreated) {
                $this->publishing->invalidate('homepage');
            }
        }
    }

    private function seedBanner(): void
    {
        $banner = Banner::firstOrCreate(
            ['name' => 'StoreZ Demo Everyday Savings'],
            [
                'placement' => 'homepage',
                'title' => 'Everyday savings are here',
                'description' => 'Discover fresh deals across the StoreZ catalog.',
                'cta_label' => 'Shop offers',
                'destination_type' => 'url',
                'destination_value' => '/offers',
                'status' => 'published',
                'sort_order' => 0,
                'settings' => [],
            ],
        );

        if ($banner->wasRecentlyCreated) {
            $this->publishing->invalidate('banner', 'homepage');
        }
    }

    private function seedHeroBanners(): void
    {
        $banners = [
            [
                'name' => 'StoreZ Demo Hero Everyday Value',
                'eyebrow' => 'StoreZ everyday value',
                'title' => 'Back to Better Deals Every Day!',
                'description' => 'Groceries, fashion, electronics and more at unbeatable prices.',
                'cta_label' => 'Shop now',
                'destination_value' => '/offers',
                'theme' => 'blue',
            ],
            [
                'name' => 'StoreZ Demo Hero Fashion Festival',
                'eyebrow' => 'StoreZ fashion festival',
                'title' => 'Fresh Looks, Big Savings!',
                'description' => 'Trending styles and everyday essentials at prices you will love.',
                'cta_label' => 'Shop fashion',
                'destination_value' => '/category',
                'theme' => 'red',
            ],
            [
                'name' => 'StoreZ Demo Hero Fresh Picks',
                'eyebrow' => 'StoreZ fresh picks',
                'title' => 'Everyday Essentials, Less!',
                'description' => 'Stock up on groceries and home favorites with dependable delivery.',
                'cta_label' => 'Shop groceries',
                'destination_value' => '/search',
                'theme' => 'green',
            ],
        ];

        foreach ($banners as $sortOrder => $data) {
            $banner = Banner::firstOrCreate(
                ['name' => $data['name']],
                [
                    'placement' => 'hero',
                    'eyebrow' => $data['eyebrow'],
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'cta_label' => $data['cta_label'],
                    'destination_type' => 'url',
                    'destination_value' => $data['destination_value'],
                    'desktop_media_id' => null,
                    'mobile_media_id' => null,
                    'status' => 'published',
                    'sort_order' => $sortOrder,
                    'settings' => ['theme' => $data['theme']],
                ],
            );

            if ($banner->wasRecentlyCreated) {
                $this->publishing->invalidate('banner', 'hero');
            }
        }
    }

    private function demoTestimonials(): array
    {
        return [
            ['id' => 'demo-nusrat-jahan', 'name' => 'Nusrat Jahan', 'role' => 'Verified customer', 'rating' => 5, 'quote' => 'Great experience! Fast delivery and products were exactly as described.', 'avatar_media_id' => null, 'enabled' => true, 'sort_order' => 0],
            ['id' => 'demo-rafiq-ahmed', 'name' => 'Rafiq Ahmed', 'role' => 'Verified customer', 'rating' => 5, 'quote' => 'Love the combo deals and COD option. Very convenient and trustworthy.', 'avatar_media_id' => null, 'enabled' => true, 'sort_order' => 1],
            ['id' => 'demo-tania-rahman', 'name' => 'Tania Rahman', 'role' => 'Verified customer', 'rating' => 5, 'quote' => 'Quality products, best prices and excellent customer service. Highly recommended.', 'avatar_media_id' => null, 'enabled' => true, 'sort_order' => 2],
            ['id' => 'demo-farhan-kabir', 'name' => 'Farhan Kabir', 'role' => 'Verified customer', 'rating' => 4, 'quote' => 'The product selection is excellent and delivery updates made every order easy to follow.', 'avatar_media_id' => null, 'enabled' => true, 'sort_order' => 3],
            ['id' => 'demo-maliha-sultana', 'name' => 'Maliha Sultana', 'role' => 'Verified customer', 'rating' => 5, 'quote' => 'I found exactly what I needed at a fair price, and checkout was quick and simple.', 'avatar_media_id' => null, 'enabled' => true, 'sort_order' => 4],
        ];
    }

    private function isLegacyDemoTestimonials(?array $settings): bool
    {
        return ! isset($settings['testimonials'])
            && str_contains((string) ($settings['content_json'] ?? ''), 'Great value and fast delivery.');
    }

    private function seedAnnouncement(): void
    {
        $announcement = Announcement::firstOrCreate(
            ['internal_title' => 'StoreZ Demo Delivery Notice'],
            [
                'message' => 'Free delivery is available on selected StoreZ orders this week.',
                'style' => 'info',
                'placement' => 'top_bar',
                'link_label' => 'Shop offers',
                'link_url' => '/offers',
                'dismissible' => true,
                'priority' => 'normal',
                'status' => 'published',
            ],
        );

        if ($announcement->wasRecentlyCreated) {
            $this->publishing->invalidate('announcement', 'top_bar');
        }
    }
}
