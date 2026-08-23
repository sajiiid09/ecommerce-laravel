@props(['items' => []])

<nav aria-label="Breadcrumb" class="mb-5 text-xs text-store-muted">
    <ol class="flex flex-wrap items-center gap-2">
        <li><a href="{{ route('store.home') }}" class="hover:text-store-blue">Home</a></li>
        @foreach ($items as $item)
            <li aria-hidden="true" class="text-store-border">/</li>
            <li @class(['text-store-ink' => empty($item['url']), 'font-medium' => empty($item['url'])])>
                @if (!empty($item['url']))<a href="{{ $item['url'] }}" class="hover:text-store-blue">{{ $item['label'] }}</a>@else{{ $item['label'] }}@endif
            </li>
        @endforeach
    </ol>
</nav>
