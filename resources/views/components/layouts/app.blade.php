@props(['title' => 'StoreZ'])

@php
    $cartItems = \App\Support\StorefrontDemoData::cartItems();
    $categories = \App\Support\StorefrontDemoData::categories();
    $trustItems = \App\Support\StorefrontDemoData::trustItems();
@endphp

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
                const item = this.cart.find((cartItem) => cartItem.id === product.id);
                item ? item.quantity += quantityToAdd : this.cart.push({ ...product, quantity: quantityToAdd });
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
        @scroll.window="showScrollTop = window.scrollY > 400"
    >
        <x-store.layout.promo-bar />
        <x-store.layout.header :categories="$categories" />
        <x-store.layout.desktop-nav :categories="$categories" />

        {{ $slot }}

        <x-store.layout.trust-strip :items="$trustItems" />
        <x-store.layout.footer :categories="$categories" />
        <x-store.checkout.cart-drawer />
        <x-ui.toast position="top-center" />

        <div class="fixed bottom-5 right-5 z-40 size-12">
            <a href="https://wa.me/8801700000000?text=Hello%20StoreZ" target="_blank" rel="noopener noreferrer"
                class="absolute bottom-0 left-0 grid size-12 place-items-center rounded-full bg-[#25D366] text-white shadow-lg transition duration-300 ease-out hover:scale-105 hover:bg-[#1ebe5d]"
                :class="showScrollTop ? '-translate-y-15' : 'translate-y-0'"
                aria-label="Chat with StoreZ on WhatsApp">
                <x-ui.icon name="phone" class="size-6 !text-white" />
            </a>
            <button type="button" x-cloak x-show="showScrollTop" x-transition
                @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
                class="absolute bottom-0 left-0 grid size-12 place-items-center rounded-full bg-store-blue text-white shadow-lg transition hover:scale-105 hover:bg-store-blue-dark"
                aria-label="Scroll to top">
                <x-ui.icon name="arrow-up" class="size-6 !text-white" />
            </button>
        </div>

        @livewireScriptConfig
    </body>
</html>
