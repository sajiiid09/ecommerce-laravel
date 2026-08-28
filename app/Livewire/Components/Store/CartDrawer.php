<?php

namespace App\Livewire\Components\Store;

use App\Services\CartService;
use App\Support\StorefrontDemoData;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\On;
use Livewire\Component;

class CartDrawer extends Component
{
    public bool $loaded = false;

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $items = [];

    public int $subtotal = 0;

    #[On('cart-initialized')]
    #[On('cart-opened')]
    public function loadCart(CartService $carts): void
    {
        if ($this->loaded) {
            return;
        }

        $this->loaded = true;
        $this->refreshCart($carts);
        $this->dispatch('cart-updated', items: $this->items);
    }

    #[On('add-to-cart')]
    public function addToCart(int $variantId, int $quantity, CartService $carts): void
    {
        $carts->add($variantId, $quantity);
        $this->loaded = true;
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
        return view('livewire.components.store.cart-drawer');
    }

    private function dispatchUpdated(CartService $carts): void
    {
        $this->refreshCart($carts);
        $this->dispatch('cart-updated', items: $this->items);
    }

    private function refreshCart(CartService $carts): void
    {
        if (! Schema::hasTable('carts')) {
            $this->items = collect(StorefrontDemoData::cartItems())->map(fn (array $item): array => [
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
            $this->subtotal = collect($this->items)->sum('line_total');

            return;
        }

        $cart = $carts->current();
        $this->items = $carts->present($cart);
        $this->subtotal = $carts->subtotal($cart);
    }
}
