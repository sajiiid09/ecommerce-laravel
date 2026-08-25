@props(['active' => false])

<button {{ $attributes->class([
    'border-b-2 px-1 pb-3 text-sm font-semibold transition',
    'border-blue-600 text-blue-600' => $active,
    'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-900' => ! $active,
]) }}>
    {{ $slot }}
</button>
