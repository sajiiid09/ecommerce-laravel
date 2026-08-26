<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommerceSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::updateOrCreate(['email' => 'customer@storez.local'], [
            'name' => 'StoreZ Demo Customer',
            'phone' => '01700000000',
            'password' => 'change-me-local',
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        $products = Product::query()->with(['defaultVariant', 'variants'])->published()->orderBy('id')->take(3)->get();
        if ($products->isEmpty()) {
            return;
        }

        $definitions = [
            ['number' => 'SZ-DEMO-PENDING', 'status' => 'pending', 'payment_status' => 'unpaid', 'product' => $products[0]],
            ['number' => 'SZ-DEMO-COMPLETED', 'status' => 'completed', 'payment_status' => 'paid', 'product' => $products[1] ?? $products[0]],
            ['number' => 'SZ-DEMO-CANCELLED', 'status' => 'cancelled', 'payment_status' => 'unpaid', 'product' => $products[2] ?? $products[0]],
        ];

        foreach ($definitions as $definition) {
            $product = $definition['product'];
            $variant = $product->defaultVariant ?: $product->variants->first();
            if (! $variant) {
                continue;
            }

            $price = $variant->currentPriceMinor();
            $order = Order::updateOrCreate(['order_number' => $definition['number']], [
                'user_id' => $customer->id,
                'checkout_token' => 'demo-'.$definition['number'],
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'customer_phone' => $customer->phone,
                'status' => $definition['status'],
                'payment_status' => $definition['payment_status'],
                'currency' => 'BDT',
                'subtotal_minor' => $price,
                'shipping_minor' => 0,
                'discount_minor' => 0,
                'tax_minor' => 0,
                'total_minor' => $price,
                'delivery_method' => 'standard',
                'payment_method' => 'cod',
                'placed_at' => now()->subDays($definition['status'] === 'pending' ? 1 : 7),
            ]);

            $item = $order->items()->updateOrCreate(['product_variant_id' => $variant->id], [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'variant_name' => $variant->name,
                'sku' => $variant->sku,
                'unit_price_minor' => $price,
                'quantity' => 1,
                'line_total_minor' => $price,
                'product_snapshot' => ['name' => $product->name, 'variant' => $variant->name, 'sku' => $variant->sku, 'price_minor' => $price],
            ]);

            OrderAddress::updateOrCreate(['order_id' => $order->id, 'type' => 'shipping'], [
                'name' => $customer->name,
                'phone' => $customer->phone,
                'address_line' => '12 Demo Street',
                'city' => 'Dhaka',
                'district' => 'Dhaka',
                'postal_code' => '1205',
                'country' => 'BD',
            ]);

            $order->statusHistory()->delete();
            $order->statusHistory()->create(['to_status' => 'pending', 'note' => 'Demo order seeded.', 'created_at' => now()->subDays(7)]);
            if ($definition['status'] !== 'pending') {
                $order->statusHistory()->create(['from_status' => 'pending', 'to_status' => $definition['status'], 'note' => 'Demo status seeded.', 'created_at' => now()->subDays(2)]);
            }

            Payment::updateOrCreate(['order_id' => $order->id], [
                'provider' => 'cod',
                'method' => 'cod',
                'status' => $definition['payment_status'] === 'paid' ? 'paid' : 'pending',
                'amount_minor' => $price,
                'currency' => 'BDT',
                'metadata' => ['demo' => true],
                'paid_at' => $definition['payment_status'] === 'paid' ? now()->subDays(2) : null,
            ]);

            if ($definition['status'] === 'completed') {
                ProductReview::updateOrCreate(['product_id' => $product->id, 'user_id' => $customer->id], [
                    'order_item_id' => $item->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'rating' => 5,
                    'title' => 'Great demo product',
                    'review' => 'This seeded review demonstrates the approved customer feedback flow.',
                    'status' => 'approved',
                    'is_verified_purchase' => true,
                    'approved_at' => now()->subDay(),
                ]);
            }
        }
    }
}
