@props(['paginator' => null])

@if ($paginator)
    <x-ui.pagination :paginator="$paginator" {{ $attributes->class('mt-6') }} />
@endif
