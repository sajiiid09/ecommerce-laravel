<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class CommerceSeeder extends Seeder
{
    public function run(): void
    {
        $customers = $this->seedCustomers();
        $verifiedItems = $this->seedOrders($customers);
        $this->seedReviews($customers, $verifiedItems);
    }

    /**
     * Create realistic demo customers. No avatar/profile images are seeded.
     */
    private function seedCustomers(): Collection
    {
        $definitions = [
            ['email' => 'customer@storez.local', 'name' => 'Nusrat Jahan', 'phone' => '01711001001'],
            ['email' => 'rafiq.ahmed@storez.local', 'name' => 'Rafiq Ahmed', 'phone' => '01812002002'],
            ['email' => 'tania.rahman@storez.local', 'name' => 'Tania Rahman', 'phone' => '01913003003'],
            ['email' => 'farhan.kabir@storez.local', 'name' => 'Farhan Kabir', 'phone' => '01614004004'],
            ['email' => 'maliha.sultana@storez.local', 'name' => 'Maliha Sultana', 'phone' => '01715005005'],
            ['email' => 'imran.hossain@storez.local', 'name' => 'Imran Hossain', 'phone' => '01816006006'],
            ['email' => 'sadia.islam@storez.local', 'name' => 'Sadia Islam', 'phone' => '01917007007'],
            ['email' => 'mehedi.hasan@storez.local', 'name' => 'Mehedi Hasan', 'phone' => '01618008008'],
            ['email' => 'ayesha.akter@storez.local', 'name' => 'Ayesha Akter', 'phone' => '01719009009'],
            ['email' => 'tanvir.chowdhury@storez.local', 'name' => 'Tanvir Chowdhury', 'phone' => '01820010010'],
            ['email' => 'sharmeen.akter@storez.local', 'name' => 'Sharmeen Akter', 'phone' => '01921011011'],
            ['email' => 'arif.mahmud@storez.local', 'name' => 'Arif Mahmud', 'phone' => '01622012012'],
        ];

        return collect($definitions)->map(function (array $data): User {
            return User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'password' => Hash::make('change-me-local'),
                    'is_admin' => false,
                    'email_verified_at' => now(),
                ],
            );
        });
    }

    /**
     * Keep three compact order examples for pending/completed/cancelled states.
     *
     * @return array<int, array<int, int>>
     */
    private function seedOrders(Collection $customers): array
    {
        $products = Product::query()
            ->with(['defaultVariant', 'variants'])
            ->published()
            ->orderBy('id')
            ->take(3)
            ->get();

        if ($products->isEmpty()) {
            return [];
        }

        $definitions = [
            ['number' => 'SZ-DEMO-PENDING', 'status' => 'pending', 'payment_status' => 'unpaid', 'product' => $products[0], 'customer' => $customers[0]],
            ['number' => 'SZ-DEMO-COMPLETED', 'status' => 'completed', 'payment_status' => 'paid', 'product' => $products[1] ?? $products[0], 'customer' => $customers[1] ?? $customers[0]],
            ['number' => 'SZ-DEMO-CANCELLED', 'status' => 'cancelled', 'payment_status' => 'unpaid', 'product' => $products[2] ?? $products[0], 'customer' => $customers[2] ?? $customers[0]],
        ];

        $verifiedItems = [];

        foreach ($definitions as $definition) {
            $product = $definition['product'];
            $customer = $definition['customer'];
            $variant = $product->defaultVariant ?: $product->variants->first();

            if (! $variant) {
                continue;
            }

            $price = $variant->currentPriceMinor();

            $order = Order::updateOrCreate(
                ['order_number' => $definition['number']],
                [
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
                ],
            );

            $item = $order->items()->updateOrCreate(
                ['product_variant_id' => $variant->id],
                [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'variant_name' => $variant->name,
                    'sku' => $variant->sku,
                    'unit_price_minor' => $price,
                    'quantity' => 1,
                    'line_total_minor' => $price,
                    'product_snapshot' => [
                        'name' => $product->name,
                        'variant' => $variant->name,
                        'sku' => $variant->sku,
                        'price_minor' => $price,
                    ],
                ],
            );

            OrderAddress::updateOrCreate(
                ['order_id' => $order->id, 'type' => 'shipping'],
                [
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'address_line' => 'House 18, Road 7, Dhanmondi',
                    'city' => 'Dhaka',
                    'district' => 'Dhaka',
                    'postal_code' => '1205',
                    'country' => 'BD',
                ],
            );

            $order->statusHistory()->delete();
            $order->statusHistory()->create([
                'to_status' => 'pending',
                'note' => 'Demo order seeded.',
                'created_at' => now()->subDays(7),
            ]);

            if ($definition['status'] !== 'pending') {
                $order->statusHistory()->create([
                    'from_status' => 'pending',
                    'to_status' => $definition['status'],
                    'note' => 'Demo status seeded.',
                    'created_at' => now()->subDays(2),
                ]);
            }

            Payment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'provider' => 'cod',
                    'method' => 'cod',
                    'status' => $definition['payment_status'] === 'paid' ? 'paid' : 'pending',
                    'amount_minor' => $price,
                    'currency' => 'BDT',
                    'metadata' => ['demo' => true],
                    'paid_at' => $definition['payment_status'] === 'paid' ? now()->subDays(2) : null,
                ],
            );

            if ($definition['status'] === 'completed') {
                $verifiedItems[$product->id][$customer->id] = $item->id;
            }
        }

        return $verifiedItems;
    }

    /**
     * Seed exactly three realistic, approved reviews for every published product.
     */
    private function seedReviews(Collection $customers, array $verifiedItems): void
    {
        if ($customers->count() < 3) {
            return;
        }

        $products = Product::query()->published()->orderBy('id')->get();
        $reviewTemplates = [
            ['rating' => 5, 'title' => 'Very satisfied', 'review' => 'The product arrived in good condition and matched the description.'],
            ['rating' => 4, 'title' => 'Good overall', 'review' => 'It has been reliable in regular use and the packaging was fine.'],
            ['rating' => 4, 'title' => 'Worth considering', 'review' => 'It offers good value for everyday use.'],
        ];

        foreach ($products as $productIndex => $product) {
            foreach ($reviewTemplates as $reviewIndex => $reviewData) {
                $customer = $customers[($productIndex + $reviewIndex) % $customers->count()];
                $orderItemId = $verifiedItems[$product->id][$customer->id] ?? null;

                ProductReview::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'user_id' => $customer->id,
                    ],
                    [
                        'order_item_id' => $orderItemId,
                        'name' => $customer->name,
                        'email' => $customer->email,
                        'rating' => $reviewData['rating'],
                        'title' => $reviewData['title'],
                        'review' => $product->name.' '.$reviewData['review'],
                        'status' => 'approved',
                        'is_verified_purchase' => $orderItemId !== null,
                        'approved_at' => now()->subDays(2 + (($productIndex * 3 + $reviewIndex) % 28)),
                    ],
                );
            }
        }
    }
}
