@props(['label' => 'Carousel', 'loop' => false, 'showDots' => true, 'showControls' => true])

<div x-data="storeCarousel({ loop: @js($loop) })" x-init="init()" {{ $attributes->class('relative') }} aria-roledescription="carousel" aria-label="{{ $label }}">
    <div class="-mx-1 overflow-hidden px-1">
        <div x-ref="viewport" class="overflow-hidden">
            <div x-ref="track" class="flex cursor-grab touch-pan-y gap-3 pb-2 active:cursor-grabbing" tabindex="0" @keydown.left.prevent="previous()" @keydown.right.prevent="next()">
                {{ $slot }}
            </div>
        </div>
    </div>
    @if ($showDots || $showControls)
    <div class="mt-4 flex items-center justify-between gap-3">
        @if ($showDots)
        <div class="flex items-center gap-1.5" role="tablist" aria-label="{{ $label }} pages">
            <template x-for="page in pageCount" :key="page"><button type="button" class="size-2 rounded-full transition-colors" :class="current === page - 1 ? 'bg-store-blue' : 'bg-store-border'" @click="goTo(page - 1)" :aria-label="`Go to ${label} page ${page}`" :aria-current="current === page - 1 ? 'true' : 'false'"></button></template>
        </div>
        @endif
        @if ($showControls)
        <div class="flex gap-2">
            <button type="button" class="grid size-10 place-items-center rounded-full border border-store-border bg-white text-store-ink transition hover:border-store-blue hover:text-store-blue disabled:cursor-not-allowed disabled:opacity-40" @click="previous()" :disabled="!canPrevious" aria-label="Previous testimonials"><x-ui.icon name="chevron-left" class="size-5 !text-current" /></button>
            <button type="button" class="grid size-10 place-items-center rounded-full border border-store-border bg-white text-store-ink transition hover:border-store-blue hover:text-store-blue disabled:cursor-not-allowed disabled:opacity-40" @click="next()" :disabled="!canNext" aria-label="Next testimonials"><x-ui.icon name="chevron-right" class="size-5 !text-current" /></button>
        </div>
        @endif
    </div>
    @endif
</div>
