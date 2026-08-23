@props(['status'])

@php
    $states = [
        'delivered' => ['Delivered', 'bg-green-100 text-green-700'],
        'processing' => ['Processing', 'bg-orange-100 text-orange-700'],
        'shipped' => ['Shipped', 'bg-blue-100 text-blue-700'],
        'out_for_delivery' => ['Out for delivery', 'bg-blue-100 text-blue-700'],
        'cancelled' => ['Cancelled', 'bg-red-100 text-red-700'],
        'returned' => ['Returned', 'bg-slate-100 text-slate-700'],
    ];
    [$label, $classes] = $states[$status] ?? [str($status)->headline(), 'bg-slate-100 text-slate-700'];
@endphp

<span {{ $attributes->class(['inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold', $classes]) }}>{{ $label }}</span>
