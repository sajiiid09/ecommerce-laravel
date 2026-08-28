<?php

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('keeps the account navigation consistent across authenticated storefront pages', function () {
    $customer = User::factory()->create(['name' => 'Account Customer']);
    $order = Order::create([
        'order_number' => 'SZ-ACCOUNT-001',
        'user_id' => $customer->id,
        'checkout_token' => 'account-navigation-token',
        'customer_name' => $customer->name,
        'customer_email' => $customer->email,
        'status' => 'pending',
        'payment_status' => 'unpaid',
        'currency' => 'BDT',
        'subtotal_minor' => 1000,
        'shipping_minor' => 0,
        'discount_minor' => 0,
        'tax_minor' => 0,
        'total_minor' => 1000,
        'delivery_method' => 'standard',
        'payment_method' => 'cod',
        'placed_at' => now(),
    ]);

    $this->actingAs($customer);

    $this->get(route('account.dashboard'))
        ->assertSuccessful()
        ->assertSee('aria-current="page"', false)
        ->assertSee('Account Overview', false)
        ->assertSee('Sign out', false)
        ->assertSee('store-account-menu', false);

    $this->get(route('account.orders'))
        ->assertSuccessful()
        ->assertSee('aria-current="page"', false)
        ->assertSee('My Orders', false)
        ->assertSee('Sign out', false);

    $this->get(route('account.order', ['order' => $order->order_number]))
        ->assertSuccessful()
        ->assertSee('aria-current="page"', false)
        ->assertSee('My Orders', false)
        ->assertSee($order->order_number, false);

    $this->get(route('account.tracking', ['order' => $order->order_number]))
        ->assertSuccessful()
        ->assertSee('aria-current="page"', false)
        ->assertSee('My Orders', false)
        ->assertSee($order->order_number, false);

    $this->get(route('store.wishlist'))
        ->assertSuccessful()
        ->assertSee('aria-current="page"', false)
        ->assertSee('Wishlist', false)
        ->assertSee('Sign out', false);
});

it('keeps the guest wishlist public without authenticated account navigation', function () {
    $this->get(route('store.wishlist'))
        ->assertSuccessful()
        ->assertSee('My Wishlist', false)
        ->assertDontSee('My account', false)
        ->assertDontSee('Sign out', false);
});

it('logs out customers through the shared storefront logout action', function () {
    $customer = User::factory()->create();

    $this->actingAs($customer)
        ->get(route('account.dashboard'))
        ->assertSuccessful()
        ->assertSee('action="'.route('logout').'"', false);

    $this->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});
