<section class="mt-8 rounded-card border border-store-border bg-white p-5">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h2 class="text-xl font-extrabold tracking-tight text-store-ink">Customer reviews</h2>
            <p class="mt-1 text-sm text-store-muted">{{ $reviewSummary['count'] }} reviews · {{ number_format($reviewSummary['average'], 1) }}/5 average rating</p>
        </div>
    </div>

    @if (session('review_status'))
        <p class="mt-4 rounded-control bg-green-50 p-3 text-sm text-green-700">{{ session('review_status') }}</p>
    @endif

    <div class="mt-5 grid gap-5 lg:grid-cols-[minmax(0,1fr)_320px]">
        <div data-review-list class="scrollbar-hidden max-h-[30rem] space-y-4 overflow-y-auto pr-2" tabindex="0" aria-label="Customer reviews">
            @forelse ($reviews as $review)
                <article data-review-item wire:key="product-review-{{ $review->id }}" class="border-b border-store-border pb-4 last:border-0">
                    <div class="flex items-center justify-between gap-3">
                        <p class="font-bold text-store-ink">{{ $review->name }}</p>
                        <span class="text-sm text-store-warning">{{ str_repeat('★', $review->rating) }}</span>
                    </div>
                    @if (filled($review->review))
                        <p class="mt-1 text-sm leading-6 text-store-text">{{ $review->review }}</p>
                    @endif
                    @if ($review->is_verified_purchase)
                        <span class="mt-2 inline-block text-xs font-bold text-store-success">Verified purchase</span>
                    @endif
                </article>
            @empty
                <p class="text-sm text-store-muted">No approved reviews yet.</p>
            @endforelse
        </div>

        <form wire:submit="submitReview" class="rounded-control bg-store-soft p-4">
            <h3 class="font-bold text-store-ink">Write a review</h3>
            @guest
                <p class="mt-2 text-sm text-store-muted">Please <a href="{{ route('login') }}" wire:navigate class="font-semibold text-store-blue">sign in</a> to review this product.</p>
            @else
                <fieldset class="mt-4">
                    <legend class="text-sm font-semibold text-store-ink">Rating <span class="text-red-600">*</span></legend>
                    <div class="mt-2 flex items-center gap-1" role="radiogroup" aria-label="Product rating">
                        @foreach (range(1, 5) as $rating)
                            <button
                                type="button"
                                wire:click="setReviewRating({{ $rating }})"
                                @class([
                                    'text-2xl leading-none transition hover:scale-110 focus:outline-none focus:ring-2 focus:ring-store-blue/40',
                                    'text-store-warning' => $reviewRating >= $rating,
                                    'text-store-muted' => $reviewRating < $rating,
                                ])
                                aria-label="{{ $rating }} out of 5 stars"
                                aria-pressed="{{ $reviewRating === $rating ? 'true' : 'false' }}"
                            >{{ $reviewRating >= $rating ? '★' : '☆' }}</button>
                        @endforeach
                    </div>
                    @error('reviewRating') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                </fieldset>
                <label class="mt-3 block text-sm font-semibold text-store-ink">Review <span class="font-normal text-store-muted">(optional)</span>
                    <textarea wire:model="reviewBody" rows="4" class="mt-2 w-full rounded-control border border-store-border px-3 py-2"></textarea>
                    @error('reviewBody') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                </label>
                <button type="submit" class="mt-4 h-10 w-full rounded-control bg-store-blue text-sm font-bold text-white">Submit Review</button>
            @endguest
        </form>
    </div>
</section>
