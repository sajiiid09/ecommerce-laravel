@php($trustItems = $trustItems ?? [['title' => 'Secure payments', 'description' => 'Protected checkout options.'], ['title' => 'Fast delivery', 'description' => 'Reliable delivery across Bangladesh.'], ['title' => 'Easy returns', 'description' => 'Helpful support when you need it.']])

@foreach($sections as $section)
    @continue(!($section['enabled'] ?? true))
    @php($settings = $section['settings'] ?? [])
    @if(($section['type'] ?? '') === 'hero')
        @php($heroBanners = $settings['heroBanners'] ?? [])
        <section class="mt-5 overflow-hidden rounded-card text-white">
            <x-store.ui.carousel loop autoplay interval="5000" label="Featured promotions" :show-controls="false">
                @forelse($heroBanners as $heroBanner)
                    @php($heroImage = $heroBanner['image'] ?: $heroBanner['mobileImage'])
                    @php($heroTheme = ['blue' => 'bg-gradient-to-br from-[#0e55a8] via-[#063875] to-[#052b5d]', 'red' => 'bg-gradient-to-br from-red-700 via-red-600 to-red-950', 'green' => 'bg-gradient-to-br from-emerald-700 via-emerald-600 to-emerald-950', 'amber' => 'bg-gradient-to-br from-amber-600 via-orange-500 to-orange-900'][$heroBanner['theme'] ?? 'blue'] ?? 'bg-gradient-to-br from-[#0e55a8] via-[#063875] to-[#052b5d]')
                    <article class="relative min-h-[300px] w-full shrink-0 overflow-hidden {{ $heroTheme }} p-8 sm:min-h-[360px] sm:p-12">
                        @if($heroImage)<picture>@if($heroBanner['mobileImage'])<source media="(max-width: 639px)" srcset="{{ $heroBanner['mobileImage'] }}">@endif<img src="{{ $heroImage }}" alt="{{ $heroBanner['title'] ?: 'StoreZ promotion' }}" class="absolute inset-0 size-full object-cover opacity-45"></picture>@endif
                        <div class="absolute inset-0 bg-gradient-to-r from-black/35 via-black/10 to-transparent"></div>
                        <div class="relative z-10 max-w-2xl">
                            @if($heroBanner['eyebrow'])<p class="text-xs font-bold uppercase tracking-[0.2em] text-white/75">{{ $heroBanner['eyebrow'] }}</p>@endif
                            <h1 class="mt-3 text-4xl font-black leading-tight sm:text-6xl">{{ $heroBanner['title'] ?: 'StoreZ promotion' }}</h1>
                            @if($heroBanner['description'])<p class="mt-4 max-w-xl text-base text-white/85 sm:text-lg">{{ $heroBanner['description'] }}</p>@endif
                            @if($heroBanner['ctaLabel'])<a href="{{ $heroBanner['url'] ?: route('store.offers') }}" class="mt-6 inline-flex rounded-control bg-white px-5 py-3 text-sm font-bold text-store-navy transition hover:bg-blue-50">{{ $heroBanner['ctaLabel'] }}</a>@endif
                        </div>
                    </article>
                @empty
                    <article class="relative min-h-[300px] w-full shrink-0 overflow-hidden bg-gradient-to-br from-[#0e55a8] via-[#063875] to-[#052b5d] p-8 sm:min-h-[360px] sm:p-12">
                        @if(!empty($settings['desktopImage']))<img src="{{ $settings['desktopImage'] }}" alt="{{ $section['title'] ?? 'StoreZ promotion' }}" class="absolute inset-0 size-full object-cover opacity-45">@endif
                        <div class="relative z-10 max-w-2xl">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-200">{{ $section['eyebrow'] ?? 'StoreZ everyday value' }}</p>
                            <h1 class="mt-3 text-4xl font-black sm:text-6xl">{{ $section['title'] ?? 'Back to Better Deals Every Day!' }}</h1>
                            <p class="mt-4 max-w-xl text-blue-100">{{ $section['subtitle'] ?? 'Groceries, fashion, electronics and more at unbeatable prices.' }}</p>
                            @if(!empty($settings['cta']))<a href="{{ $settings['url'] ?? route('store.offers') }}" class="mt-6 inline-flex rounded-control bg-store-red px-5 py-3 text-sm font-bold text-white">{{ $settings['cta'] }}</a>@endif
                        </div>
                    </article>
                @endforelse
            </x-store.ui.carousel>
        </section>
    @elseif(($section['type'] ?? '') === 'categories')
        <section class="mt-8"><h2 class="mb-4 text-xl font-extrabold text-store-ink">{{ $section['title'] ?? 'Shop by Category' }}</h2><div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-6">@foreach($settings['categories'] ?? [] as $category)<a href="{{ route('store.category', ['slug' => $category['slug'] ?? '']) }}" class="rounded-card border border-store-border p-4 text-center text-sm font-bold text-store-blue hover:shadow-store-soft">{{ $category['name'] }}</a>@endforeach</div></section>
    @elseif(($section['type'] ?? '') === 'brands')
        <section class="mt-8"><h2 class="mb-4 text-xl font-extrabold text-store-ink">{{ $section['title'] ?? 'Top Brands' }}</h2><div class="flex flex-wrap gap-3">@foreach($settings['brands'] ?? [] as $brand)<a href="{{ route('store.brand', ['slug' => $brand['slug'] ?? ($brand['id'] ?? '')]) }}" class="rounded-card border border-store-border bg-white px-5 py-3 text-sm font-bold text-store-blue hover:border-store-blue">{{ $brand['name'] }}</a>@endforeach</div></section>
    @elseif(($section['type'] ?? '') === 'banners')
        <section class="mt-8 grid gap-4 md:grid-cols-2">@foreach($settings['banners'] ?? [] as $banner)<a href="{{ $banner['url'] ?: route('store.offers') }}" class="group relative overflow-hidden rounded-card bg-store-navy text-white"><picture>@if($banner['mobileImage'])<source media="(max-width: 639px)" srcset="{{ $banner['mobileImage'] }}">@endif@if($banner['image'])<img src="{{ $banner['image'] }}" alt="{{ $banner['title'] }}" class="h-56 w-full object-cover opacity-70 transition group-hover:scale-105">@endif</picture><div class="absolute inset-0 flex flex-col justify-end bg-gradient-to-t from-black/70 to-transparent p-5"><h2 class="text-xl font-black">{{ $banner['title'] ?: 'Featured promotion' }}</h2>@if($banner['description'])<p class="mt-1 text-sm text-white/80">{{ $banner['description'] }}</p>@endif@if($banner['ctaLabel'])<span class="mt-3 inline-flex w-fit rounded-control bg-white px-3 py-2 text-xs font-bold text-store-navy">{{ $banner['ctaLabel'] }}</span>@endif</div></a>@endforeach</section>
    @elseif(($section['type'] ?? '') === 'trust')
        <section class="mt-8 grid gap-3 rounded-card bg-store-soft p-5 sm:grid-cols-3">@foreach($trustItems as $item)<div class="text-center"><p class="font-bold text-store-ink">{{ $item['title'] }}</p><p class="mt-1 text-xs text-store-muted">{{ $item['description'] }}</p></div>@endforeach</section>
    @elseif(($section['type'] ?? '') === 'newsletter')
        <section class="mt-8 rounded-card bg-store-blue p-6 text-white"><h2 class="text-xl font-black">{{ $section['title'] ?? 'Stay in the loop' }}</h2><p class="mt-2 text-sm text-blue-100">{{ $section['subtitle'] ?? 'Get offers and product updates in your inbox.' }}</p><form action="#newsletter" class="mt-4 flex max-w-md"><input type="email" placeholder="Your email" class="min-w-0 flex-1 rounded-l-control border-0 px-3 py-2 text-sm text-store-ink"><button class="rounded-r-control bg-store-red px-4 text-sm font-bold text-white">Subscribe</button></form></section>
    @elseif(in_array(($section['type'] ?? ''), ['featured_products', 'bestsellers', 'new_arrivals', 'flash_deals'], true))
        <section class="mt-8"><h2 class="mb-4 text-xl font-extrabold text-store-ink">{{ $section['title'] ?? 'Featured Products' }}</h2><div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">@foreach($settings['products'] ?? [] as $product)<x-store.catalog.product-card :product="$product" compact />@endforeach</div></section>
    @elseif(in_array(($section['type'] ?? ''), ['shop_by_need', 'testimonials'], true))
        @if(($section['type'] ?? '') === 'testimonials' && !empty($settings['testimonials']))
            <section class="mt-8">
                <div class="mb-4 flex items-end justify-between gap-3"><div><h2 class="text-xl font-extrabold text-store-ink">{{ $section['title'] ?? 'What Our Customers Say' }}</h2><p class="mt-1 text-sm text-store-muted">{{ $section['subtitle'] ?? 'Real feedback from StoreZ shoppers.' }}</p></div></div>
                <x-store.ui.carousel loop label="Customer testimonials">
                    @foreach($settings['testimonials'] as $testimonial)
                        <x-store.ui.testimonial-card wire:key="homepage-testimonial-{{ $testimonial['id'] }}" class="w-full shrink-0 sm:w-[calc((100%-0.75rem)/2)] lg:w-[calc((100%-1.5rem)/3)]" :name="$testimonial['name']" :role="$testimonial['role']" :rating="$testimonial['rating']" :quote="$testimonial['quote']" :avatar="$testimonial['avatar']" />
                    @endforeach
                </x-store.ui.carousel>
            </section>
        @else
            <section class="mt-8 rounded-card border border-store-border bg-store-soft p-6"><h2 class="text-xl font-extrabold text-store-ink">{{ $section['title'] ?? ucwords(str_replace('_', ' ', $section['type'])) }}</h2><p class="mt-2 text-sm text-store-muted">{{ $section['subtitle'] ?? 'Discover more from StoreZ.' }}</p>@if(!empty($settings['content_json']))<pre class="mt-4 whitespace-pre-wrap text-xs text-store-muted">{{ $settings['content_json'] }}</pre>@endif</section>
        @endif
    @else
        <section class="mt-8 rounded-card border border-store-border bg-store-soft p-6"><h2 class="text-xl font-extrabold text-store-ink">{{ $section['title'] ?? 'StoreZ' }}</h2><p class="mt-2 text-sm text-store-muted">{{ $section['subtitle'] ?? 'Discover more from StoreZ.' }}</p></section>
    @endif
@endforeach
