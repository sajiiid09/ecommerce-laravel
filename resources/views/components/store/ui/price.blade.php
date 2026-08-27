@props(['price', 'oldPrice' => null, 'size' => 'default'])

@php
    $sizes = [
        'sm' => 'text-sm',
        'default' => 'text-lg md:text-xl',
        'lg' => 'text-2xl md:text-3xl',
    ];

    $formattedPrice = number_format((int) $price / 100, 2);
    $formattedOldPrice = $oldPrice ? number_format((int) $oldPrice / 100, 2) : null;
@endphp

<div {{ $attributes->class('flex flex-wrap items-baseline gap-x-2 gap-y-1') }}>
    <span class="{{ $sizes[$size] ?? $sizes['default'] }} font-extrabold text-store-red">৳{{ $formattedPrice }}</span>
    @if ($formattedOldPrice)
        <span class="text-xs font-medium text-store-placeholder line-through">৳{{ $formattedOldPrice }}</span>
    @endif
</div>
