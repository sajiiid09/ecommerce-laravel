<?php

namespace App\Livewire\Pages\Store;

use App\Services\CartService;
use App\Services\CatalogQueryService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Product extends Component
{
    public array $product;

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
        $this->dispatch('open-cart');
        session()->flash('status', 'Product added to your cart.');
    }

    public function buyNow(int $variantId, int $quantity = 1): void
    {
        app(CartService::class)->add($variantId, $quantity);
        $this->redirect(route('store.checkout'), navigate: true);
    }

    public function render()
    {
        return view('pages.store.product', ['product' => $this->product]);
    }
}
