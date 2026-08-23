@props(['rating', 'reviews' => null])

<div {{ $attributes->class('flex items-center gap-1 text-xs') }} aria-label="Rated {{ $rating }} out of 5{{ $reviews ? ', ' . number_format($reviews) . ' reviews' : '' }}">
    <x-ui.icon name="star" variant="solid" class="size-4 !text-store-warning" />
    <span class="font-semibold text-store-text">{{ number_format($rating, 1) }}</span>
    @if ($reviews)
        <span class="text-store-muted">({{ number_format($reviews) }})</span>
    @endif
</div>
