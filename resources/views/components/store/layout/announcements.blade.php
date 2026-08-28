@props([
    'announcements' => collect(),
    'placement' => 'top_bar',
])

<?php $items = collect($announcements)->values(); ?>

@if($items->isNotEmpty())
    <div
        x-data="{
            ids: @js($items->pluck('id')->values()),
            activeIndex: 0,
            timer: null,
            paused: false,
            init() {
                this.normalize();
                this.start();
            },
            visibleIndices() {
                return this.ids.map((id, index) => index);
            },
            normalize() {
                const visible = this.visibleIndices();
                if (!visible.includes(this.activeIndex)) {
                    this.activeIndex = visible[0];
                }
            },
            start() {
                this.stop();
                if (!this.paused && this.visibleIndices().length > 1) {
                    this.timer = setInterval(() => this.next(), 5000);
                }
            },
            stop() {
                if (this.timer) {
                    clearInterval(this.timer);
                    this.timer = null;
                }
            },
            next() {
                const visible = this.visibleIndices();
                if (visible.length < 2) return;
                const current = visible.indexOf(this.activeIndex);
                this.activeIndex = visible[(current + 1) % visible.length];
            },
            previous() {
                const visible = this.visibleIndices();
                if (visible.length < 2) return;
                const current = visible.indexOf(this.activeIndex);
                this.activeIndex = visible[(current - 1 + visible.length) % visible.length];
            },
        }"
        x-init="init(); return () => stop()"
        x-on:mouseenter="paused = true; stop()"
        x-on:mouseleave="paused = false; start()"
        x-on:focusin="paused = true; stop()"
        x-on:focusout="if (!$el.contains($event.relatedTarget)) { paused = false; start() }"
        x-on:livewire:navigating.window="stop()"
        x-show="ids.length > 0"
        aria-live="polite"
        class="relative"
    >
        <?php foreach ($items as $index => $announcement): ?>
            @php
                $styleClasses = match ($announcement->style) {
                    'success' => 'bg-emerald-600 text-white',
                    'warning' => 'bg-amber-400 text-amber-950',
                    'danger' => 'bg-red-600 text-white',
                    default => 'bg-store-blue text-white',
                };
            @endphp
            <div
                x-cloak
                x-show="activeIndex === {{ $index }}"
                x-bind:aria-hidden="activeIndex !== {{ $index }}"
                class="{{ $styleClasses }} flex min-h-10 items-center justify-center gap-3 px-4 py-2 text-center text-sm font-medium"
            >
                <span>{{ $announcement->message }}</span>
                @if(filled($announcement->link_url))
                    <a href="{{ $announcement->link_url }}" class="font-bold underline underline-offset-2 hover:opacity-80">{{ $announcement->link_label ?: 'Learn more' }}</a>
                @endif
                @if($items->count() > 1)
                    <div class="flex items-center gap-1" aria-label="Announcement controls">
                        <button type="button" x-on:click="previous()" class="rounded px-1.5 text-lg leading-none hover:bg-black/10" aria-label="Previous announcement">&#8249;</button>
                        <button type="button" x-on:click="next()" class="rounded px-1.5 text-lg leading-none hover:bg-black/10" aria-label="Next announcement">&#8250;</button>
                    </div>
                @endif
            </div>
        <?php endforeach; ?>
    </div>
@endif
