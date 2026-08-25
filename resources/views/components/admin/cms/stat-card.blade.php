@props([
    'label',
    'value',
    'accent' => 'blue',
    'trend' => null,
    'href' => null,
])

@php
    $accentClasses = [
        'blue' => 'bg-blue-50 text-blue-600',
        'green' => 'bg-emerald-50 text-emerald-600',
        'orange' => 'bg-orange-50 text-orange-600',
        'purple' => 'bg-violet-50 text-violet-600',
        'pink' => 'bg-pink-50 text-pink-600',
    ][$accent] ?? 'bg-blue-50 text-blue-600';
@endphp

<section {{ $attributes->class('rounded-xl border border-slate-200 bg-white p-5 shadow-sm') }}>
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="text-xs font-semibold text-slate-500">{{ $label }}</p>
            <p class="mt-2 text-2xl font-extrabold tracking-tight text-slate-950">{{ $value }}</p>
            @if($href)
                <a href="{{ $href }}" class="mt-2 inline-flex text-xs font-bold text-blue-600 hover:text-blue-700">View details <span aria-hidden="true" class="ml-1">→</span></a>
            @elseif($trend)
                <p class="mt-2 text-xs font-semibold text-emerald-600">{{ $trend }}</p>
            @endif
        </div>
        <span class="grid size-11 shrink-0 place-items-center rounded-full {{ $accentClasses }}" aria-hidden="true">
            {{ $slot }}
        </span>
    </div>
</section>
