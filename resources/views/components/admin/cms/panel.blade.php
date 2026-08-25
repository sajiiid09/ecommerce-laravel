@props(['title' => null, 'description' => null])

<section {{ $attributes->class('rounded-xl border border-slate-200 bg-white p-5 shadow-sm') }}>
    @if($title || $description)
        <div class="flex items-start justify-between gap-3">
            <div>
                @if($title)<h2 class="text-base font-bold text-slate-950">{{ $title }}</h2>@endif
                @if($description)<p class="mt-1 text-xs text-slate-500">{{ $description }}</p>@endif
            </div>
        </div>
    @endif
    <div @class(['mt-4' => $title || $description])>{{ $slot }}</div>
</section>
