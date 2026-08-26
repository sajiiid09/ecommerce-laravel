<?php

namespace App\Livewire\Components\Store;

use App\Services\CartService;
use App\Support\StorefrontDemoData;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\On;
use Livewire\Component;

class CartDrawer extends Component
{
    #[On('add-to-cart')]
    public function addToCart(int $variantId, int $quantity, CartService $carts): void
    {
        $carts->add($variantId, $quantity);
        $this->dispatchUpdated($carts);
        $this->dispatch('open-cart');
    }

    public function updateItem(int $itemId, int $quantity, CartService $carts): void
    {
        $carts->updateQuantity($itemId, $quantity);
        $this->dispatchUpdated($carts);
    }

    public function removeItem(int $itemId, CartService $carts): void
    {
        $carts->remove($itemId);
        $this->dispatchUpdated($carts);
    }

    public function render()
    {
        if (! Schema::hasTable('carts')) {
            $items = collect(StorefrontDemoData::cartItems())->map(fn (array $item): array => [
                ...$item,
                'id' => $item['id'],
                'variant_id' => null,
                'product_id' => $item['id'],
                'sku' => null,
                'variant' => null,
                'price' => $item['price'] * 100,
                'quantity' => $item['quantity'],
                'line_total' => $item['price'] * 100 * $item['quantity'],
            ])->all();

            return view('livewire.components.store.cart-drawer', ['items' => $items, 'subtotal' => collect($items)->sum('line_total')]);
        }

        $carts = app(CartService::class);
        $cart = $carts->current();

        return view('livewire.components.store.cart-drawer', ['items' => $carts->present($cart), 'subtotal' => $carts->subtotal($cart)]);
    }

    private function dispatchUpdated(CartService $carts): void
    {
        $cart = $carts->current();
        $this->dispatch('cart-updated', items: $carts->present($cart));
    }
}
