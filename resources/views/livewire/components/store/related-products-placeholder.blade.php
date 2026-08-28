<div class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5" aria-label="Loading related products">
    @foreach (range(1, 5) as $product)
        <div class="rounded-card border border-store-border bg-white p-3">
            <x-ui.skeleton class="aspect-square w-full rounded-control" />
            <div class="mt-3 space-y-2">
                <x-ui.skeleton class="h-3 w-16" />
                <x-ui.skeleton class="h-5 w-full" />
                <x-ui.skeleton class="h-5 w-24" />
                <x-ui.skeleton class="h-10 w-full" />
            </div>
        </div>
    @endforeach
</div>
