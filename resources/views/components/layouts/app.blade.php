@props(['title' => 'StoreZ'])

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" type="image/png" href="{{ asset('images/brand/favicon.png') }}">
        <title>{{ $title }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body
        x-data="{
            cartOpen: false,
            mobileMenuOpen: false,
            showScrollTop: false,
            cart: @js($cartItems),
            wishlist: @js(\App\Support\StorefrontDemoData::wishlistIds()),
            notify(content, type = 'success') {
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: { content, type, duration: 3200 }
                }));
            },
            addToCart(product, quantityToAdd = 1) {
                if (product.variantId && window.Livewire) {
                    window.Livewire.dispatch('add-to-cart', { variantId: product.variantId, quantity: quantityToAdd });
                }
                this.cartOpen = true;
                this.notify(`${product.name} added to cart`);
            },
            toggleWishlist(productId) {
                const isSaved = this.wishlist.includes(productId);
                this.wishlist = isSaved
                    ? this.wishlist.filter((id) => id !== productId)
                    : [...this.wishlist, productId];
                this.notify(isSaved ? 'Removed from wishlist' : 'Added to wishlist', isSaved ? 'info' : 'success');
            },
            changeCartQuantity(item, amount) {
                item.quantity = Math.max(1, item.quantity + amount);
            },
            removeFromCart(productId, productName) {
                this.cart = this.cart.filter((item) => item.id !== productId);
                this.notify(`${productName} removed from cart`, 'info');
            }
        }"
        x-effect="document.body.classList.toggle('overflow-hidden', cartOpen)"
        @keydown.escape.window="cartOpen = false; mobileMenuOpen = false"
        @open-cart.window="cartOpen = true"
        @cart-updated.window="cart = $event.detail.items || cart"
        @scroll.window="showScrollTop = window.scrollY > 400"
    >
        @foreach($announcements as $announcement)<x-store.layout.announcement :announcement="$announcement" />@endforeach
        <x-store.layout.promo-bar />
        <x-store.layout.header :categories="$categories" />
        <x-store.layout.desktop-nav :categories="$categories" />

        {{ $slot }}

        <x-store.layout.trust-strip :items="$trustItems" />
        <x-store.layout.footer :categories="$categories" />
        <livewire:components.store.cart-drawer />
        <x-ui.toast position="top-center" />

        <x-store.layout.floating-actions :whatsapp-number="$whatsappNumber" />

        @livewireScriptConfig
    </body>
</html>
