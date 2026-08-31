<?php

namespace App\Services;

use App\Enums\ProductStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function current(): Cart
    {
        $userId = auth()->id();
        $sessionToken = $this->sessionToken();

        $cart = Cart::query()
            ->where('status', 'active')
            ->when($userId, fn ($query) => $query->where('user_id', $userId), fn ($query) => $query->where('session_token', $sessionToken))
            ->latest('id')
            ->first();

        if (! $cart) {
            $cart = Cart::create([
                'user_id' => $userId,
                'session_token' => $userId ? null : $sessionToken,
                'status' => 'active',
            ]);
        }

        return $this->load($cart);
    }

    public function add(int $variantId, int $quantity = 1): Cart
    {
        $this->ensurePositiveQuantity($quantity);
        $variant = $this->availableVariant($variantId);
        $cart = $this->current();
        $item = $cart->items()->firstOrNew(['product_variant_id' => $variant->id]);
        $nextQuantity = (int) $item->quantity + $quantity;

        $this->ensureAvailable($variant, $nextQuantity);

        $item->quantity = $nextQuantity;
        $item->save();

        return $this->load($cart);
    }

    public function updateQuantity(int $itemId, int $quantity): Cart
    {
        $this->ensurePositiveQuantity($quantity);
        $cart = $this->current();
        $item = $cart->items()->with('variant.inventory')->findOrFail($itemId);
        $this->ensureAvailable($item->variant, $quantity);
        $item->update(['quantity' => $quantity]);

        return $this->load($cart);
    }

    public function remove(int $itemId): Cart
    {
        $cart = $this->current();
        $cart->items()->whereKey($itemId)->delete();

        return $this->load($cart);
    }

    public function clear(): Cart
    {
        $cart = $this->current();
        $cart->items()->delete();

        return $this->load($cart);
    }

    public function merge(?User $user = null): Cart
    {
        $user ??= auth()->user();
        if (! $user) {
            return $this->current();
        }

        $token = session()->get('cart_token');
        $guestCart = $token
            ? Cart::query()->where('session_token', $token)->where('status', 'active')->with('items')->first()
            : null;
        $customerCart = Cart::query()->whereBelongsTo($user)->where('status', 'active')->with('items')->latest('id')->first();

        if (! $customerCart) {
            $customerCart = $guestCart ?: Cart::create(['user_id' => $user->id, 'status' => 'active']);
            $customerCart->forceFill(['user_id' => $user->id, 'session_token' => null])->save();
        } elseif ($guestCart && $guestCart->isNot($customerCart)) {
            foreach ($guestCart->items as $guestItem) {
                $customerItem = $customerCart->items->firstWhere('product_variant_id', $guestItem->product_variant_id);
                $quantity = (int) ($customerItem?->quantity ?? 0) + (int) $guestItem->quantity;
                $variant = ProductVariant::with('inventory')->find($guestItem->product_variant_id);

                if (! $variant) {
                    continue;
                }

                $available = $this->availableQuantity($variant);
                $quantity = $available === null ? $quantity : min($quantity, $available);

                if ($quantity > 0) {
                    $customerCart->items()->updateOrCreate(
                        ['product_variant_id' => $variant->id],
                        ['quantity' => $quantity],
                    );
                }
            }

            $guestCart->update(['status' => 'converted']);
        }

        session()->forget('cart_token');

        return $this->load($customerCart);
    }

    public function subtotal(?Cart $cart = null): int
    {
        $cart ??= $this->current();

        return $cart->items->sum(fn (CartItem $item): int => $item->variant->currentPriceMinor() * (int) $item->quantity);
    }

    public function validate(?Cart $cart = null): Cart
    {
        $cart = $this->load($cart ?? $this->current());

        if ($cart->items->isEmpty()) {
            throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);
        }

        foreach ($cart->items as $item) {
            $variant = $item->variant;

            if (! $variant || ! $variant->is_active || $variant->product?->status !== ProductStatus::Published) {
                throw ValidationException::withMessages(['cart' => 'One or more cart items are no longer available.']);
            }

            $this->ensureAvailable($variant, (int) $item->quantity);
        }

        return $cart;
    }

    public function markConverted(?Cart $cart = null): void
    {
        ($cart ?? $this->current())->update(['status' => 'converted']);
        session()->forget('cart_token');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function present(?Cart $cart = null): array
    {
        $cart = $this->load($cart ?? $this->current());

        return $cart->items->map(fn (CartItem $item): array => [
            'id' => $item->id,
            'variant_id' => $item->product_variant_id,
            'product_id' => $item->variant->product_id,
            'name' => $item->variant->product->name,
            'variant' => $item->variant->name,
            'sku' => $item->variant->sku,
            'image' => $item->variant->media->first()?->asset?->url()
                ?? $item->variant->product->media->first()?->asset?->url()
                ?? asset('images/placeholders/no-image.svg'),
            'price' => $item->variant->currentPriceMinor(),
            'old_price' => $item->variant->compareAtPriceMinor(),
            'quantity' => (int) $item->quantity,
            'line_total' => $item->variant->currentPriceMinor() * (int) $item->quantity,
        ])->all();
    }

    private function load(Cart $cart): Cart
    {
        return $cart->load([
            'items.variant.product',
            'items.variant.inventory',
            'items.variant.media.asset',
            'items.variant.product.media.asset',
        ]);
    }

    private function availableVariant(int $variantId): ProductVariant
    {
        $variant = ProductVariant::query()->with(['product', 'inventory'])->find($variantId);

        if (! $variant || ! $variant->is_active || $variant->product?->status !== ProductStatus::Published) {
            throw (new ModelNotFoundException)->setModel(ProductVariant::class, [$variantId]);
        }

        return $variant;
    }

    private function ensureAvailable(ProductVariant $variant, int $quantity): void
    {
        $available = $this->availableQuantity($variant);

        if ($available !== null && $available < $quantity && ! $variant->inventory?->allow_backorders) {
            throw ValidationException::withMessages(['cart' => "Only {$available} item(s) are available for {$variant->product->name}."]);
        }
    }

    private function availableQuantity(ProductVariant $variant): ?int
    {
        return $variant->inventory?->track_quantity ? $variant->availableQuantity() : null;
    }

    private function sessionToken(): string
    {
        return session()->get('cart_token') ?? tap(Str::random(48), fn (string $token) => session()->put('cart_token', $token));
    }

    private function ensurePositiveQuantity(int $quantity): void
    {
        if ($quantity < 1) {
            throw ValidationException::withMessages(['quantity' => 'Quantity must be greater than zero.']);
        }
    }
}
