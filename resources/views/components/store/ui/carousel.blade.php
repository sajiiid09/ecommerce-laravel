@props(['label' => 'Carousel', 'loop' => false, 'showDots' => true, 'showControls' => true, 'autoplay' => false, 'interval' => 5000])

<div x-data="storeCarousel({ loop: @js($loop), autoplay: @js($autoplay), interval: @js($interval) })" x-init="init()" {{ $attributes->class('relative') }} aria-roledescription="carousel" aria-label="{{ $label }}">
    <div class="relative">
        <div class="-mx-1 overflow-hidden {{ $showControls ? 'px-12' : 'px-1' }}">
            <div x-ref="viewport" class="overflow-hidden">
                <div x-ref="track" class="flex cursor-grab touch-pan-y gap-3 pb-2 active:cursor-grabbing" tabindex="0" @keydown.left.prevent="previous()" @keydown.right.prevent="next()">
                    {{ $slot }}
                </div>
            </div>
        </div>

        @if ($showControls)
        <div x-show="pageCount > 1" class="pointer-events-none absolute inset-y-0 left-0 right-0 flex items-center justify-between px-2">
            <button type="button" class="pointer-events-auto grid size-10 place-items-center rounded-full border border-store-border bg-white/95 text-store-ink shadow-sm transition hover:border-store-blue hover:text-store-blue disabled:cursor-not-allowed disabled:opacity-40" @click="previous()" :disabled="!canPrevious" aria-label="Previous {{ $label }}"><x-ui.icon name="chevron-left" class="size-5 !text-current" /></button>
            <button type="button" class="pointer-events-auto grid size-10 place-items-center rounded-full border border-store-border bg-white/95 text-store-ink shadow-sm transition hover:border-store-blue hover:text-store-blue disabled:cursor-not-allowed disabled:opacity-40" @click="next()" :disabled="!canNext" aria-label="Next {{ $label }}"><x-ui.icon name="chevron-right" class="size-5 !text-current" /></button>
        </div>
        @endif
    </div>
    @if ($showDots)
    <div x-show="pageCount > 1" class="mt-4 flex justify-center gap-1.5" role="tablist" aria-label="{{ $label }} pages">
        <template x-for="page in pageCount" :key="page"><button type="button" class="size-2 rounded-full transition-colors" :class="current === page - 1 ? 'bg-store-blue' : 'bg-store-border'" @click="goTo(page - 1)" :aria-label="'Go to {{ $label }} page ' + page" :aria-current="current === page - 1 ? 'true' : 'false'"></button></template>
    </div>
    @endif
</div>
