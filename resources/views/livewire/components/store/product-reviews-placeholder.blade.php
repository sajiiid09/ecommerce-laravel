<section class="mt-8 rounded-card border border-store-border bg-white p-5" aria-label="Loading customer reviews">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div class="space-y-2">
            <x-ui.skeleton class="h-6 w-52" />
            <x-ui.skeleton class="h-4 w-64" />
        </div>
        <x-ui.skeleton class="h-5 w-24" />
    </div>
    <div class="mt-5 grid gap-5 lg:grid-cols-[minmax(0,1fr)_320px]">
        <div class="space-y-4">
            @foreach (range(1, 3) as $review)
                <div class="space-y-2 border-b border-store-border pb-4 last:border-0">
                    <x-ui.skeleton class="h-4 w-32" />
                    <x-ui.skeleton class="h-4 w-48" />
                    <x-ui.skeleton class="h-4 w-full" />
                    <x-ui.skeleton class="h-4 w-4/5" />
                </div>
            @endforeach
        </div>
        <div class="space-y-3 rounded-control bg-store-soft p-4">
            <x-ui.skeleton class="h-5 w-32" />
            <x-ui.skeleton class="h-10 w-full" />
            <x-ui.skeleton class="h-10 w-full" />
            <x-ui.skeleton class="h-24 w-full" />
            <x-ui.skeleton class="h-10 w-full" />
        </div>
    </div>
</section>
