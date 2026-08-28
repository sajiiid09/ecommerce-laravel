<main class="bg-store-soft pb-12">
    <x-store.ui.container>
        <div class="py-8">
            <p class="text-sm font-semibold text-store-blue">Home / Offers & Deals</p>
            <h1 class="mt-2 text-3xl font-black text-store-ink">Offers & Deals</h1>
            <p class="mt-2 text-store-muted">Save more on limited-time StoreZ promotions.</p>
        </div>
        @if(count($banners ?? []))
            <div class="mb-8 grid gap-4 md:grid-cols-2">@foreach($banners as $banner)<a href="{{ $banner['url'] ?: '#limited' }}" class="group relative overflow-hidden rounded-card bg-store-navy text-white"><img src="{{ $banner['image'] ?: asset('images/placeholders/no-image.svg') }}" alt="{{ $banner['title'] }}" loading="lazy" decoding="async" class="h-48 w-full object-cover opacity-70 transition group-hover:scale-105"><div class="absolute inset-0 flex flex-col justify-end bg-gradient-to-t from-black/70 to-transparent p-5"><h2 class="text-xl font-black">{{ $banner['title'] ?: 'Special offer' }}</h2>@if($banner['ctaLabel'])<span class="mt-3 inline-flex w-fit rounded-control bg-white px-3 py-2 text-xs font-bold text-store-navy">{{ $banner['ctaLabel'] }}</span>@endif</div></a>@endforeach</div>
        @endif
        <section class="rounded-card bg-gradient-to-r from-store-navy to-store-blue p-6 text-white sm:p-10">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-200">Weekend mega sale</p>
            <h2 class="mt-3 text-3xl font-black sm:text-5xl">Big deals. Better everyday value.</h2>
            <p class="mt-3 max-w-xl text-blue-100">Explore flash discounts across groceries, electronics, fashion and
                home essentials.</p><a href="#limited"
                class="mt-6 inline-flex rounded-control bg-white px-5 py-3 text-sm font-bold text-store-navy">Shop
                limited deals</a>
        </section>
        <div class="mt-8 flex flex-wrap gap-2"><span
                class="rounded-full bg-store-blue px-4 py-2 text-sm font-semibold text-white">All Offers</span><span
                class="rounded-full border border-store-border bg-white px-4 py-2 text-sm text-store-text">Flash
                Sale</span><span
                class="rounded-full border border-store-border bg-white px-4 py-2 text-sm text-store-text">Combo
                Deals</span><span
                class="rounded-full border border-store-border bg-white px-4 py-2 text-sm text-store-text">Bank
                Offers</span></div>
        <section id="limited" class="mt-8">
            <div class="flex items-center justify-between"><x-store.ui.section-heading
                    title="Limited-time products" /><span class="font-mono text-sm font-bold text-store-red">02 : 45 :
                    30</span></div>
            <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
                @foreach (array_slice($products, 0, 6) as $product)
                    <x-store.catalog.product-card :product="$product" compact />
                @endforeach
            </div>
        </section>
    </x-store.ui.container>
</main>
