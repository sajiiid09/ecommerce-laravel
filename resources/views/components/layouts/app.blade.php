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
        <title>{{ $title }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body
        x-data="{
            cartOpen: false,
            mobileMenuOpen: false,
            cart: @js($cartItems),
            wishlist: @js(\App\Support\StorefrontDemoData::wishlistIds()),
            addToCart(product) {
                const item = this.cart.find((cartItem) => cartItem.id === product.id);
                item ? item.quantity++ : this.cart.push({ ...product, quantity: 1 });
                this.cartOpen = true;
            },
            toggleWishlist(productId) {
                this.wishlist = this.wishlist.includes(productId)
                    ? this.wishlist.filter((id) => id !== productId)
                    : [...this.wishlist, productId];
            }
        }"
        x-effect="document.body.classList.toggle('overflow-hidden', cartOpen)"
        @keydown.escape.window="cartOpen = false; mobileMenuOpen = false"
    >
        <x-store.layout.promo-bar />
        <x-store.layout.header :categories="$categories" />
        <x-store.layout.desktop-nav :categories="$categories" />

        {{ $slot }}

        <x-store.layout.trust-strip :items="$trustItems" />
        <x-store.layout.footer :categories="$categories" />
        <x-store.checkout.cart-drawer />

        @livewireScriptConfig
    </body>
</html>
