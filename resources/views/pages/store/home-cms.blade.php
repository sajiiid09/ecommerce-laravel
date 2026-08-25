<x-layouts.app title="StoreZ — Shop Smarter">
    <main class="bg-white pb-10"><x-store.ui.container>
        @foreach($sections as $section)
            @continue(!($section['enabled'] ?? true))
            @if(($section['type'] ?? '') === 'hero')
                <section class="mt-5 rounded-card bg-gradient-to-br from-[#0e55a8] via-[#063875] to-[#052b5d] p-8 text-white sm:p-12"><p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-200">{{ $section['eyebrow'] ?? 'StoreZ everyday value' }}</p><h1 class="mt-3 max-w-2xl text-4xl font-black sm:text-6xl">{{ $section['title'] ?? 'Back to Better Deals Every Day!' }}</h1><p class="mt-4 max-w-xl text-blue-100">{{ $section['subtitle'] ?? 'Groceries, fashion, electronics and more at unbeatable prices.' }}</p></section>
            @elseif(($section['type'] ?? '') === 'categories')
                <section class="mt-8"><h2 class="mb-4 text-xl font-extrabold text-store-ink">{{ $section['title'] ?? 'Shop by Category' }}</h2><div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-6">@foreach($section['settings']['categories'] ?? [] as $category)<a href="{{ route('store.category',['slug'=>$category['slug'] ?? null]) }}" class="rounded-card border border-store-border p-4 text-center text-sm font-bold text-store-blue hover:shadow-store-soft">{{ $category['name'] }}</a>@endforeach</div></section>
            @elseif(in_array(($section['type'] ?? ''), ['featured_products','bestsellers','new_arrivals','flash_deals']))
                <section class="mt-8"><h2 class="mb-4 text-xl font-extrabold text-store-ink">{{ $section['title'] ?? 'Featured Products' }}</h2><div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">@foreach($section['settings']['products'] ?? [] as $product)<x-store.catalog.product-card :product="$product" compact />@endforeach</div></section>
            @else
                <section class="mt-8 rounded-card border border-store-border bg-store-soft p-6"><h2 class="text-xl font-extrabold text-store-ink">{{ $section['title'] ?? 'StoreZ' }}</h2><p class="mt-2 text-sm text-store-muted">{{ $section['subtitle'] ?? 'Discover more from StoreZ.' }}</p></section>
            @endif
        @endforeach
    </x-store.ui.container></main>
</x-layouts.app>
