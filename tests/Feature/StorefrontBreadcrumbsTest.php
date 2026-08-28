<?php

use App\Models\User;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('renders Sheaf breadcrumbs on every customer-facing breadcrumb page', function () {
    $product = app(ProductService::class)->save([
        'name' => 'Breadcrumb Product',
        'product_type' => 'simple',
        'status' => 'published',
        'visibility' => 'visible',
        'regular_price_minor' => 2500,
        'inventory_quantity' => 5,
    ])->load('defaultVariant');

    $pages = [
        [
            'url' => route('store.category'),
            'current' => 'All Products',
            'links' => [route('store.home')],
        ],
        [
            'url' => route('store.search'),
            'current' => 'Search',
            'links' => [route('store.home')],
        ],
        [
            'url' => route('store.product', ['slug' => $product->slug]),
            'current' => $product->name,
            'links' => [route('store.home'), route('store.category')],
        ],
        [
            'url' => route('store.cart'),
            'current' => 'Shopping Cart',
            'links' => [route('store.home')],
        ],
        [
            'url' => route('store.checkout'),
            'current' => 'Checkout',
            'links' => [route('store.home'), route('store.cart')],
        ],
    ];

    foreach ($pages as $page) {
        $response = $this->get($page['url'])
            ->assertSuccessful()
            ->assertSee('aria-label="Breadcrumb"', false)
            ->assertSee('group/breadcrumbs', false)
            ->assertSee('aria-current="page"', false)
            ->assertSee($page['current'], false);

        foreach ($page['links'] as $link) {
            $response->assertSee('href="'.$link.'"', false);
        }
    }

    $customer = User::factory()->create();
    $this->actingAs($customer);
    session()->put('cart_token', 'breadcrumb-order-token');
    app(CartService::class)->add($product->defaultVariant->id);

    $order = app(OrderService::class)->place([
        'customer_name' => $customer->name,
        'customer_email' => $customer->email,
        'customer_phone' => '01700000000',
        'address_line' => 'Breadcrumb address',
        'city' => 'Dhaka',
        'delivery_method' => 'standard',
        'payment_method' => 'cod',
        'checkout_token' => (string) Str::uuid(),
    ], $customer);

    $this->get(route('account.order', ['order' => $order->order_number]))
        ->assertSuccessful()
        ->assertSee('aria-label="Breadcrumb"', false)
        ->assertSee('group/breadcrumbs', false)
        ->assertSee('aria-current="page"', false)
        ->assertSee($order->order_number, false)
        ->assertSee('href="'.route('store.home').'"', false)
        ->assertSee('href="'.route('account.orders').'"', false);
});
