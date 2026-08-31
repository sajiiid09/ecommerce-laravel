<?php

namespace App\Livewire\Components\Store;

use App\Services\CartService;
use App\Support\StorefrontDemoData;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;
use Throwable;

class CartDrawer extends Component
{
    public bool $loaded = false;

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $items = [];

    public int $subtotal = 0;

    #[On('cart-initialized')]
    public function initializeCart(CartService $carts): void
    {
        $this->loaded = true;
        $this->refreshCart($carts);
        $this->dispatch('cart-updated', items: $this->items);
    }

    #[On('cart-opened')]
    public function loadCart(CartService $carts): void
    {
        if ($this->loaded) {
            return;
        }

        $this->initializeCart($carts);
    }

    #[On('add-to-cart')]
    public function addToCart(int $variantId, int $quantity, CartService $carts): void
    {
        try {
            $carts->add($variantId, $quantity);
            $this->loaded = true;
            $this->dispatchUpdated($carts);
            $this->dispatch('cart-item-added', variant_id: $variantId, quantity: $quantity);
            $this->dispatch('open-cart');
        } catch (Throwable $exception) {
            report($exception);
            $this->loaded = true;
            $this->refreshCart($carts);
            $this->dispatch('cart-updated', items: $this->items);
            $this->dispatch('cart-add-failed', message: $this->cartAddFailureMessage($exception));
            $this->dispatch('open-cart');
        }
    }

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
                'old_price' => isset($item['oldPrice']) ? $item['oldPrice'] * 100 : null,
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

    private function cartAddFailureMessage(Throwable $exception): string
    {
        if ($exception instanceof ValidationException) {
            $message = collect($exception->errors())->flatten()->first();

            if (filled($message)) {
                return (string) $message;
            }
        }

        if ($exception instanceof ModelNotFoundException) {
            return 'This product is no longer available.';
        }

        return 'Unable to add this product to your cart.';
    }
}
