<?php

namespace App\Support;

/**
 * Temporary normalized storefront data for the frontend-only phase.
 *
 * Replace this provider with application services or repositories once the
 * commerce backend is introduced; component input shapes should remain stable.
 */
final class StorefrontDemoData
{
    private static function placeholderImage(): string
    {
        return asset('images/placeholders/no-image.svg');
    }

    public static function categories(): array
    {
        return [
            ['id' => 'groceries', 'name' => 'Groceries', 'slug' => 'groceries'],
            ['id' => 'electronics', 'name' => 'Electronics', 'slug' => 'electronics'],
            ['id' => 'fashion', 'name' => 'Fashion', 'slug' => 'fashion'],
            ['id' => 'home-living', 'name' => 'Home & Living', 'slug' => 'home-living'],
            ['id' => 'beauty', 'name' => 'Beauty & Care', 'slug' => 'beauty'],
            ['id' => 'baby-toys', 'name' => 'Baby & Toys', 'slug' => 'baby-toys'],
        ];
    }

    public static function homepageCategories(): array
    {
        return [
            ['name' => 'Grocery & Essentials', 'icon' => 'shopping-bag'], ['name' => 'Fresh Food', 'icon' => 'cake'],
            ['name' => 'Fashion', 'icon' => 'sparkles'], ['name' => 'Electronics', 'icon' => 'device-phone-mobile'],
            ['name' => 'Beauty & Personal Care', 'icon' => 'beaker'], ['name' => 'Home & Living', 'icon' => 'home'],
            ['name' => 'Baby Care', 'icon' => 'gift'], ['name' => 'Sports & Outdoors', 'icon' => 'trophy'],
        ];
    }

    public static function combos(): array
    {
        return [
            ['name' => 'Family Grocery Pack', 'price' => 2199, 'save' => 561], ['name' => 'Home Essential Pack', 'price' => 1599, 'save' => 481],
            ['name' => 'Beauty Care Pack', 'price' => 1199, 'save' => 441], ['name' => 'Baby Care Pack', 'price' => 1749, 'save' => 591],
        ];
    }

    public static function products(): array
    {
        return [
            [
                'id' => 1, 'slug' => 'wireless-noise-cancelling-headphones',
                'name' => 'Wireless Noise Cancelling Headphones', 'brand' => 'SoundMax',
                'image' => self::placeholderImage(), 'price' => 4290,
                'oldPrice' => 4990, 'discount' => 14, 'rating' => 4.8,
                'reviews' => 126, 'inStock' => true,
            ],
            [
                'id' => 2, 'slug' => 'smart-led-bulb-pack',
                'name' => 'Smart LED Bulb, Pack of 2', 'brand' => 'BrightHome',
                'image' => self::placeholderImage(), 'price' => 890,
                'oldPrice' => 1090, 'discount' => 18, 'rating' => 4.6,
                'reviews' => 88, 'inStock' => true,
            ],
            [
                'id' => 3, 'slug' => 'daily-essential-basket',
                'name' => 'Daily Essential Grocery Basket', 'brand' => 'StoreZ Fresh',
                'image' => self::placeholderImage(), 'price' => 1290,
                'oldPrice' => null, 'discount' => null, 'rating' => 4.7,
                'reviews' => 241, 'inStock' => true,
            ],
            ['id' => 4, 'slug' => 'premium-basmati-rice', 'name' => 'Premium Basmati Rice 5kg', 'brand' => 'Teer', 'image' => self::placeholderImage(), 'price' => 950, 'oldPrice' => 1180, 'discount' => 19, 'rating' => 4.6, 'reviews' => 2100, 'inStock' => true, 'highlights' => ['Extra long grain premium quality', 'Naturally aromatic, fluffy and non-sticky', 'Perfect for biryani, pulao, fried rice and daily meals', 'Carefully packed for retail freshness'], 'options' => [['label' => '1kg', 'value' => '1kg', 'price' => 210], ['label' => '5kg', 'value' => '5kg', 'price' => 950], ['label' => '10kg', 'value' => '10kg', 'price' => 1780]]],
            ['id' => 5, 'slug' => 'fresh-soyabean-oil', 'name' => 'Fresh Soyabean Oil 2L', 'brand' => 'Fresh', 'image' => self::placeholderImage(), 'price' => 620, 'oldPrice' => 730, 'discount' => 15, 'rating' => 4.7, 'reviews' => 980, 'inStock' => true],
            ['id' => 6, 'slug' => 'orix-crystal-detergent', 'name' => 'Orix Crystal Detergent Powder 2kg', 'brand' => 'Orix', 'image' => self::placeholderImage(), 'price' => 520, 'oldPrice' => 695, 'discount' => 25, 'rating' => 4.6, 'reviews' => 850, 'inStock' => true],
            ['id' => 7, 'slug' => 'redmi-note-13', 'name' => 'Redmi Note 13 (8/128GB)', 'brand' => 'Xiaomi', 'image' => self::placeholderImage(), 'price' => 18999, 'oldPrice' => 24999, 'discount' => 24, 'rating' => 4.6, 'reviews' => 456, 'inStock' => true, 'highlights' => ['6.67-inch AMOLED display', '8GB RAM with 128GB storage', 'All-day battery with fast charging', 'High-resolution camera system'], 'options' => [['label' => '8/128GB', 'value' => '8/128GB', 'price' => 18999], ['label' => '8/256GB', 'value' => '8/256GB', 'price' => 21999]]],
            ['id' => 8, 'slug' => 'nivea-soft-moisturizer', 'name' => 'Nivea Soft Light Moisturizer 300ml', 'brand' => 'Nivea', 'image' => self::placeholderImage(), 'price' => 390, 'oldPrice' => 475, 'discount' => 18, 'rating' => 4.6, 'reviews' => 930, 'inStock' => true],
            ['id' => 9, 'slug' => 'miyako-electric-kettle', 'name' => 'Miyako Electric Kettle 1.8L', 'brand' => 'Miyako', 'image' => self::placeholderImage(), 'price' => 850, 'oldPrice' => 1090, 'discount' => 22, 'rating' => 4.5, 'reviews' => 680, 'inStock' => true],
            ['id' => 11, 'slug' => 'samsung-galaxy-a15', 'name' => 'Samsung Galaxy A15', 'brand' => 'Samsung', 'image' => self::placeholderImage(), 'price' => 17499, 'oldPrice' => 19999, 'discount' => 12, 'rating' => 4.6, 'reviews' => 1100, 'inStock' => true],
            ['id' => 12, 'slug' => 'walton-nonstick-cookware', 'name' => 'Walton Non-Stick Cookware Set', 'brand' => 'Walton', 'image' => self::placeholderImage(), 'price' => 3450, 'oldPrice' => 4200, 'discount' => 18, 'rating' => 4.6, 'reviews' => 670, 'inStock' => true],
            ['id' => 13, 'slug' => 'apex-casual-shoes', 'name' => 'Apex Casual Shoes', 'brand' => 'Apex', 'image' => self::placeholderImage(), 'price' => 1799, 'oldPrice' => 2390, 'discount' => 25, 'rating' => 4.5, 'reviews' => 820, 'inStock' => true],
            ['id' => 14, 'slug' => 'fresh-noodles', 'name' => 'Instant Noodles Family Pack', 'brand' => 'Fresh', 'image' => self::placeholderImage(), 'price' => 75, 'oldPrice' => 90, 'discount' => 17, 'rating' => 4.6, 'reviews' => 410, 'inStock' => true],
            ['id' => 15, 'slug' => 'decorative-table-lamp', 'name' => 'Decorative Table Lamp', 'brand' => 'BrightHome', 'image' => self::placeholderImage(), 'price' => 890, 'oldPrice' => 1200, 'discount' => 26, 'rating' => 4.7, 'reviews' => 320, 'inStock' => true],
        ];
    }

    public static function brands(): array
    {
        return collect(self::products())->pluck('brand')->unique()->map(fn (string $name) => [
            'id' => str($name)->slug(), 'name' => $name, 'slug' => str($name)->slug(),
        ])->values()->all();
    }

    public static function promotions(): array
    {
        return [
            ['id' => 'welcome', 'title' => 'Welcome to StoreZ', 'subtitle' => 'Everyday essentials, delivered with ease.', 'cta' => 'Shop offers'],
        ];
    }

    public static function profile(): array
    {
        return ['name' => 'Alex Morgan', 'email' => 'alex@example.test', 'phone' => '+880 1700-000000'];
    }

    public static function addresses(): array
    {
        return [[
            'id' => 1, 'label' => 'Home', 'recipient' => 'Alex Morgan',
            'line1' => 'House 12, Road 7', 'area' => 'Dhanmondi, Dhaka', 'default' => true,
        ]];
    }

    public static function cartItems(): array
    {
        return array_map(
            fn (array $product) => $product + ['quantity' => 1],
            array_slice(self::products(), 0, 2),
        );
    }

    public static function wishlistIds(): array
    {
        return [1, 3];
    }

    public static function orders(): array
    {
        return [[
            'id' => 'SZ-100248', 'status' => 'out_for_delivery',
            'placedAt' => '2026-08-20', 'total' => 5180, 'items' => self::cartItems(),
        ]];
    }

    public static function trackingTimeline(): array
    {
        return [
            ['key' => 'placed', 'label' => 'Order placed', 'completed' => true],
            ['key' => 'confirmed', 'label' => 'Order confirmed', 'completed' => true],
            ['key' => 'packed', 'label' => 'Packed', 'completed' => true],
            ['key' => 'shipped', 'label' => 'Shipped', 'completed' => true],
            ['key' => 'out_for_delivery', 'label' => 'Out for delivery', 'active' => true],
            ['key' => 'delivered', 'label' => 'Delivered', 'completed' => false],
        ];
    }

    public static function paymentMethods(): array
    {
        return [
            ['id' => 'cod', 'name' => 'Cash on Delivery'],
            ['id' => 'bkash', 'name' => 'bKash'],
            ['id' => 'card', 'name' => 'Credit or Debit Card'],
        ];
    }

    public static function trustItems(): array
    {
        return [
            ['title' => 'Fast Delivery', 'description' => 'Quick delivery to your doorstep.'],
            ['title' => 'Easy Returns', 'description' => 'Simple returns when you need them.'],
            ['title' => 'Secure Payments', 'description' => 'Your payment information stays protected.'],
            ['title' => 'Cash on Delivery', 'description' => 'Pay when your order arrives.'],
        ];
    }
}
