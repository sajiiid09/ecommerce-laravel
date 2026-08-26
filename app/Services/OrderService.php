<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(
        private readonly CartService $carts,
        private readonly InventoryService $inventory,
        private readonly PaymentManager $payments,
    ) {}

    /**
     * @param  array{customer_name: string, customer_email: string, customer_phone?: string|null, address_line: string, city: string, district?: string|null, postal_code?: string|null, country?: string, delivery_method: string, payment_method: string, checkout_token: string}  $data
     */
    public function place(array $data, ?User $user = null): Order
    {
        $existing = Order::query()->where('checkout_token', $data['checkout_token'])->first();
        if ($existing) {
            return $existing->load(['items', 'shippingAddress', 'payment']);
        }

        if (! $this->payments->supports($data['payment_method'])) {
            throw ValidationException::withMessages(['payment_method' => 'The selected payment method is unavailable.']);
        }

        return DB::transaction(function () use ($data, $user): Order {
            $cart = $this->carts->validate();
            $shippingMinor = $data['delivery_method'] === 'express' ? 6000 : 0;
            $subtotalMinor = $this->carts->subtotal($cart);

            $order = Order::create([
                'order_number' => $this->nextOrderNumber(),
                'user_id' => $user?->id ?? auth()->id(),
                'checkout_token' => $data['checkout_token'],
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'customer_phone' => $data['customer_phone'] ?? null,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'currency' => 'BDT',
                'subtotal_minor' => $subtotalMinor,
                'shipping_minor' => $shippingMinor,
                'discount_minor' => 0,
                'tax_minor' => 0,
                'total_minor' => $subtotalMinor + $shippingMinor,
                'delivery_method' => $data['delivery_method'],
                'payment_method' => $data['payment_method'],
                'placed_at' => now(),
            ]);

            foreach ($cart->items as $cartItem) {
                $variant = $cartItem->variant->fresh(['product', 'inventory', 'media.asset', 'product.media.asset']);
                $quantity = (int) $cartItem->quantity;
                $unitPrice = $variant->currentPriceMinor();
                $this->inventory->sell($variant, $quantity, 'order', $order->id);
                $order->items()->create([
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $variant->product->name,
                    'variant_name' => $variant->name,
                    'sku' => $variant->sku,
                    'unit_price_minor' => $unitPrice,
                    'quantity' => $quantity,
                    'line_total_minor' => $unitPrice * $quantity,
                    'product_snapshot' => ['name' => $variant->product->name, 'variant' => $variant->name, 'sku' => $variant->sku, 'price_minor' => $unitPrice],
                ]);
            }

            $order->shippingAddress()->create([
                'type' => 'shipping',
                'name' => $data['customer_name'],
                'phone' => $data['customer_phone'] ?? '',
                'address_line' => $data['address_line'],
                'city' => $data['city'],
                'district' => $data['district'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'country' => $data['country'] ?? 'BD',
            ]);
            $order->statusHistory()->create(['to_status' => 'pending', 'note' => 'Order placed.']);
            $this->payments->create($order, $data['payment_method']);
            $this->carts->markConverted($cart);

            return $order->load(['items', 'shippingAddress', 'payment']);
        });
    }

    public function transition(Order $order, string $status, ?string $note = null): Order
    {
        $allowed = ['pending' => ['processing', 'completed', 'cancelled'], 'processing' => ['completed', 'cancelled'], 'completed' => [], 'cancelled' => []];
        if (! in_array($status, $allowed[$order->status] ?? [], true)) {
            throw ValidationException::withMessages(['status' => 'This order status transition is not allowed.']);
        }

        return DB::transaction(function () use ($order, $status, $note): Order {
            $fromStatus = $order->status;
            if ($status === 'cancelled') {
                $order->load('items.variant');
                foreach ($order->items as $item) {
                    if ($item->variant) {
                        $this->inventory->restore($item->variant, $item->quantity, 'order', $order->id);
                    }
                }
            }
            $attributes = ['status' => $status];
            if ($status === 'completed') {
                $attributes['payment_status'] = 'paid';
                $order->payment()->update(['status' => 'paid', 'paid_at' => now()]);
            }
            $order->update($attributes);
            $order->statusHistory()->create(['from_status' => $fromStatus, 'to_status' => $status, 'note' => $note, 'changed_by' => auth()->id()]);

            return $order->fresh(['items', 'shippingAddress', 'statusHistory', 'payment']);
        });
    }

    private function nextOrderNumber(): string
    {
        do {
            $number = 'SZ-'.Str::upper(Str::random(8));
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }
}
