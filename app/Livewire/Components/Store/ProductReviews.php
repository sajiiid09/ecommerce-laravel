<?php

namespace App\Livewire\Components\Store;

use App\Models\Product as ProductModel;
use App\Services\ReviewService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class ProductReviews extends Component
{
    public int $productId = 0;

    public int $reviewRating = 5;

    public string $reviewBody = '';

    public function mount(int $productId): void
    {
        $this->productId = $productId;
    }

    public function placeholder(): View
    {
        return view('livewire.components.store.product-reviews-placeholder');
    }

    public function submitReview(ReviewService $reviews): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login');

            return;
        }

        $data = $this->validate([
            'reviewRating' => ['required', 'integer', 'between:1,5'],
            'reviewBody' => ['nullable', 'string', 'min:10', 'max:5000'],
        ]);

        $reviews->submit(ProductModel::findOrFail($this->productId), auth()->user(), [
            'rating' => $data['reviewRating'],
            'review' => $data['reviewBody'],
        ]);

        $this->reset('reviewBody');
        $this->reviewRating = 5;
        session()->flash('review_status', 'Thanks! Your review has been published.');
    }

    public function setReviewRating(int $rating): void
    {
        $this->reviewRating = $rating;
    }

    public function render(): View
    {
        $reviewSummary = ['average' => 0, 'count' => 0];
        $reviews = new Collection;

        if (config('features.reviews') && Schema::hasTable('product_reviews')) {
            $product = ProductModel::find($this->productId);

            if ($product) {
                $reviewService = app(ReviewService::class);
                $reviewSummary = $reviewService->summary($product);
                $reviews = $reviewService->approved($product);
            }
        }

        return view('livewire.components.store.product-reviews', compact('reviewSummary', 'reviews'));
    }
}
