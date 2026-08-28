<?php

use App\Livewire\Pages\Account\Addresses;
use App\Livewire\Pages\Store\Checkout;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\UserAddress;
use App\Services\CartService;
use App\Services\ProductService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

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
        ->set('district', 'Dhanmondi')
        ->set('postalCode', '1205')
        ->call('saveAddress');
    $home = $customer->addresses()->where('label', 'Home')->firstOrFail();

    $component
        ->call('openCreate')
        ->set('label', 'Office')
        ->set('recipientName', 'Address Customer')
        ->set('phone', '01700000000')
        ->set('addressLine', 'Office 3, Gulshan')
        ->set('city', 'Dhaka')
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
    ]);
    $product = addressBookProduct();
    $this->actingAs($customer);
    app(CartService::class)->add($product->defaultVariant->id);

    $checkout = Livewire::actingAs($customer)->test(Checkout::class);

    $checkout
        ->assertSet('selectedAddressId', $address->id)
        ->assertSee('Trusted House 9', false)
        ->call('useNewAddress')
        ->assertSet('selectedAddressId', null)
        ->set('customer_name', 'Manual Recipient')
        ->set('customer_phone', '01900000000')
        ->set('address_line', 'Manual House 10')
        ->set('city', 'Dhaka')
        ->set('district', 'Mirpur')
        ->set('checkout_token', (string) Str::uuid())
        ->call('placeOrder');

    $order = Order::query()->latest('id')->firstOrFail();
    $snapshot = $order->shippingAddress;
    $address->update(['address_line' => 'Changed after checkout']);

    expect($snapshot->address_line)->toBe('Manual House 10')
        ->and($order->fresh()->shippingAddress->address_line)->toBe('Manual House 10');
});

it('rejects a saved address id that is not owned by the authenticated customer', function () {
    $customer = User::factory()->create();
    $otherCustomer = User::factory()->create();
    $otherAddress = UserAddress::factory()->for($otherCustomer)->create();

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

it('keeps guest checkout on manual address fields', function () {
    $this->get(route('store.checkout'))
        ->assertSuccessful()
        ->assertSee('Customer and delivery address', false)
        ->assertDontSee('Saved addresses', false)
        ->assertSee('Full name', false);
});
test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
