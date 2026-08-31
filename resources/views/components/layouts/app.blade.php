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
    cartAddPending: false,
    pendingCartProductName: null,
    cartReturnFocus: null,
    wishlist: @js($wishlistIds ?? []),
    wishlistAuthenticated: @js($wishlistAuthenticated ?? false),
    wishlistStorageKey: @js($wishlistStorageKey ?? 'storez-wishlist-guest'),
    wishlistSyncUrl: @js(route('store.wishlist.sync')),
    wishlistItemsUrl: @js(url('/wishlist/items')),
    init() {
        this.initializeWishlist();
    },
    initializeCart() {
        this.$nextTick(() => window.Livewire?.dispatch('cart-initialized'));
    },
    openCart(trigger = document.activeElement) {
        if (!this.cartOpen) {
            this.cartReturnFocus = trigger && typeof trigger.focus === 'function' ?
                trigger :
                this.$refs.cartTrigger;
        }

        this.cartOpen = true;

        if (!this.cartLoaded && !this.cartAddPending) {
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
        if (this.cartAddPending) {
            return;
        }

        if (!product.variantId || !window.Livewire) {
            this.openCart();
            this.notify(`${product.name} added to cart`);

            return;
        }

        this.cartAddPending = true;
        this.pendingCartProductName = product.name;
        this.notify(`Adding ${product.name} to cart...`, 'info');
        this.openCart();
        window.Livewire.dispatch('add-to-cart', { variantId: product.variantId, quantity: quantityToAdd });
    },
    completeCartAdd() {
        const productName = this.pendingCartProductName || 'Product';
        this.cartAddPending = false;
        this.pendingCartProductName = null;
        this.notify(`${productName} added to cart`);
    },
    failCartAdd(message) {
        this.cartAddPending = false;
        this.pendingCartProductName = null;
        this.notify(message || 'Unable to add this product to your cart.', 'error');
    },
    quickAddProduct: null,
    quickAddQuantity: 1,
    quickAddSelectedVariantId: null,
    quickAddSelectedOptions: {},
    quickAddSelectedImage: null,
    openProductQuickAdd(product) {
        this.quickAddProduct = product;
        this.quickAddQuantity = 1;
        this.quickAddSelectedVariantId = product.variantId || product.variants?.[0]?.id || null;
        const variant = product.variants?.find((item) => item.id === this.quickAddSelectedVariantId) || product.variants?.[0];
        this.quickAddSelectedOptions = Object.fromEntries((variant?.optionValues || []).map((optionValue) => [optionValue.option, optionValue.value]));
        this.quickAddSelectedImage = variant?.gallery?.[0] || @js(asset('images/placeholders/no-image.svg'));
        window.dispatchEvent(new CustomEvent('open-modal', { detail: { id: 'product-quick-add' } }));
    },
    closeProductQuickAdd() {
        window.dispatchEvent(new CustomEvent('close-modal', { detail: { id: 'product-quick-add' } }));
        this.quickAddProduct = null;
        this.quickAddSelectedOptions = {};
        this.quickAddSelectedVariantId = null;
        this.quickAddSelectedImage = null;
    },
    quickAddMatches(variant, selectedOptions) {
        const optionNames = Object.keys(selectedOptions);
        return variant.optionValues?.length === optionNames.length && optionNames.every((optionName) => variant.optionValues.some((optionValue) => optionValue.option === optionName && optionValue.value === selectedOptions[optionName]));
    },
    get quickAddVariant() {
        const variants = this.quickAddProduct?.variants || [];
        const selected = variants.find((variant) => variant.id === this.quickAddSelectedVariantId);
        const matching = variants.find((variant) => this.quickAddMatches(variant, this.quickAddSelectedOptions));
        return matching || selected || variants[0] || { id: null, sku: null, price: 0, compareAtPrice: null, stock: 0, available: false, optionValues: [], gallery: [] };
    },
    get quickAddGallery() {
        return this.quickAddVariant.gallery?.length ? this.quickAddVariant.gallery : [@js(asset('images/placeholders/no-image.svg'))];
    },
    quickAddOptionValueAvailable(name, value) {
        if (!this.quickAddProduct) {
            return false;
        }

        const selectedOptions = { ...this.quickAddSelectedOptions, [name]: value };

        return (this.quickAddProduct.variants || []).some((variant) => variant.available && this.quickAddMatches(variant, selectedOptions));
    },
    quickAddSelectOption(name, value) {
        if (!this.quickAddOptionValueAvailable(name, value)) {
            return;
        }

        this.quickAddSelectedOptions[name] = value;
        const matching = (this.quickAddProduct?.variants || []).find((variant) => this.quickAddMatches(variant, this.quickAddSelectedOptions));
        this.quickAddSelectedVariantId = matching?.id || this.quickAddSelectedVariantId;
        this.quickAddSelectedImage = this.quickAddGallery[0];
    },
    quickAddFormatMoney(value) {
        return '\u09F3' + new Intl.NumberFormat().format(Math.round((value || 0) / 100));
    },
    quickAddAddToCart() {
        const variant = this.quickAddVariant;

        if (!variant.id || !variant.available || this.quickAddQuantity < 1) {
            return;
        }

        this.addToCart({ ...this.quickAddProduct, variantId: variant.id }, this.quickAddQuantity);
        this.closeProductQuickAdd();
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
    @cart-item-added.window="completeCartAdd()"
    @cart-add-failed.window="failCartAdd($event.detail.message)"
    x-on:livewire:navigated.window="initializeCart()"
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

    @if (request()->routeIs('store.home', 'store.category', 'store.search', 'store.offers', 'store.brand', 'store.wishlist', 'store.product'))
    <x-ui.modal
        id="product-quick-add"
        width="3xl"
        heading="Quick add to cart"
        description="Choose an option before adding this product to your cart."
        @modal-closed.window="if ($event.detail?.id === 'product-quick-add') { quickAddProduct = null; quickAddSelectedOptions = {}; quickAddSelectedVariantId = null; quickAddSelectedImage = null; }"
    >
        <div x-show="quickAddProduct" x-cloak class="space-y-5">
            <div class="grid gap-6 md:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
                <div>
                    <div class="flex aspect-square items-center justify-center rounded-card border border-store-border bg-white p-5">
                        <img :src="quickAddSelectedImage || quickAddGallery[0]" src="{{ asset('images/placeholders/no-image.svg') }}" :alt="quickAddProduct?.name || 'Product image'" class="size-full object-contain">
                    </div>
                    <div class="mt-3 flex gap-2 overflow-x-auto">
                        <template x-for="(image, index) in quickAddGallery" :key="`${image}-${index}`">
                            <button type="button" class="size-16 shrink-0 rounded-control border bg-white p-1.5" :class="quickAddSelectedImage === image ? 'border-2 border-store-blue' : 'border-store-border'" @click="quickAddSelectedImage = image" :aria-label="`Show image ${index + 1}`">
                                <img :src="image" :alt="quickAddProduct?.name || 'Product image'" class="size-full object-contain">
                            </button>
                        </template>
                    </div>
                </div>

                <div>
                    <p class="text-sm font-semibold text-store-blue" x-text="quickAddProduct?.brand || 'StoreZ'"></p>
                    <h3 class="mt-1 text-xl font-extrabold text-store-ink" x-text="quickAddProduct?.name"></h3>
                    <p class="mt-3 text-sm leading-6 text-store-text" x-text="quickAddProduct?.shortDescription || 'A dependable product selected for everyday value.'"></p>

                    <div class="mt-3 flex items-center gap-2 text-xs" aria-label="Product rating">
                        <x-ui.icon name="star" variant="solid" class="size-4 !text-store-warning" />
                        <span class="font-semibold text-store-text" x-text="Number(quickAddProduct?.rating || 0).toFixed(1)"></span>
                        <span class="text-store-muted" x-text="`(${quickAddProduct?.reviews || 0})`"></span>
                    </div>

                    <div class="mt-4 flex flex-wrap items-baseline gap-2">
                        <span class="text-2xl font-black text-store-red" x-text="quickAddFormatMoney(quickAddVariant.price)"></span>
                        <span x-show="quickAddVariant.compareAtPrice > quickAddVariant.price" x-text="quickAddFormatMoney(quickAddVariant.compareAtPrice)" class="text-sm text-store-muted line-through"></span>
                    </div>
                    <p class="mt-2 text-sm text-store-muted">SKU: <span x-text="quickAddVariant.sku || '—'"></span></p>
                    <p class="mt-2 text-sm font-semibold" :class="quickAddVariant.available ? 'text-store-success' : 'text-store-error'">
                        <span x-text="quickAddVariant.available ? (quickAddVariant.stock > 0 ? `In Stock (${quickAddVariant.stock})` : 'In Stock') : 'Out of Stock'"></span>
                    </p>

                    <div class="mt-5 space-y-4">
                        <template x-for="option in (quickAddProduct?.options || [])" :key="option.name">
                            <div>
                                <p class="text-sm font-semibold text-store-ink"><span x-text="option.label"></span>: <span class="ml-1 font-normal text-store-muted" x-text="quickAddSelectedOptions[option.name] || 'Choose'" ></span></p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <template x-for="value in option.values" :key="`${option.name}-${value.value}`">
                                        <button type="button" class="rounded-full border px-3 py-1.5 text-sm" :class="{ 'border-store-blue bg-store-blue-soft font-bold text-store-blue': quickAddSelectedOptions[option.name] === value.value, 'border-store-border': quickAddSelectedOptions[option.name] !== value.value, 'cursor-not-allowed opacity-40': !quickAddOptionValueAvailable(option.name, value.value) }" :disabled="!quickAddOptionValueAvailable(option.name, value.value)" @click="quickAddSelectOption(option.name, value.value)" x-text="value.label"></button>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="mt-5 flex flex-wrap items-center gap-3">
                        <span class="text-sm font-semibold text-store-ink">Quantity:</span>
                        <div class="inline-flex h-10 items-center rounded-control border border-store-border bg-white">
                            <button type="button" class="grid size-9 place-items-center text-store-ink hover:bg-store-soft" @click="quickAddQuantity = Math.max(1, quickAddQuantity - 1)" aria-label="Decrease quantity"><x-ui.icon name="minus" class="size-4 !text-current" /></button>
                            <output class="w-8 text-center text-sm font-bold text-store-ink" x-text="quickAddQuantity"></output>
                            <button type="button" class="grid size-9 place-items-center text-store-ink hover:bg-store-soft" @click="quickAddQuantity++" aria-label="Increase quantity"><x-ui.icon name="plus" class="size-4 !text-current" /></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-store-border pt-4">
                <button type="button" class="rounded-control border border-store-border px-4 py-2.5 text-sm font-semibold text-store-text hover:bg-store-soft" @click="closeProductQuickAdd()">Cancel</button>
                <button type="button" class="inline-flex items-center justify-center gap-2 rounded-control bg-store-blue px-5 py-2.5 text-sm font-bold text-white disabled:cursor-not-allowed disabled:opacity-50" :disabled="cartAddPending || !quickAddVariant.id || !quickAddVariant.available || quickAddQuantity < 1" @click="quickAddAddToCart()"><x-ui.icon name="shopping-cart" class="size-4 !text-white" />Add to Cart</button>
            </div>
        </div>
    </x-ui.modal>
    @endif

    <x-store.layout.floating-actions :whatsapp-number="$whatsappNumber" />

    @livewireScriptConfig
</body>

</html>
