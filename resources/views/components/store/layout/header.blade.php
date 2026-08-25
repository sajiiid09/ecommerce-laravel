@props(['categories' => []])

<header class="sticky top-0 z-50 border-b border-store-border bg-white">
    <x-store.ui.container>
        <div class="flex h-18 items-center gap-3 py-3 lg:h-21 lg:gap-5">
            <button type="button"
                class="inline-flex size-11 items-center justify-center rounded-control text-store-ink hover:bg-store-soft lg:hidden"
                @click="mobileMenuOpen = !mobileMenuOpen" :aria-expanded="mobileMenuOpen.toString()"
                aria-controls="mobile-navigation">
                <x-ui.icon name="bars-3" class="size-6 !text-store-ink" />
                <span class="sr-only">Toggle navigation</span>
            </button>

            <a href="{{ route('store.home') }}" wire:navigate class="shrink-0" aria-label="StoreZ home">
                <img src="{{ $configuredLogo ?: asset('images/brand/storez-logo.png') }}" alt="StoreZ" class="h-auto w-25 sm:w-30">
            </a>

            <button type="button"
                class="hidden shrink-0 items-center gap-2 text-left text-xs text-store-muted lg:flex">
                <x-ui.icon name="map-pin" class="size-5 !text-store-ink" />
                <span><span class="block text-[10px] leading-none">Deliver to</span><span
                        class="mt-1 block font-semibold text-store-ink">Dhaka, 1205</span></span>
                <x-ui.icon name="chevron-down" class="size-3 !text-store-ink" />
            </button>

            <form class="hidden min-w-0 flex-1 md:flex" role="search" action="{{ route('store.search') }}"
                method="get">
                <label for="global-search" class="sr-only">Search products</label>
                <div
                    class="flex h-11 w-full overflow-hidden rounded-control border border-store-border bg-white transition focus-within:border-store-blue focus-within:ring-2 focus-within:ring-store-blue/10">
                    <input id="global-search" name="q" type="search"
                        placeholder="Search for products, brands and more"
                        class="min-w-0 flex-1 border-0 px-4 text-sm outline-none placeholder:text-store-placeholder">
                    <select name="category" aria-label="Product category"
                        class="hidden min-w-44 border-x border-store-border bg-store-soft px-3 text-xs text-store-text outline-none xl:block">
                        <option value="">All Categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category['slug'] }}">{{ $category['name'] }}</option>
                        @endforeach
                    </select>
                    <button type="submit"
                        class="grid w-12 shrink-0 place-items-center bg-store-blue text-white transition hover:bg-store-blue-dark"
                        aria-label="Search">
                        <x-ui.icon name="magnifying-glass" class="size-5 !text-white" />
                    </button>
                </div>
            </form>

            <nav class="ml-auto flex items-center gap-2 sm:gap-4" aria-label="Account shortcuts">
                <a href="{{ route('account.dashboard') }}" wire:navigate
                    class="hidden h-11 items-center gap-2 rounded-control px-2 text-xs text-store-muted hover:bg-store-soft hover:text-store-blue lg:flex"><x-ui.icon
                        name="user" class="size-6 shrink-0 !text-store-ink" /><span><span
                            class="block font-semibold text-store-ink">Account</span>
                        {{-- <span>Sign in /
                            Register</span></span> --}}
                </a>
                <a href="{{ route('store.wishlist') }}" wire:navigate
                    class="inline-flex h-11 min-w-11 items-center justify-center gap-2 rounded-control px-2 text-store-ink hover:bg-store-soft"
                    aria-label="Wishlist"><x-ui.icon name="heart"
                        class="size-6 shrink-0 !text-store-ink" aria-hidden="true" /><span
                        class="hidden text-xs xl:inline">Wishlist</span></a>
                <button type="button"
                    class="relative inline-flex size-11 items-center justify-center rounded-control text-store-ink hover:bg-store-soft"
                    @click="cartOpen = true" aria-controls="cart-drawer">
                    <x-ui.icon name="shopping-cart" class="size-6 !text-store-ink" />
                    <span
                        class="absolute right-0 top-0 grid size-4 place-items-center rounded-full bg-store-red text-[10px] font-bold text-white"
                        x-text="cart.reduce((total, item) => total + item.quantity, 0)"></span>
                    <span class="sr-only">Open shopping cart</span>
                </button>
            </nav>
        </div>

        <form class="pb-3 md:hidden" role="search" action="{{ route('store.search') }}" method="get">
            <label for="global-search-mobile" class="sr-only">Search products</label>
            <div
                class="flex h-11 overflow-hidden rounded-control border border-store-border focus-within:border-store-blue focus-within:ring-2 focus-within:ring-store-blue/10">
                <input id="global-search-mobile" name="q" type="search"
                    placeholder="Search products, brands and more"
                    class="min-w-0 flex-1 border-0 px-3 text-sm outline-none placeholder:text-store-placeholder">
                <button type="submit" class="grid w-12 place-items-center bg-store-blue text-white"
                    aria-label="Search"><x-ui.icon name="magnifying-glass" class="size-5 !text-white" /></button>
            </div>
        </form>
    </x-store.ui.container>

    <div id="mobile-navigation" x-cloak x-show="mobileMenuOpen" x-transition.opacity
        class="absolute inset-x-0 top-full border-y border-store-border bg-white shadow-store-soft lg:hidden">
        <x-store.ui.container class="py-3">
            <a href="#categories"
                class="mb-2 flex items-center gap-2 rounded-control bg-store-blue px-3 py-3 text-sm font-semibold text-white"><x-ui.icon
                    name="squares-2x2" class="size-5 !text-white" />All Categories</a>
            <div class="grid grid-cols-2 gap-1">
                @foreach ($categories as $category)
                    <a href="#{{ $category['slug'] }}"
                        class="rounded-control px-3 py-2 text-sm font-medium text-store-text hover:bg-store-soft">{{ $category['name'] }}</a>
                @endforeach
                <a href="#offers"
                    class="rounded-control bg-store-promo-soft px-3 py-2 text-sm font-semibold text-store-red">Offers
                    Zone</a>
            </div>
        </x-store.ui.container>
    </div>
</header>
