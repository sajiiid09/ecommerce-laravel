@props(['discount'])

@if ($discount)
    <span {{ $attributes->class('inline-flex rounded bg-store-red px-1.5 py-1 text-[10px] font-extrabold uppercase tracking-wide text-white') }}>{{ $discount }}% Off</span>
@endif
