@php
    $allProducts = \App\Support\StorefrontDemoData::products();
    $queryLower = strtolower($query);
    $products =
        $query === ''
            ? $allProducts
            : array_values(
                array_filter(
                    $allProducts,
                    fn(array $product) => str_contains(
                        strtolower($product['name'] . ' ' . $product['brand']),
                        $queryLower,
                    ),
                ),
            );
    $brands = collect($allProducts)->pluck('brand')->unique()->values()->all();
    $selectedBrands = (array) request('brand', []);
    if ($selectedBrands) {
        $products = array_values(
            array_filter($products, fn($product) => in_array($product['brand'], $selectedBrands, true)),
        );
    }
    if (request()->boolean('in_stock')) {
        $products = array_values(array_filter($products, fn($product) => $product['inStock']));
    }
    $sort = request('sort', 'relevance');
    if ($sort === 'price_asc') {
        usort($products, fn($a, $b) => $a['price'] <=> $b['price']);
    } elseif ($sort === 'price_desc') {
        usort($products, fn($a, $b) => $b['price'] <=> $a['price']);
    } elseif ($sort === 'rating') {
        usort($products, fn($a, $b) => $b['rating'] <=> $a['rating']);
    }
    $page = max(1, (int) request('page', 1));
    $pageCount = max(1, (int) ceil(count($products) / 12));
@endphp

<main x-data="{ filtersOpen: false }" class="bg-store-soft py-6 sm:py-8">
    <x-store.ui.container><x-store.ui.breadcrumb :items="[['label' => 'Search']]" />
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-store-ink sm:text-3xl">
                    {{ $query ? 'Search results for “' . $query . '”' : 'Search all products' }}</h1>
                <p class="mt-1 text-sm text-store-muted">{{ count($products) }} products found</p>
            </div><button type="button"
                class="inline-flex h-10 items-center gap-2 rounded-control border border-store-blue bg-white px-4 text-sm font-bold text-store-blue lg:hidden"
                @click="filtersOpen = true" aria-controls="search-filters"
                :aria-expanded="filtersOpen.toString()"><x-ui.icon name="adjustments-horizontal"
                    class="size-4 !text-current" />Filters</button>
        </div>
        <form method="GET" class="mt-6 grid gap-5 lg:grid-cols-[240px_minmax(0,1fr)]">
            <div x-cloak x-show="filtersOpen" x-transition.opacity class="fixed inset-0 z-30 bg-store-ink/45 lg:hidden"
                @click="filtersOpen = false" aria-hidden="true"></div>
            <aside id="search-filters" x-cloak x-show="filtersOpen || window.innerWidth >= 1024" x-transition
                class="fixed inset-y-0 left-0 z-40 h-auto w-[min(88vw,22rem)] overflow-y-auto rounded-none border-r border-store-border bg-white p-4 shadow-2xl lg:static lg:z-auto lg:block lg:h-fit lg:w-auto lg:rounded-card lg:border lg:shadow-none"
                @keydown.escape.window="filtersOpen = false">
                <div class="flex items-center justify-between">
                    <h2 class="font-bold text-store-ink">Filter results</h2>
                    <div class="flex items-center gap-3"><a href="{{ route('store.search', ['q' => $query]) }}"
                            class="text-xs font-semibold text-store-blue hover:underline">Clear all</a><button
                            type="button" class="lg:hidden" @click="filtersOpen = false"
                            aria-label="Close filters"><x-ui.icon name="x-mark"
                                class="size-5 !text-current" /></button></div>
                </div><input type="hidden" name="q" value="{{ $query }}">
                <div class="mt-5 space-y-5"><x-store.ui.filter-group title="Brands" name="brand" :options="$brands"
                        :selected="$selectedBrands" /><x-store.ui.filter-group title="Availability" name="in_stock"
                        :options="['In stock only']" :selected="request('in_stock') ? ['In stock only'] : []" /></div><button
                    class="mt-5 h-10 w-full rounded-control bg-store-blue text-sm font-bold text-white"
                    @click="filtersOpen = false">Apply filters</button>
            </aside>
            <section>
                <div
                    class="flex flex-wrap items-center justify-between gap-3 rounded-card border border-store-border bg-white p-3">
                    <p class="text-sm text-store-muted">Showing <span
                            class="font-bold text-store-ink">{{ count($products) }}</span> results</p>
                    <div class="flex items-center gap-2" x-data="{ selectedSort: @js($sort) }"><label
                            class="text-xs font-semibold text-store-muted">Sort by</label><x-ui.select name="sort"
                            x-model="selectedSort" size="sm" placeholder="Relevance"
                            trigger-class="!bg-white !text-store-ink dark:!bg-white dark:!text-store-ink"
                            @change="setTimeout(() => $el.closest('form').submit())" class="w-48">
                            <x-ui.select.option value="relevance">Relevance</x-ui.select.option>
                            <x-ui.select.option value="price_asc">Price: Low to High</x-ui.select.option>
                            <x-ui.select.option value="price_desc">Price: High to Low</x-ui.select.option>
                            <x-ui.select.option value="rating">Rating</x-ui.select.option>
                        </x-ui.select></div>
                </div>
                @if (count($products))
                    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4">
                        @foreach (array_slice($products, ($page - 1) * 12, 12) as $product)
                            <x-store.catalog.product-card :product="$product" />
                        @endforeach
                    </div>
                <x-store.ui.pagination :current="$page" :total="$pageCount" />@else<div
                        class="mt-4 rounded-card border border-store-border bg-white p-12 text-center"><x-ui.icon
                            name="magnifying-glass" class="mx-auto size-10 !text-store-placeholder" />
                        <h2 class="mt-4 text-lg font-bold text-store-ink">No products found</h2>
                        <p class="mt-1 text-sm text-store-muted">Try a different product, brand, or category.</p><a
                            href="{{ route('store.home') }}" wire:navigate
                            class="mt-5 inline-flex h-10 items-center rounded-control bg-store-blue px-4 py-2 text-sm font-bold text-white">Continue
                            Shopping</a>
                    </div>
                @endif
            </section>
        </form>
    </x-store.ui.container>
</main>
