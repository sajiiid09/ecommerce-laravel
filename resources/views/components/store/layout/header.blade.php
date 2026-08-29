@props(['categories' => []])

<header class="{{ ($headerSticky ?? true) ? 'sticky top-0 z-50' : 'relative' }} border-b border-store-border bg-white">
    <x-store.ui.container>
        <div class="flex h-18 items-center gap-3 py-3 lg:h-21 lg:gap-5">
            <button type="button"
                class="inline-flex size-11 items-center justify-center rounded-control text-store-ink hover:bg-store-soft lg:hidden"
                @click="mobileMenuOpen = !mobileMenuOpen" :aria-expanded="mobileMenuOpen.toString()"
                aria-controls="mobile-navigation">
                <x-ui.icon name="bars-3" class="size-6 !text-store-ink" />
                <span class="sr-only">Toggle navigation</span>
            </button>

            <a href="{{ route('store.home') }}" wire:navigate class="shrink-0" aria-label="{{ $storeName }} home">
                <img src="{{ $configuredLogo ?: asset('images/brand/storez-logo.png') }}" alt="{{ $storeName }}" loading="eager" fetchpriority="high" decoding="async" class="h-auto w-25 sm:w-30">
            </a>

            <button type="button"
                class="hidden shrink-0 items-center gap-2 text-left text-xs text-store-muted lg:flex">
                <x-ui.icon name="map-pin" class="size-5 !text-store-ink" />
                <span><span class="block text-[10px] leading-none">Deliver to</span><span
                        class="mt-1 block font-semibold text-store-ink">{{ $headerSupportText ?: 'Dhaka, 1205' }}</span></span>
                <x-ui.icon name="chevron-down" class="size-3 !text-store-ink" />
            </button>

            @if($headerShowSearch ?? true)
                <livewire:components.store.header-search :categories="$categories" mode="desktop" />
            @endif

            <nav class="ml-auto flex items-center gap-2 sm:gap-4" aria-label="Account shortcuts">
                @auth
                    <div class="relative" x-data="{ accountMenuOpen: false }" @keydown.escape.window="accountMenuOpen = false">
                        <button type="button" class="flex h-11 items-center gap-1 rounded-control px-1 text-left text-xs text-store-muted hover:bg-store-soft hover:text-store-blue sm:gap-2 sm:px-2" @click="accountMenuOpen = !accountMenuOpen" :aria-expanded="accountMenuOpen.toString()" aria-controls="store-account-menu">
                            <x-ui.icon name="user" class="size-6 shrink-0 !text-store-ink" />
                            <span class="hidden sm:block"><span class="block font-semibold text-store-ink">Account</span><span class="block text-[10px] text-store-muted">{{ auth()->user()->name }}</span></span>
                            <x-ui.icon name="chevron-down" class="size-4 !text-current" />
                        </button>
                        <div id="store-account-menu" x-cloak x-show="accountMenuOpen" x-transition @click.outside="accountMenuOpen = false" class="absolute right-0 top-full z-50 mt-2 w-56 rounded-card border border-store-border bg-white p-2 shadow-store-soft">
                            <a href="{{ route('account.dashboard') }}" wire:navigate @click="accountMenuOpen = false" class="block rounded-control px-3 py-2 text-sm text-store-text hover:bg-store-soft">Profile</a>
                            <a href="{{ route('account.orders') }}" wire:navigate @click="accountMenuOpen = false" class="block rounded-control px-3 py-2 text-sm text-store-text hover:bg-store-soft">My Orders</a>
                            <a href="{{ route('store.wishlist') }}" wire:navigate @click="accountMenuOpen = false" class="block rounded-control px-3 py-2 text-sm text-store-text hover:bg-store-soft">Wishlist</a>
                            <a href="{{ route('account.addresses') }}" wire:navigate @click="accountMenuOpen = false" class="block rounded-control px-3 py-2 text-sm text-store-text hover:bg-store-soft">Addresses</a>
                            <div class="my-1 border-t border-store-border"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center rounded-control px-3 py-2 text-left text-sm text-store-text hover:bg-store-soft hover:text-store-red">Sign out</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" wire:navigate
                        class="hidden h-11 items-center gap-2 rounded-control px-2 text-xs text-store-muted hover:bg-store-soft hover:text-store-blue lg:flex"><x-ui.icon
                            name="user" class="size-6 shrink-0 !text-store-ink" /><span><span
                                class="block font-semibold text-store-ink">Account</span><span class="block text-[10px]">Sign in / Register</span></span>
                    </a>
                @endauth
                <a href="{{ route('store.wishlist') }}" wire:navigate
                    class="inline-flex h-11 min-w-11 items-center justify-center gap-2 rounded-control px-2 text-store-ink hover:bg-store-soft"
                    aria-label="Wishlist"><x-ui.icon name="heart"
                        class="size-6 shrink-0 !text-store-ink" aria-hidden="true" /><span
                        class="hidden text-xs xl:inline">Wishlist</span></a>
                <button type="button"
                    class="relative inline-flex size-11 items-center justify-center rounded-control text-store-ink hover:bg-store-soft"
                    x-ref="cartTrigger" @click="openCart($event.currentTarget)" aria-controls="cart-drawer"
                    :aria-expanded="cartOpen.toString()" :aria-busy="(!cartLoaded).toString()">
                    <span x-cloak x-show="!cartLoaded" aria-hidden="true">
                        <x-ui.icon.loading class="size-6 !text-store-ink" />
                    </span>
                    <span x-cloak x-show="cartLoaded" aria-hidden="true">
                        <x-ui.icon name="shopping-cart" class="size-6 !text-store-ink" />
                    </span>
                    <span
                        x-cloak
                        x-show="cartLoaded && cart.reduce((total, item) => total + item.quantity, 0) > 0"
                        class="absolute right-0 top-0 grid size-4 place-items-center rounded-full bg-store-red text-[10px] font-bold text-white"
                        x-text="cart.reduce((total, item) => total + item.quantity, 0)"></span>
                    <span class="sr-only">Open shopping cart</span>
                </button>
            </nav>
        </div>

        @if($headerShowSearch ?? true)
            <livewire:components.store.header-search :categories="$categories" mode="mobile" />
        @endif
    </x-store.ui.container>

    @if(count($headerMenu ?? []))
        <nav class="hidden border-t border-store-border lg:block" aria-label="Primary navigation">
            <x-store.ui.container>
                <ul class="flex items-center gap-6 py-3 text-sm font-semibold text-store-text">
                    @foreach($headerMenu as $item)
                        @if($item['enabled'])
                            <li class="relative group">
                                <a href="{{ $item['url'] }}" wire:navigate class="hover:text-store-blue">{{ $item['label'] }}</a>
                                @if(count($item['children']))
                                    <ul class="invisible absolute left-0 top-full z-20 min-w-48 rounded-control border border-store-border bg-white p-2 opacity-0 shadow-store-soft transition group-hover:visible group-hover:opacity-100">
                                        @foreach($item['children'] as $child)
                                            @if($child['enabled'])
                                                <li><a href="{{ $child['url'] }}" wire:navigate class="block rounded px-3 py-2 text-xs hover:bg-store-soft hover:text-store-blue">{{ $child['label'] }}</a></li>
                                            @endif
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endif
                    @endforeach
                </ul>
            </x-store.ui.container>
        </nav>
    @endif

    <div id="mobile-navigation" x-cloak x-show="mobileMenuOpen" x-transition.opacity
        class="absolute inset-x-0 top-full border-y border-store-border bg-white shadow-store-soft lg:hidden">
        <x-store.ui.container class="py-3">
            <a href="#categories"
                class="mb-2 flex items-center gap-2 rounded-control bg-store-blue px-3 py-3 text-sm font-semibold text-white"><x-ui.icon
                    name="squares-2x2" class="size-5 !text-white" />All Categories</a>
            <div class="grid grid-cols-2 gap-1">
                @forelse (($mobileMenu ?? []) as $item)
                    @if($item['enabled'])
                        <a href="{{ $item['url'] }}" wire:navigate class="rounded-control px-3 py-2 text-sm font-medium text-store-text hover:bg-store-soft">{{ $item['label'] }}</a>
                    @endif
                @empty
                    @foreach ($categories as $category)
                        <a href="{{ route('store.category', ['slug' => $category['slug']]) }}" wire:navigate class="rounded-control px-3 py-2 text-sm font-medium text-store-text hover:bg-store-soft">{{ $category['name'] }}</a>
                    @endforeach
                @endforelse
                <a href="#offers"
                    class="rounded-control bg-store-promo-soft px-3 py-2 text-sm font-semibold text-store-red">Offers
                    Zone</a>
            </div>
        </x-store.ui.container>
    </div>
</header>
