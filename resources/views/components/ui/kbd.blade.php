@props([])

<kbd {{ $attributes->class('rounded border border-neutral-200 bg-neutral-50 px-1.5 py-0.5 font-mono text-[10px] font-medium text-neutral-500 dark:border-white/10 dark:bg-white/5 dark:text-neutral-300') }}>
    {{ $slot }}
</kbd>
