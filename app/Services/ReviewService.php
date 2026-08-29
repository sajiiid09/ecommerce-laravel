<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class ReviewService
{
    public function __construct(private readonly CatalogCache $cache) {}

    public function submit(Product $product, User $user, array $data): ProductReview
    {
        if (ProductReview::query()->where('product_id', $product->id)->where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages(['review' => 'You have already reviewed this product.']);
        }

        $orderItem = OrderItem::query()
            ->where('product_id', $product->id)
            ->whereHas('order', fn ($query) => $query->where('user_id', $user->id)->where('status', 'completed'))
            ->latest('id')
            ->first();

        $review = ProductReview::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'order_item_id' => $orderItem?->id,
            'name' => $user->name,
            'email' => $user->email,
            'rating' => (int) $data['rating'],
            'title' => $data['title'] ?? null,
            'review' => filled($data['review'] ?? null) ? $data['review'] : null,
            'status' => 'approved',
            'approved_at' => now(),
            'is_verified_purchase' => $orderItem !== null,
        ]);

        $this->forget($product);

        return $review;
    }

    public function approve(ProductReview $review): ProductReview
    {
        $review->update(['status' => 'approved', 'approved_at' => now()]);
        $this->forget($review->product);

        return $review->fresh();
    }

    public function reject(ProductReview $review): ProductReview
    {
        $review->update(['status' => 'rejected', 'approved_at' => null]);
        $this->forget($review->product);

        return $review->fresh();
    }

    public function delete(ProductReview $review): void
    {
        $product = $review->product()->first();
        $review->delete();

        if ($product) {
            $this->forget($product);
        }
    }

    public function summary(Product $product): array
    {
        return Cache::remember($this->cache->reviewSummary($product->id), 600, function () use ($product): array {
            $query = $product->reviews()->where('status', 'approved');

            return ['average' => round((float) $query->avg('rating'), 1), 'count' => $query->count()];
        });
    }

    public function approved(Product $product): Collection
    {
        $attributes = Cache::remember($this->cache->approvedReviews($product->id), 600, function () use ($product): array {
            return $product->reviews()
                ->where('status', 'approved')
                ->latest('approved_at')
                ->get()
                ->map(fn (ProductReview $review): array => $review->getAttributes())
                ->all();
        });

        return ProductReview::hydrate($attributes);
    }

    private function forget(Product $product): void
    {
        $this->cache->forgetProduct($product->slug, $product->id);
    }
}
