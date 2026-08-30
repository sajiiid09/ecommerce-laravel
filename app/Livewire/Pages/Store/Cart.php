<?php

namespace App\Livewire\Pages\Store;

use App\Services\CartService;
use App\Support\StorefrontDemoData;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Cart extends Component
{
    public function updateItem(int $itemId, int $quantity, string $direction, CartService $carts): void
    {
        $currentItem = $carts->current()->items->firstWhere('id', $itemId);
        $currentQuantity = (int) ($currentItem?->quantity ?? $quantity);

        $carts->updateQuantity($itemId, $quantity);
        $this->dispatchUpdated($carts);
        $this->dispatchQuantityNotification($currentQuantity, $quantity, $direction);
    }

    public function removeItem(int $itemId, CartService $carts): void
    {
        $carts->remove($itemId);
        $this->dispatchUpdated($carts);
    }

    public function clearCart(CartService $carts): void
    {
        $carts->clear();
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

            return view('pages.store.cart-content', ['items' => $items, 'subtotal' => collect($items)->sum('line_total')]);
        }

        $carts = app(CartService::class);
        $cart = $carts->current();

        return view('pages.store.cart-content', ['items' => $carts->present($cart), 'subtotal' => $carts->subtotal($cart)]);
    }

    private function dispatchUpdated(CartService $carts): void
    {
        $cart = $carts->current();
        $this->dispatch('cart-updated', items: $carts->present($cart));
    }

    private function dispatchQuantityNotification(int $currentQuantity, int $quantity, string $direction): void
    {
        if ($direction === 'decrease' && $currentQuantity === $quantity) {
            $this->dispatch('notify', content: 'Quantity is already at the minimum of 1.', type: 'warning');

            return;
        }

        $message = match ($direction) {
            'increase' => "Quantity increased to {$quantity}.",
            'decrease' => "Quantity decreased to {$quantity}.",
            default => "Quantity updated to {$quantity}.",
        };

        $this->dispatch('notify', content: $message, type: $direction === 'decrease' ? 'error' : 'success');
    }
}
