@props(['name', 'quote', 'rating' => 5, 'role' => 'Verified customer', 'avatar' => null])

<article {{ $attributes->class('h-full rounded-card border border-store-border bg-white p-5') }} aria-roledescription="slide">
    <div class="flex items-start justify-between gap-3">
        <div class="flex min-w-0 items-center gap-3">
            @if($avatar)
                <img src="{{ $avatar }}" alt="" class="size-10 shrink-0 rounded-full object-cover">
            @else
                <span class="grid size-10 shrink-0 place-items-center rounded-full bg-store-soft text-sm font-black text-store-blue">{{ str($name)->substr(0, 1)->upper() }}</span>
            @endif
            <div class="min-w-0">
                <h3 class="truncate text-base font-bold text-store-ink">{{ $name }}</h3>
                <p class="text-xs text-store-muted">{{ $role }}</p>
                <x-store.ui.rating :rating="$rating" class="mt-1" />
            </div>
        </div>
        <span class="text-3xl font-bold leading-none text-store-border" aria-hidden="true">&rdquo;</span>
    </div>
    <p class="mt-4 text-sm leading-6 text-store-muted">{{ $quote }}</p>
</article>
