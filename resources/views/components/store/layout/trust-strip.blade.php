@props(['items' => []])

<section class="border-y border-store-border bg-store-blue-soft">
    <x-store.ui.container class="grid grid-cols-1 divide-y divide-store-border py-2 sm:grid-cols-2 sm:divide-x sm:divide-y-0 lg:grid-cols-4">
        @foreach ($items as $index => $item)
            <div class="flex items-center gap-3 px-3 py-3 sm:px-5">
                <span class="grid size-9 shrink-0 place-items-center rounded-full bg-white text-store-blue"><x-ui.icon :name="['truck', 'arrow-uturn-left', 'lock-closed', 'banknotes'][$index]" class="size-5 !text-store-blue" /></span>
                <div><p class="text-sm font-bold text-store-ink">{{ $item['title'] }}</p><p class="text-xs text-store-muted">{{ $item['description'] }}</p></div>
            </div>
        @endforeach
    </x-store.ui.container>
</section>
