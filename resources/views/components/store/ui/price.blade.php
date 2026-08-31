@props(['price', 'oldPrice' => null, 'size' => 'default'])

@php
    $sizes = [
        'sm' => 'text-sm',
        'default' => 'text-lg md:text-xl',
        'lg' => 'text-2xl md:text-3xl',
    ];

    $priceMinor = (int) $price;
    $oldPriceMinor = (int) ($oldPrice ?? 0);
    $formattedPrice = number_format($priceMinor / 100, 2);
    $formattedOldPrice = $oldPriceMinor > $priceMinor ? number_format($oldPriceMinor / 100, 2) : null;
@endphp

<div {{ $attributes->class('flex flex-wrap items-baseline gap-x-2 gap-y-1') }}>
    <span class="{{ $sizes[$size] ?? $sizes['default'] }} font-extrabold text-store-red">৳{{ $formattedPrice }}</span>
    @if ($formattedOldPrice)
        <span class="text-xs font-medium text-store-placeholder line-through">৳{{ $formattedOldPrice }}</span>
    @endif
</div>
