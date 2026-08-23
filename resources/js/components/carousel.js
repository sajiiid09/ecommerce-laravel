import EmblaCarousel from 'embla-carousel';

document.addEventListener('alpine:init', () => {
    Alpine.data('storeCarousel', (config = {}) => ({
        loop: Boolean(config.loop),
        autoplay: Boolean(config.autoplay),
        interval: Number(config.interval) || 5000,
        current: 0,
        embla: null,
        autoplayTimer: null,

        init() {
            this.embla = EmblaCarousel(this.$refs.viewport, {
                align: 'start',
                // Handle looping manually so Embla does not render cloned
                // first/last slides at the edges of the viewport.
                loop: false,
                containScroll: 'trimSnaps',
                skipSnaps: false,
                slides: 'article',
            });

            this.syncCurrent = this.syncCurrent.bind(this);
            this.embla.on('select', this.syncCurrent);
            this.embla.on('reInit', this.syncCurrent);
            this.syncCurrent();
            this.startAutoplay();
        },

        get pageCount() {
            return this.embla?.scrollSnapList().length || 1;
        },

        get canPrevious() {
            return this.loop || Boolean(this.embla?.canScrollPrev());
        },

        get canNext() {
            return this.loop || Boolean(this.embla?.canScrollNext());
        },

        previous() {
            if (!this.embla) return;

            if (this.embla.canScrollPrev()) {
                this.embla.scrollPrev();
            } else if (this.loop) {
                this.embla.scrollTo(this.pageCount - 1);
            }
        },

        next() {
            if (!this.embla) return;

            if (this.embla.canScrollNext()) {
                this.embla.scrollNext();
            } else if (this.loop) {
                this.embla.scrollTo(0);
            }
        },

        goTo(page) {
            this.embla?.scrollTo(page);
        },

        syncCurrent() {
            this.current = this.embla?.selectedScrollSnap() || 0;
        },

        startAutoplay() {
            if (!this.autoplay || !this.embla || this.pageCount < 2) return;

            this.stopAutoplay();
            this.autoplayTimer = window.setInterval(() => this.next(), this.interval);
        },

        stopAutoplay() {
            if (this.autoplayTimer) {
                window.clearInterval(this.autoplayTimer);
                this.autoplayTimer = null;
            }
        },

        destroy() {
            this.stopAutoplay();
            this.embla?.destroy();
        },
    }));
});
