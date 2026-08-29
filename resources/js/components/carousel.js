import EmblaCarousel from 'embla-carousel';

document.addEventListener('alpine:init', () => {
    Alpine.data('storeCarousel', (config = {}) => ({
        loop: Boolean(config.loop),
        autoplay: Boolean(config.autoplay),
        interval: Number(config.interval) || 5000,
        current: 0,
        embla: null,
        autoplayTimer: null,
        autoplayResumeTimer: null,
        autoplayPaused: false,
        viewport: null,

        init() {
            if (this.embla) return;

            this.viewport = this.$refs.viewport;

            if (!this.viewport) return;

            this.embla = EmblaCarousel(this.viewport, {
                align: 'start',
                // Handle looping manually so Embla does not render cloned
                // first/last slides at the edges of the viewport.
                loop: false,
                containScroll: 'trimSnaps',
                skipSnaps: false,
                slides: 'article',
            });

            this.syncCurrent = this.syncCurrent.bind(this);
            this.handleUserInteraction = this.handleUserInteraction.bind(this);
            this.embla.on('select', this.syncCurrent);
            this.embla.on('reInit', this.syncCurrent);
            this.embla.on('pointerDown', this.handleUserInteraction);
            this.viewport.addEventListener('wheel', this.handleUserInteraction, { passive: true });
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

            this.pauseAutoplayForInteraction();

            if (this.embla.canScrollPrev()) {
                this.embla.scrollPrev();
            } else if (this.loop) {
                this.embla.scrollTo(this.pageCount - 1);
            }
        },

        next() {
            if (!this.embla) return;

            this.pauseAutoplayForInteraction();
            this.advanceToNextSlide();
        },

        advanceToNextSlide() {
            if (!this.embla) return;

            if (this.embla.canScrollNext()) {
                this.embla.scrollNext();
            } else if (this.loop) {
                this.embla.scrollTo(0);
            }
        },

        goTo(page) {
            this.pauseAutoplayForInteraction();
            this.embla?.scrollTo(page);
        },

        handleUserInteraction() {
            this.pauseAutoplayForInteraction();
        },

        syncCurrent() {
            this.current = this.embla?.selectedScrollSnap() || 0;
        },

        startAutoplay() {
            if (!this.autoplay || this.autoplayPaused || !this.embla || this.pageCount < 2) return;

            this.stopAutoplay();
            this.autoplayTimer = window.setInterval(() => this.advanceToNextSlide(), this.interval);
        },

        stopAutoplay() {
            if (this.autoplayTimer) {
                window.clearInterval(this.autoplayTimer);
                this.autoplayTimer = null;
            }
        },

        pauseAutoplayForInteraction() {
            if (!this.autoplay) return;

            this.autoplayPaused = true;
            this.stopAutoplay();

            if (this.autoplayResumeTimer) {
                window.clearTimeout(this.autoplayResumeTimer);
            }

            this.autoplayResumeTimer = window.setTimeout(() => {
                this.autoplayPaused = false;
                this.autoplayResumeTimer = null;
                this.startAutoplay();
            }, 5000);
        },

        destroy() {
            this.stopAutoplay();
            if (this.autoplayResumeTimer) {
                window.clearTimeout(this.autoplayResumeTimer);
                this.autoplayResumeTimer = null;
            }

            this.embla?.off('select', this.syncCurrent);
            this.embla?.off('reInit', this.syncCurrent);
            this.embla?.off('pointerDown', this.handleUserInteraction);
            this.viewport?.removeEventListener('wheel', this.handleUserInteraction);
            this.embla?.destroy();
            this.embla = null;
            this.viewport = null;
        },
    }));
});
