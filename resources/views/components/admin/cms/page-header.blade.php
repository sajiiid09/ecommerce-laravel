@props([
    'eyebrow' => null,
    'title',
    'description' => null,
])

<div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
    <div>
        @if($eyebrow)
            <p class="text-xs font-semibold text-blue-600">{{ $eyebrow }}</p>
        @endif
        <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-slate-950">{{ $title }}</h1>
        @if($description)
            <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
        @endif
    </div>
    @if(isset($actions))
        <div class="flex flex-wrap items-center gap-3">{{ $actions }}</div>
    @elseif(trim($slot))
        <div class="flex flex-wrap items-center gap-3">{{ $slot }}</div>
    @endif
</div>
