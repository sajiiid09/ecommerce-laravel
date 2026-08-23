@props(['icon', 'label', 'variant' => 'default'])

@php
    $variants = [
        'default' => 'border border-store-border bg-white text-store-ink hover:border-store-blue hover:text-store-blue',
        'ghost' => 'text-store-ink hover:bg-store-soft hover:text-store-blue',
        'danger' => 'border border-store-border bg-white text-store-muted hover:border-store-red hover:text-store-red',
    ];
@endphp

<button type="button" {{ $attributes->class(['inline-flex size-11 items-center justify-center rounded-control transition-colors', $variants[$variant] ?? $variants['default']]) }} aria-label="{{ $label }}">
    <x-ui.icon :name="$icon" class="size-5 !text-current" />
    <span class="sr-only">{{ $label }}</span>
</button>
