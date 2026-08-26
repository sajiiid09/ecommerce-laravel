<?php

namespace App\Livewire\Pages\Store;

use App\Models\Product as ProductModel;
use App\Services\CartService;
use App\Services\CatalogQueryService;
use App\Services\ReviewService;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Product extends Component
{
    public array $product;

    public int $reviewRating = 5;

    public string $reviewTitle = '';

    public string $reviewBody = '';

    protected CatalogQueryService $catalog;

    public function boot(CatalogQueryService $catalog): void
    {
        $this->catalog = $catalog;
    }

    public function mount(string $slug): void
    {
        $product = $this->catalog->product($slug);
        abort_unless($product, 404);
        $this->product = $product;
    }

    public function addToCart(int $variantId, int $quantity = 1): void
    {
        $carts = app(CartService::class);
        $carts->add($variantId, $quantity);
        $this->dispatch('cart-updated', items: $carts->present());
        $this->dispatch('open-cart');
        session()->flash('status', 'Product added to your cart.');
    }

    public function submitReview(ReviewService $reviews): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login');

            return;
        }

        $data = $this->validate([
            'reviewRating' => ['required', 'integer', 'between:1,5'],
            'reviewTitle' => ['nullable', 'string', 'max:255'],
            'reviewBody' => ['required', 'string', 'min:10', 'max:5000'],
        ]);
        $reviews->submit(ProductModel::findOrFail($this->product['id']), auth()->user(), [
            'rating' => $data['reviewRating'],
            'title' => $data['reviewTitle'],
            'review' => $data['reviewBody'],
        ]);
        $this->reset(['reviewTitle', 'reviewBody']);
        $this->reviewRating = 5;
        session()->flash('review_status', 'Thanks! Your review is awaiting approval.');
    }

    public function render()
    {
        $related = $this->catalog->products(['category' => $this->product['categorySlug'] ?? null], 6)
            ->getCollection()
            ->reject(fn (array $product): bool => $product['id'] === $this->product['id'])
            ->take(6);

        $reviewSummary = ['average' => 0, 'count' => 0];
        $reviews = collect();
        if (config('features.reviews') && Schema::hasTable('products')) {
            $reviewProduct = ProductModel::find($this->product['id']);
            if ($reviewProduct) {
                $reviewSummary = app(ReviewService::class)->summary($reviewProduct);
                $reviews = app(ReviewService::class)->approved($reviewProduct);
            }
        }

        return view('pages.store.product', compact('related', 'reviewSummary', 'reviews') + ['product' => $this->product]);
    }
}
