<div @class([
    'relative hidden min-w-0 flex-1 md:flex' => $mode === 'desktop',
    'relative pb-3 md:hidden' => $mode === 'mobile',
]) x-data="{ searchOpen: false, activeIndex: -1 }" @keydown.escape.window="searchOpen = false; activeIndex = -1">
    <form id="header-search-form-{{ $mode }}" class="flex w-full" role="search" action="{{ route('store.search') }}" method="get">
        <label for="global-search-{{ $mode }}" class="sr-only">Search products</label>
        <div class="relative flex h-11 w-full overflow-visible rounded-control border border-store-border bg-white transition focus-within:border-store-blue focus-within:ring-2 focus-within:ring-store-blue/10">
            <input
                id="global-search-{{ $mode }}"
                name="q"
                type="search"
                wire:model.live.debounce.300ms="query"
                x-on:focus="searchOpen = $event.target.value.trim().length >= 2"
                x-on:input="searchOpen = $event.target.value.trim().length >= 2; activeIndex = -1"
                x-on:keydown.arrow-down.prevent="searchOpen = true; activeIndex = Math.min(activeIndex + 1, Math.max(0, $root.querySelectorAll('[data-search-result]').length - 1))"
                x-on:keydown.arrow-up.prevent="activeIndex = Math.max(activeIndex - 1, -1)"
                x-on:keydown.enter="if (activeIndex >= 0) { $event.preventDefault(); $root.querySelectorAll('[data-search-result]')[activeIndex]?.click(); }"
                placeholder="Search for products, brands and more"
                autocomplete="off"
                role="combobox"
                aria-autocomplete="list"
                aria-controls="header-search-results-{{ $mode }}"
                x-bind:aria-expanded="searchOpen.toString()"
                class="min-w-0 flex-1 border-0 px-4 text-sm outline-none placeholder:text-store-placeholder"
            >
            @if($mode === 'desktop')
                <select name="category" aria-label="Product category" class="hidden min-w-44 border-x border-store-border bg-store-soft px-3 text-xs text-store-text outline-none xl:block">
                    <option value="">All Categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category['slug'] }}">{{ $category['name'] }}</option>
                    @endforeach
                </select>
            @endif
            <button type="submit" class="grid w-12 shrink-0 place-items-center bg-store-blue text-white transition hover:bg-store-blue-dark" aria-label="Search">
                <x-ui.icon name="magnifying-glass" class="size-5 !text-white" />
            </button>
        </div>
    </form>

    <div
        id="header-search-results-{{ $mode }}"
        x-cloak
        x-show="searchOpen"
        x-transition.origin.top
        @click.outside="searchOpen = false; activeIndex = -1"
        class="absolute inset-x-0 top-full z-50 mt-2 overflow-hidden rounded-card border border-store-border bg-white shadow-store-soft"
        role="listbox"
        aria-label="Product search suggestions"
    >
        <div wire:loading.flex wire:target="query" class="items-center gap-2 px-4 py-5 text-sm text-store-muted">
            <x-ui.icon.loading class="size-4 !text-store-blue" /> Searching products...
        </div>

        <div wire:loading.remove wire:target="query">
            @if($query !== '' && mb_strlen($query) < 2)
                <p class="px-4 py-5 text-sm text-store-muted">Keep typing to search products.</p>
            @elseif(count($suggestions))
                <div class="max-h-96 overflow-y-auto py-2">
                    @foreach($suggestions as $index => $product)
                        <a
                            href="{{ route('store.product', ['slug' => $product['slug']]) }}"
                            wire:navigate
                            data-search-result
                            role="option"
                            aria-selected="false"
                            x-on:mouseenter="activeIndex = {{ $index }}"
                            x-bind:aria-selected="(activeIndex === {{ $index }}).toString()"
                            x-bind:class="activeIndex === {{ $index }} ? 'bg-store-soft' : ''"
                            class="flex items-center gap-3 px-4 py-3 transition hover:bg-store-soft"
                        >
                            <img src="{{ asset(ltrim($product['image'], '/')) }}" alt="" class="size-12 shrink-0 object-contain" loading="lazy" decoding="async">
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-semibold text-store-ink">{{ $product['name'] }}</span>
                                <span class="mt-1 block text-xs text-store-muted">{{ $product['brand'] }}</span>
                            </span>
                            <x-store.ui.price :price="$product['price']" :old-price="$product['oldPrice']" size="sm" class="shrink-0" />
                        </a>
                    @endforeach
                </div>
                <button type="submit" form="header-search-form-{{ $mode }}" class="block w-full border-t border-store-border bg-store-soft px-4 py-3 text-center text-sm font-bold text-store-blue hover:text-store-blue-dark">See all results</button>
            @elseif($query !== '')
                <p class="px-4 py-5 text-sm text-store-muted">No products found.</p>
            @endif
        </div>
    </div>
</div>
