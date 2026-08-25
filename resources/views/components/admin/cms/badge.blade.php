@props(['tone' => 'neutral'])

@php
    $classes = [
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/10',
        'warning' => 'bg-amber-50 text-amber-700 ring-amber-600/10',
        'danger' => 'bg-red-50 text-red-700 ring-red-600/10',
        'info' => 'bg-blue-50 text-blue-700 ring-blue-600/10',
        'neutral' => 'bg-slate-100 text-slate-600 ring-slate-500/10',
    ][$tone] ?? 'bg-slate-100 text-slate-600 ring-slate-500/10';
@endphp

<span {{ $attributes->class("inline-flex items-center rounded-md px-2 py-1 text-[11px] font-bold ring-1 ring-inset {$classes}") }}>{{ $slot }}</span>
