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
        <div class="space-y-4">
            @forelse ($reviews as $review)
                <article wire:key="product-review-{{ $review->id }}" class="border-b border-store-border pb-4 last:border-0">
                    <div class="flex items-center justify-between gap-3">
                        <p class="font-bold text-store-ink">{{ $review->name }}</p>
                        <span class="text-sm text-store-warning">{{ str_repeat('★', $review->rating) }}</span>
                    </div>
                    @if ($review->title)
                        <h3 class="mt-2 text-sm font-bold text-store-ink">{{ $review->title }}</h3>
                    @endif
                    <p class="mt-1 text-sm leading-6 text-store-text">{{ $review->review }}</p>
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
                <label class="mt-4 block text-sm font-semibold text-store-ink">Rating
                    <select wire:model="reviewRating" class="mt-2 h-10 w-full rounded-control border border-store-border px-3">
                        <option value="5">5 — Excellent</option>
                        <option value="4">4 — Good</option>
                        <option value="3">3 — Okay</option>
                        <option value="2">2 — Poor</option>
                        <option value="1">1 — Bad</option>
                    </select>
                </label>
                <label class="mt-3 block text-sm font-semibold text-store-ink">Title
                    <input wire:model="reviewTitle" class="mt-2 h-10 w-full rounded-control border border-store-border px-3">
                </label>
                <label class="mt-3 block text-sm font-semibold text-store-ink">Review
                    <textarea wire:model="reviewBody" rows="4" class="mt-2 w-full rounded-control border border-store-border px-3 py-2"></textarea>
                    @error('reviewBody') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                </label>
                <button type="submit" class="mt-4 h-10 w-full rounded-control bg-store-blue text-sm font-bold text-white">Submit Review</button>
            @endguest
        </form>
    </div>
</section>
