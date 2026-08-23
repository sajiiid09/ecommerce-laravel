@props(['name', 'quote', 'rating' => 5])

<article {{ $attributes->class('h-full rounded-card border border-store-border bg-white p-5') }} aria-roledescription="slide">
    <div class="flex items-start justify-between gap-3"><div><h3 class="text-base font-bold text-store-ink">{{ $name }}</h3><x-store.ui.rating :rating="$rating" class="mt-1" /></div><span class="text-3xl font-bold leading-none text-store-border" aria-hidden="true">”</span></div>
    <p class="mt-4 text-sm leading-6 text-store-muted">{{ $quote }}</p>
</article>
