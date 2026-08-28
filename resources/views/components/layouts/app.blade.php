@props(['title' => null])

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ $faviconUrl ?: asset('images/brand/favicon.png') }}">
    <meta name="description" content="{{ $storeTagline ?? 'Shop smarter every day.' }}">
    <title>{{ $title ? str_replace('StoreZ', $storeName ?? 'StoreZ', $title) : $storeName ?? 'StoreZ' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body x-data="{
    cartOpen: false,
    mobileMenuOpen: false,
    showScrollTop: false,
    cart: [],
    cartLoaded: false,
    cartReturnFocus: null,
    wishlist: @js($wishlistIds ?? []),
    wishlistAuthenticated: @js($wishlistAuthenticated ?? false),
    wishlistStorageKey: @js($wishlistStorageKey ?? 'storez-wishlist-guest'),
    wishlistSyncUrl: @js(route('store.wishlist.sync')),
    wishlistItemsUrl: @js(url('/wishlist/items')),
    init() {
        this.initializeWishlist();
        this.$nextTick(() => window.Livewire?.dispatch('cart-initialized'));
    },
    openCart(trigger = document.activeElement) {
        if (!this.cartOpen) {
            this.cartReturnFocus = trigger && typeof trigger.focus === 'function' ?
                trigger :
                this.$refs.cartTrigger;
        }

        this.cartOpen = true;

        if (!this.cartLoaded) {
            window.Livewire?.dispatch('cart-opened');
        }

        this.$nextTick(() => this.$refs.cartClose?.focus());
    },
    closeCart() {
        if (!this.cartOpen) {
            return;
        }

        this.cartOpen = false;
        this.restoreCartFocus();
    },
    restoreCartFocus() {
        const focusTarget = this.cartReturnFocus;
        this.cartReturnFocus = null;

        this.$nextTick(() => {
            if (focusTarget && document.contains(focusTarget) && typeof focusTarget.focus === 'function') {
                focusTarget.focus();
            }
        });
    },
    trapCartFocus(event) {
        if (event.key !== 'Tab') {
            return;
        }

        const focusableElements = [...(this.$refs.cartPanel?.querySelectorAll(`button:not([disabled]), a[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex='-1'])`) || [])]
            .filter((element) => element.getClientRects().length > 0);

        if (focusableElements.length === 0) {
            event.preventDefault();
            this.$refs.cartClose?.focus();

            return;
        }

        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];

        if (event.shiftKey && document.activeElement === firstElement) {
            event.preventDefault();
            lastElement.focus();
        } else if (!event.shiftKey && document.activeElement === lastElement) {
            event.preventDefault();
            firstElement.focus();
        }
    },
    normalizeWishlist(ids) {
        return [...new Set((Array.isArray(ids) ? ids : []).map((id) => Number(id)).filter((id) => Number.isInteger(id) && id > 0))];
    },
    storeWishlistLocally() {
        try {
            window.localStorage.setItem(this.wishlistStorageKey, JSON.stringify(this.wishlist));
        } catch (error) {
            // Local storage may be unavailable in privacy-restricted browsers.
        }
    },
    requestHeaders() {
        return {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
            'X-Requested-With': 'XMLHttpRequest',
        };
    },
    async requestWishlist(url, options = {}) {
        const response = await fetch(url, {
            ...options,
            headers: { ...this.requestHeaders(), ...(options.headers || {}) },
        });
        const payload = await response.json().catch(() => ({}));
        if (!response.ok) {
            throw new Error(payload.message || 'Unable to update wishlist.');
        }
        return payload;
    },
    initializeWishlist() {
        const serverIds = this.normalizeWishlist(this.wishlist);
        if (this.wishlistAuthenticated) {
            try {
                window.localStorage.removeItem('storez-wishlist-guest');
            } catch (error) {
                // Local storage may be unavailable in privacy-restricted browsers.
            }
            this.wishlist = serverIds;
            this.storeWishlistLocally();
            return;
        }

        let storedIds = null;
        try {
            const stored = window.localStorage.getItem(this.wishlistStorageKey);
            storedIds = stored === null ? null : this.normalizeWishlist(JSON.parse(stored));
        } catch (error) {
            storedIds = null;
        }

        this.wishlist = storedIds ?? serverIds;
        this.storeWishlistLocally();
        this.syncGuestWishlist().catch(() => {});
    },
    async syncGuestWishlist() {
        if (this.wishlistAuthenticated) {
            return this.wishlist;
        }

        const payload = await this.requestWishlist(this.wishlistSyncUrl, {
            method: 'POST',
            body: JSON.stringify({ ids: this.wishlist }),
        });
        this.wishlist = this.normalizeWishlist(payload.ids);
        this.storeWishlistLocally();
        return this.wishlist;
    },
    notify(content, type = 'success') {
        window.dispatchEvent(new CustomEvent('notify', {
            detail: { content, type, duration: 3200 }
        }));
    },
    addToCart(product, quantityToAdd = 1) {
        if (product.variantId && window.Livewire) {
            window.Livewire.dispatch('add-to-cart', { variantId: product.variantId, quantity: quantityToAdd });
        }
        this.openCart();
        this.notify(`${product.name} added to cart`);
    },
    async toggleWishlist(productId) {
        const id = Number(productId);
        const isSaved = this.wishlist.includes(id);
        const previous = [...this.wishlist];
        this.wishlist = isSaved ?
            this.wishlist.filter((wishlistId) => wishlistId !== id) :
            [...this.wishlist, id];
        this.storeWishlistLocally();

        try {
            const payload = await this.requestWishlist(`${this.wishlistItemsUrl}/${id}`, {
                method: isSaved ? 'DELETE' : 'POST',
            });
            this.wishlist = this.normalizeWishlist(payload.ids);
            this.storeWishlistLocally();
            this.notify(isSaved ? 'Removed from wishlist' : 'Added to wishlist', isSaved ? 'info' : 'success');
        } catch (error) {
            this.wishlist = previous;
            this.storeWishlistLocally();
            this.notify(error.message || 'Unable to update wishlist.', 'error');
        }
    },
    async clearWishlist() {
        const previous = [...this.wishlist];
        this.wishlist = [];
        this.storeWishlistLocally();

        try {
            const payload = await this.requestWishlist(this.wishlistItemsUrl, { method: 'DELETE' });
            this.wishlist = this.normalizeWishlist(payload.ids);
            this.storeWishlistLocally();
            this.notify('Wishlist cleared', 'info');
        } catch (error) {
            this.wishlist = previous;
            this.storeWishlistLocally();
            this.notify(error.message || 'Unable to clear wishlist.', 'error');
        }
    },
    changeCartQuantity(item, amount) {
        item.quantity = Math.max(1, item.quantity + amount);
    },
    removeFromCart(productId, productName) {
        this.cart = this.cart.filter((item) => item.id !== productId);
        this.notify(`${productName} removed from cart`, 'info');
    }
}" x-effect="document.body.classList.toggle('overflow-hidden', cartOpen)"
    @keydown.escape.window="if (cartOpen) closeCart(); mobileMenuOpen = false" @open-cart.window="openCart()"
    @cart-updated.window="cart = $event.detail.items || cart; cartLoaded = true"
    x-on:livewire:navigating.window="closeCart(); mobileMenuOpen = false"
    @scroll.window="showScrollTop = window.scrollY > 400">
    <x-store.layout.announcements :announcements="$announcementsByPlacement->get('top_bar', collect())" placement="top_bar" />
    {{-- <x-store.layout.promo-bar /> --}}
    <x-store.layout.header :categories="$categories" />
    <x-store.layout.desktop-nav :categories="$categories" />

    <x-store.layout.announcements :announcements="$announcementsByPlacement->get('storefront', collect())" placement="storefront" />
    @if(request()->routeIs('store.checkout'))
        <x-store.layout.announcements :announcements="$announcementsByPlacement->get('checkout', collect())" placement="checkout" />
    @elseif(request()->routeIs('account.*'))
        <x-store.layout.announcements :announcements="$announcementsByPlacement->get('account', collect())" placement="account" />
    @endif

    {{ $slot }}

    <x-store.layout.trust-strip :items="$trustItems" />
    <x-store.layout.footer :categories="$categories" />
    <livewire:components.store.cart-drawer />
    <x-ui.toast position="top-center" />

    <x-store.layout.floating-actions :whatsapp-number="$whatsappNumber" />

    @livewireScriptConfig
</body>

</html>
