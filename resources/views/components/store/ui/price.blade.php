@props(['price', 'oldPrice' => null, 'size' => 'default'])

@php
    $sizes = [
        'sm' => 'text-sm',
        'default' => 'text-lg md:text-xl',
        'lg' => 'text-2xl md:text-3xl',
    ];
@endphp

<div {{ $attributes->class('flex flex-wrap items-baseline gap-x-2 gap-y-1') }}>
    <span class="{{ $sizes[$size] ?? $sizes['default'] }} font-extrabold text-store-red">৳{{ number_format($price) }}</span>
    @if ($oldPrice)
        <span class="text-xs font-medium text-store-placeholder line-through">৳{{ number_format($oldPrice) }}</span>
    @endif
</div>
