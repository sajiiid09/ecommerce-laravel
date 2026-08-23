<x-layouts.app title="{{ $query ? 'Search: ' . $query : 'Search Products' }} — StoreZ">
    @include('pages.store.search-content')
</x-layouts.app>
