<x-layouts.app :title="$page->title.' — StoreZ'">
    <main class="bg-white py-10"><x-store.ui.container><div class="mx-auto max-w-4xl"><p class="text-sm font-semibold text-store-blue">StoreZ / Pages</p><h1 class="mt-2 text-4xl font-black text-store-ink">{{ $page->title }}</h1>@if($page->excerpt)<p class="mt-3 text-lg text-store-muted">{{ $page->excerpt }}</p>@endif<div class="prose mt-8 max-w-none">{!! $page->content_html !!}</div></div></x-store.ui.container></main>
</x-layouts.app>
