import EmblaCarousel from 'embla-carousel';

document.addEventListener('alpine:init', () => {
    Alpine.data('storeCarousel', (config = {}) => ({
        loop: Boolean(config.loop),
        current: 0,
        embla: null,

        init() {
            this.embla = EmblaCarousel(this.$refs.viewport, {
                align: 'start',
                loop: this.loop,
                skipSnaps: false,
                slides: 'article',
            });

            this.syncCurrent = this.syncCurrent.bind(this);
            this.embla.on('select', this.syncCurrent);
            this.embla.on('reInit', this.syncCurrent);
            this.syncCurrent();
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
            this.embla?.scrollPrev();
        },

        next() {
            this.embla?.scrollNext();
        },

        goTo(page) {
            this.embla?.scrollTo(page);
        },

        syncCurrent() {
            this.current = this.embla?.selectedScrollSnap() || 0;
        },

        destroy() {
            this.embla?.destroy();
        },
    }));
});
