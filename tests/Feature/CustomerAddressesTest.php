<?php

use App\Livewire\Pages\Account\Addresses;
use App\Livewire\Pages\Store\Checkout;
use App\Models\District;
use App\Models\MediaAsset;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductMedia;
use App\Models\User;
use App\Models\UserAddress;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\ProductService;
use Database\Seeders\DistrictSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DistrictSeeder::class);
});

function addressBookProduct(): Product
{
    return app(ProductService::class)->save([
        'name' => 'Address Book Product',
        'product_type' => 'simple',
        'status' => 'published',
        'visibility' => 'visible',
        'regular_price_minor' => 2500,
        'inventory_quantity' => 5,
    ])->load('defaultVariant');
}

it('redirects guests and shows authenticated customers their address book', function () {
    $this->get(route('account.addresses'))->assertRedirect(route('login'));

    $customer = User::factory()->create();
    $address = UserAddress::factory()->for($customer)->create([
        'label' => 'Home',
        'recipient_name' => 'Address Customer',
        'address_line' => 'House 1, Road 2',
    ]);

    $this->actingAs($customer)
        ->get(route('account.addresses'))
        ->assertSuccessful()
        ->assertSee('Addresses', false)
        ->assertSee('Home', false)
        ->assertSee('House 1, Road 2', false)
        ->assertSee('href="'.route('account.addresses').'"', false)
        ->assertSee('aria-current="page"', false);

    expect($address->fresh()->is_default)->toBeTrue();
});

it('validates and manages multiple customer addresses with one default', function () {
    $customer = User::factory()->create();
    $component = Livewire::actingAs($customer)->test(Addresses::class);

    $component->call('saveAddress')->assertHasErrors([
        'label', 'recipientName', 'phone', 'addressLine', 'city',
    ]);

    $component
        ->set('label', 'Home')
        ->set('recipientName', 'Address Customer')
        ->set('phone', '01700000000')
        ->set('addressLine', 'House 1, Road 2')
        ->set('city', 'Dhaka')
        ->set('districtId', District::query()->where('name', 'Dhaka')->value('id'))
        ->set('postalCode', '1205')
        ->call('saveAddress');
    $home = $customer->addresses()->where('label', 'Home')->firstOrFail();
    expect($home->district_id)->toBe(District::query()->where('name', 'Dhaka')->value('id'));

    $component
        ->call('openCreate')
        ->set('label', 'Office')
        ->set('recipientName', 'Address Customer')
        ->set('phone', '01700000000')
        ->set('addressLine', 'Office 3, Gulshan')
        ->set('city', 'Dhaka')
        ->set('districtId', District::query()->where('name', 'Dhaka')->value('id'))
        ->set('isDefault', true)
        ->call('saveAddress');
    $office = $customer->addresses()->where('label', 'Office')->firstOrFail();

    expect($customer->addresses()->count())->toBe(2)
        ->and($home->fresh()->is_default)->toBeFalse()
        ->and($office->fresh()->is_default)->toBeTrue();

    $component->call('openEdit', $office->id)->set('label', 'Office updated')->call('saveAddress');
    expect($office->fresh()->label)->toBe('Office updated');

    $component->call('deleteAddress', $office->id);

    expect($home->fresh()->is_default)->toBeTrue()
        ->and($customer->addresses()->count())->toBe(1);
});

it('does not allow a customer to access another customer address', function () {
    $customer = User::factory()->create();
    $otherCustomer = User::factory()->create();
    $otherAddress = UserAddress::factory()->for($otherCustomer)->create();

    expect(fn () => Livewire::actingAs($customer)->test(Addresses::class)->call('openEdit', $otherAddress->id))
        ->toThrow(ModelNotFoundException::class);
});

it('selects a saved checkout address and preserves its order snapshot', function () {
    $customer = User::factory()->create(['name' => 'Checkout Customer', 'email' => 'checkout@example.test']);
    $address = UserAddress::factory()->for($customer)->create([
        'label' => 'Home',
        'recipient_name' => 'Trusted Recipient',
        'phone' => '01800000000',
        'address_line' => 'Trusted House 9',
        'city' => 'Dhaka',
        'district' => 'Uttara',
        'district_id' => District::query()->where('name', 'Dhaka')->value('id'),
    ]);
    $product = addressBookProduct();
    $this->actingAs($customer);
    app(CartService::class)->add($product->defaultVariant->id);

    $checkout = Livewire::actingAs($customer)->test(Checkout::class);

    $checkout
        ->assertSet('selectedAddressId', $address->id)
        ->assertSet('districtId', $address->district_id)
        ->assertSee('Trusted House 9', false)
        ->set('delivery_method', 'express')
        ->set('payment_method', 'cod')
        ->call('useNewAddress')
        ->assertSet('selectedAddressId', null)
        ->assertSet('delivery_method', 'express')
        ->assertSet('payment_method', 'cod')
        ->set('customer_name', 'Manual Recipient')
        ->set('customer_phone', '01900000000')
        ->set('address_line', 'Manual House 10')
        ->set('city', 'Dhaka')
        ->set('districtId', District::query()->where('name', 'Dhaka')->value('id'))
        ->set('checkout_token', (string) Str::uuid())
        ->call('placeOrder');

    $order = Order::query()->latest('id')->firstOrFail();
    $snapshot = $order->shippingAddress;
    $address->update(['address_line' => 'Changed after checkout']);

    expect($snapshot->address_line)->toBe('Manual House 10')
        ->and($order->fresh()->shippingAddress->address_line)->toBe('Manual House 10');
});

it('normalizes legacy JSON district values in saved addresses', function () {
    $customer = User::factory()->create();
    $district = District::query()->where('name', 'Thakurgaon')->firstOrFail();
    $address = UserAddress::factory()->for($customer)->create([
        'district' => json_encode($district->toArray(), JSON_THROW_ON_ERROR),
        'district_id' => null,
    ]);

    Livewire::actingAs($customer)
        ->test(Addresses::class)
        ->call('openEdit', $address->id)
        ->assertSet('district', 'Thakurgaon')
        ->assertSet('districtId', $district->id)
        ->assertSee('Thakurgaon', false)
        ->call('saveAddress')
        ->assertHasNoErrors()
        ->call('openCreate')
        ->assertSet('district', '')
        ->assertSet('districtId', null)
        ->assertDontSee('Legacy district', false);

    expect($address->fresh()->district_id)->toBe($district->id)
        ->and($address->fresh()->district)->toBe('Thakurgaon');
});

it('renders address choices with a shared loading lock', function () {
    $customer = User::factory()->create();
    UserAddress::factory()->for($customer)->count(2)->create();
    $product = addressBookProduct();

    $this->actingAs($customer);
    app(CartService::class)->add($product->defaultVariant->id);

    $this->get(route('store.checkout'))
        ->assertSuccessful()
        ->assertSee('addressSelectionPending', false)
        ->assertSee('x-bind:disabled="addressSelectionPending"', false)
        ->assertSee('wire:target="selectAddress,useNewAddress"', false)
        ->assertSee('aria-busy', false)
        ->assertSee('wire:key="checkout-address-new"', false)
        ->assertSee('Loading address…', false);
});

it('does not send state changes when selecting the current address again', function () {
    $customer = User::factory()->create();
    $address = UserAddress::factory()->for($customer)->create([
        'address_line' => 'Current saved address',
        'district_id' => District::query()->where('name', 'Dhaka')->value('id'),
    ]);
    $product = addressBookProduct();

    $this->actingAs($customer);
    app(CartService::class)->add($product->defaultVariant->id);

    Livewire::actingAs($customer)
        ->test(Checkout::class)
        ->call('selectAddress', $address->id)
        ->set('delivery_method', 'express')
        ->set('payment_method', 'cod')
        ->call('selectAddress', $address->id)
        ->assertSet('selectedAddressId', $address->id)
        ->assertSet('address_line', 'Current saved address')
        ->assertSet('delivery_method', 'express')
        ->assertSet('payment_method', 'cod');
});

it('renders selected address choices as disabled and other choices as actionable', function () {
    $customer = User::factory()->create();
    $district = District::query()->where('name', 'Barishal')->firstOrFail();
    $selectedAddress = UserAddress::factory()->for($customer)->create([
        'is_default' => true,
        'address_line' => 'Selected House',
        'city' => 'Barishal',
        'district_id' => $district->id,
    ]);
    $otherAddress = UserAddress::factory()->for($customer)->create([
        'district_id' => District::query()->where('name', 'Dhaka')->value('id'),
    ]);
    $product = addressBookProduct();

    $this->actingAs($customer);
    app(CartService::class)->add($product->defaultVariant->id);

    $this->get(route('store.checkout'))
        ->assertSuccessful()
        ->assertSee('wire:key="checkout-address-'.$selectedAddress->id.'"', false)
        ->assertSee('Selected House, Barishal, Barishal', false)
        ->assertSee('aria-pressed="true"', false)
        ->assertDontSee('x-on:click="selectSavedAddress('.$selectedAddress->id.')"', false)
        ->assertSee('x-on:click="selectSavedAddress('.$otherAddress->id.')"', false)
        ->assertSee('x-bind:disabled="addressSelectionPending"', false);
});

it('renders the new address choice as disabled when it is already selected', function () {
    $customer = User::factory()->create();
    UserAddress::factory()->for($customer)->create();
    $product = addressBookProduct();

    $this->actingAs($customer);
    app(CartService::class)->add($product->defaultVariant->id);

    Livewire::actingAs($customer)
        ->test(Checkout::class)
        ->call('useNewAddress')
        ->assertSee('wire:key="checkout-address-new"', false)
        ->assertSee('aria-pressed="true"', false)
        ->assertDontSee('x-on:click="useNewAddress()"', false);
});

it('rejects a saved address id that is not owned by the authenticated customer', function () {
    $customer = User::factory()->create();
    $otherCustomer = User::factory()->create();
    $otherAddress = UserAddress::factory()->for($otherCustomer)->create();
    $this->actingAs($customer);
    $product = addressBookProduct();
    app(CartService::class)->add($product->defaultVariant->id);

    Livewire::actingAs($customer)
        ->test(Checkout::class)
        ->set('selectedAddressId', $otherAddress->id)
        ->call('nextStep')
        ->assertHasErrors('selectedAddressId');
});

it('uses the trusted saved address when placing an authenticated order', function () {
    $customer = User::factory()->create(['email' => 'trusted-checkout@example.test']);
    $address = UserAddress::factory()->for($customer)->create([
        'recipient_name' => 'Saved Recipient',
        'phone' => '01711111111',
        'address_line' => 'Saved House 4',
        'city' => 'Dhaka',
    ]);
    $product = addressBookProduct();
    $this->actingAs($customer);
    app(CartService::class)->add($product->defaultVariant->id);

    Livewire::actingAs($customer)
        ->test(Checkout::class)
        ->set('checkout_token', (string) Str::uuid())
        ->call('placeOrder');

    $order = Order::query()->latest('id')->firstOrFail();
    $snapshot = $order->shippingAddress;
    $address->update(['address_line' => 'Updated saved address']);

    expect($snapshot->name)->toBe('Saved Recipient')
        ->and($snapshot->phone)->toBe('01711111111')
        ->and($order->fresh()->shippingAddress->address_line)->toBe('Saved House 4');
});

it('flashes a success toast after a COD checkout', function () {
    $customer = User::factory()->create();
    $product = addressBookProduct();
    $this->actingAs($customer);
    app(CartService::class)->add($product->defaultVariant->id);

    $checkout = Livewire::actingAs($customer)
        ->test(Checkout::class)
        ->set('customer_name', $customer->name)
        ->set('customer_phone', '01700000000')
        ->set('address_line', 'House 1, Road 2')
        ->set('city', 'Dhaka')
        ->set('districtId', District::query()->where('name', 'Dhaka')->value('id'))
        ->set('checkout_token', (string) Str::uuid())
        ->call('placeOrder');

    $order = Order::query()->latest('id')->firstOrFail();

    $checkout->assertRedirect(route('store.order-success', ['order' => $order->order_number]));
    expect(session('notify'))->toEqual([
        'content' => 'Order placed successfully.',
        'type' => 'success',
    ]);
});

it('keeps guest checkout on manual address fields', function () {
    $product = addressBookProduct();
    app(CartService::class)->add($product->defaultVariant->id);

    $this->get(route('store.checkout'))
        ->assertSuccessful()
        ->assertSee('Customer and delivery address', false)
        ->assertDontSee('Saved addresses', false)
        ->assertSee('Full name', false)
        ->assertSee('Select district', false)
        ->assertSee('wire:model.live.debounce.300ms="districtId"', false)
        ->assertSee('wire:target="districtId"', false)
        ->assertSee('class="text-red-600" aria-hidden="true">*</span>', false);
});

it('uses managed district fees for standard and express orders and snapshots the name', function () {
    $customer = User::factory()->create();
    $district = District::query()->where('name', 'Dhaka')->firstOrFail();
    $district->update(['delivery_fee_minor' => 12500]);
    $product = addressBookProduct();

    $this->actingAs($customer);
    app(CartService::class)->add($product->defaultVariant->id);
    $standard = app(OrderService::class)->place([
        'customer_name' => 'District Customer', 'customer_email' => $customer->email, 'customer_phone' => '01700000000',
        'address_line' => 'House 1', 'city' => 'Dhaka', 'district_id' => $district->id,
        'district' => $district->name, 'delivery_method' => 'standard', 'payment_method' => 'cod', 'checkout_token' => (string) Str::uuid(),
    ], $customer);

    app(CartService::class)->add($product->defaultVariant->id);
    $express = app(OrderService::class)->place([
        'customer_name' => 'District Customer', 'customer_email' => $customer->email, 'customer_phone' => '01700000000',
        'address_line' => 'House 1', 'city' => 'Dhaka', 'district_id' => $district->id,
        'district' => $district->name, 'delivery_method' => 'express', 'payment_method' => 'cod', 'checkout_token' => (string) Str::uuid(),
    ], $customer);

    expect($standard->shipping_minor)->toBe(12500)
        ->and($standard->shippingAddress->district)->toBe('Dhaka')
        ->and($express->shipping_minor)->toBe(18500);
});

it('redirects to the homepage when checkout is opened with an empty cart', function () {
    $this->get(route('store.checkout'))
        ->assertRedirect(route('store.home'));
});

it('requires the checkout customer and address fields before continuing', function () {
    $product = addressBookProduct();
    app(CartService::class)->add($product->defaultVariant->id);

    Livewire::test(Checkout::class)
        ->set('customer_email', 'checkout@example.test')
        ->call('nextStep')
        ->assertHasErrors(['customer_name', 'customer_phone', 'districtId', 'district', 'address_line'])
        ->assertSet('step', 1);
});

it('allows checkout to continue without an email address', function () {
    $product = addressBookProduct();
    app(CartService::class)->add($product->defaultVariant->id);

    Livewire::test(Checkout::class)
        ->set('customer_name', 'Checkout Customer')
        ->set('customer_phone', '01700000000')
        ->set('districtId', District::query()->where('name', 'Dhaka')->value('id'))
        ->set('address_line', 'House 1, Road 2')
        ->call('nextStep')
        ->assertHasNoErrors()
        ->assertSet('step', 2);
});

it('provides guarded checkout navigation while preserving entered state', function () {
    $product = addressBookProduct();
    app(CartService::class)->add($product->defaultVariant->id);
    $districtId = District::query()->where('name', 'Dhaka')->value('id');

    $checkout = Livewire::test(Checkout::class)
        ->assertSee('Back to Cart')
        ->assertSee('Continue to Delivery')
        ->set('customer_name', 'Checkout Customer')
        ->set('customer_phone', '01700000000')
        ->set('districtId', $districtId)
        ->set('address_line', 'House 1, Road 2')
        ->call('goToStep', 3)
        ->assertSet('step', 1)
        ->call('nextStep')
        ->assertSet('step', 2)
        ->assertSee('Continue to Payment')
        ->assertSee('Back')
        ->call('selectDeliveryMethod', 'express')
        ->assertSee('Express Delivery · ৳140.00', false)
        ->assertSee('has-[:checked]:border-2', false)
        ->call('goToStep', 1)
        ->assertSet('step', 1)
        ->assertSet('customer_name', 'Checkout Customer')
        ->assertSet('districtId', $districtId)
        ->call('goToStep', 2)
        ->assertSet('step', 2)
        ->call('nextStep')
        ->assertSet('step', 3)
        ->assertSee('Continue to Review')
        ->call('nextStep')
        ->assertSet('step', 4)
        ->assertSee('Place Order');

    $checkout->call('goToStep', 2)
        ->assertSet('step', 2)
        ->assertSet('address_line', 'House 1, Road 2');
});

it('defers delivery method updates until checkout advances and previews the shipping amount locally', function () {
    $product = addressBookProduct();
    app(CartService::class)->add($product->defaultVariant->id);
    $districtId = District::query()->where('name', 'Dhaka')->value('id');

    $checkout = Livewire::test(Checkout::class)
        ->set('customer_name', 'Checkout Customer')
        ->set('customer_phone', '01700000000')
        ->set('districtId', $districtId)
        ->set('address_line', 'House 1, Road 2')
        ->call('nextStep')
        ->assertSet('step', 2)
        ->assertSet('delivery_method', 'standard')
        ->assertSee('Standard Delivery · ৳80.00', false)
        ->assertSee('Express Delivery · ৳140.00', false)
        ->assertSee('wire:model="delivery_method"', false)
        ->assertSee('x-on:change="deliveryMethod = $event.target.value"', false)
        ->assertSee('shippingAmount', false)
        ->assertDontSee('$wire.selectDeliveryMethod', false)
        ->assertDontSee('wire:model.live="delivery_method"', false)
        ->call('selectDeliveryMethod', 'express')
        ->assertSet('delivery_method', 'express')
        ->assertSee('Express Delivery · ৳140.00', false)
        ->call('selectDeliveryMethod', 'express')
        ->assertSet('delivery_method', 'express');

    $checkout->call('selectDeliveryMethod', 'standard')
        ->assertSet('delivery_method', 'standard')
        ->assertSee('Standard Delivery · ৳80.00', false);
});

it('rejects unsupported delivery methods without changing the selected method', function () {
    $product = addressBookProduct();
    app(CartService::class)->add($product->defaultVariant->id);

    Livewire::test(Checkout::class)
        ->call('selectDeliveryMethod', 'overnight')
        ->assertHasErrors('delivery_method')
        ->assertSet('delivery_method', 'standard');
});

it('shows the first product image in the checkout order summary', function () {
    $product = addressBookProduct();
    $firstImage = MediaAsset::create([
        'disk' => 'public',
        'path' => 'products/first.jpg',
        'filename' => 'first.jpg',
        'mime_type' => 'image/jpeg',
        'size' => 1,
    ]);
    $secondImage = MediaAsset::create([
        'disk' => 'public',
        'path' => 'products/second.jpg',
        'filename' => 'second.jpg',
        'mime_type' => 'image/jpeg',
        'size' => 1,
    ]);
    ProductMedia::create([
        'product_id' => $product->id,
        'media_asset_id' => $firstImage->id,
        'role' => 'main',
        'sort_order' => 0,
    ]);
    ProductMedia::create([
        'product_id' => $product->id,
        'media_asset_id' => $secondImage->id,
        'role' => 'gallery',
        'sort_order' => 1,
    ]);

    $this->actingAs(User::factory()->create());
    app(CartService::class)->add($product->defaultVariant->id);

    Livewire::test(Checkout::class)
        ->assertSee('src="'.$firstImage->url().'"', false)
        ->assertSee('alt="Address Book Product"', false);
});

it('renders the order success page with the resolved order model', function () {
    $customer = User::factory()->create(['name' => 'Success Customer']);
    $product = addressBookProduct();
    $this->actingAs($customer);
    app(CartService::class)->add($product->defaultVariant->id);

    $order = app(OrderService::class)->place([
        'customer_name' => $customer->name,
        'customer_email' => $customer->email,
        'customer_phone' => '01700000000',
        'address_line' => 'House 1, Road 2',
        'city' => 'Dhaka',
        'district' => 'Dhanmondi',
        'delivery_method' => 'standard',
        'payment_method' => 'cod',
        'checkout_token' => (string) Str::uuid(),
    ], $customer);

    $this->get(route('store.order-success', ['order' => $order->order_number]))
        ->assertSuccessful()
        ->assertSee('Thank you, Success Customer.', false)
        ->assertSee($order->order_number, false);
});
test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
