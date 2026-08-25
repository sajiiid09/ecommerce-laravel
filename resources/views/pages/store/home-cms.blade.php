<x-layouts.app title="StoreZ - Shop Smarter">
    <main class="bg-white pb-10"><x-store.ui.container>
        @include('pages.store.home-sections', ['sections' => $sections])
    </x-store.ui.container></main>
</x-layouts.app>
