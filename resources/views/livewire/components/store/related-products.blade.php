<div class="contents">
    @if ($related->isNotEmpty())
        <x-store.ui.carousel loop :show-dots="false" label="Related products">
            @foreach ($related as $item)
                <x-store.catalog.product-card wire:key="related-product-{{ $item['id'] }}" :product="$item" compact class="min-w-0 basis-full shrink-0 sm:basis-[calc((100%-0.75rem)/2)] md:basis-[calc((100%-1.5rem)/3)] xl:basis-[calc((100%-3rem)/5)]" />
            @endforeach
        </x-store.ui.carousel>
    @else
        <p class="rounded-card border border-store-border bg-white p-6 text-sm text-store-muted">No related products available.</p>
    @endif
</div>
