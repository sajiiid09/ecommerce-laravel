@props(['size' => 'default'])

@php
    $width = match ($size) {
        'narrow' => 'max-w-2xl',
        'content' => 'max-w-6xl',
        default => 'max-w-8xl',
    };
@endphp

<div {{ $attributes->class([$width, 'mx-auto w-full px-4 sm:px-5 lg:px-6']) }}>
    {{ $slot }}
</div>
