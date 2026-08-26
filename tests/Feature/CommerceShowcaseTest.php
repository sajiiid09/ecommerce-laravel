<?php

use App\Livewire\Pages\Admin\Orders\Show as AdminOrderShow;
use App\Livewire\Pages\Admin\Settings\Payments as AdminPaymentSettings;
use App\Livewire\Pages\Auth\Login;
use App\Livewire\Pages\Auth\Register;
use App\Models\Cart;
use App\Models\PaymentProviderCredential;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PaymentManager;
use App\Services\ProductService;
use App\Services\ReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function commerceProduct(array $attributes = []): Product
{
    return app(ProductService::class)->save(array_merge([
        'name' => 'Showcase Product',
        'product_type' => 'simple',
        'status' => 'published',
        'visibility' => 'visible',
        'regular_price_minor' => 2500,
        'inventory_quantity' => 5,
    ], $attributes))->load('defaultVariant');
}

it('registers a customer and protects account routes', function () {
    $this->get('/account')->assertRedirect(route('login'));

    Livewire::test(Register::class)
        ->set('name', 'Demo Customer')
        ->set('email', 'customer@example.test')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->call('register')
        ->assertRedirect(route('account.dashboard'));

    $this->assertAuthenticated();
    expect(User::where('email', 'customer@example.test')->exists())->toBeTrue();
});

it('authenticates customers, throttles invalid logins, and supports logout', function () {
    $user = User::factory()->create(['email' => 'login@example.test', 'password' => 'password123']);

    for ($attempt = 0; $attempt < 5; $attempt++) {
        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'incorrect-password')
            ->call('login');
    }

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'incorrect-password')
        ->call('login')
        ->assertHasErrors('email');

    $secondUser = User::factory()->create(['email' => 'fresh-login@example.test', 'password' => 'password123']);
    Livewire::test(Login::class)
        ->set('email', $secondUser->email)
        ->set('password', 'password123')
        ->call('login')
        ->assertRedirect(url('/account'));

    $this->post(route('logout'))->assertRedirect(route('login'));
    $this->assertGuest();
});

it('renders login and registration forms with Sheaf UI inputs, revealable passwords, and buttons', function () {
    $login = $this->get(route('login'))->assertSuccessful()->getContent();
    $register = $this->get(route('register'))->assertSuccessful()->getContent();

    expect(substr_count($login, 'data-slot="control"'))->toBe(2)
        ->and(substr_count($login, 'data-slot="button"'))->toBe(1)
        ->and(substr_count($login, 'x-bind:aria-label="revealed'))->toBe(1)
        ->and(substr_count($register, 'data-slot="control"'))->toBe(4)
        ->and(substr_count($register, 'data-slot="button"'))->toBe(1)
        ->and(substr_count($register, 'x-bind:aria-label="revealed'))->toBe(2);
});

it('persists a guest cart, places an idempotent COD order, and restores cancelled stock', function () {
    $product = commerceProduct();
    $variant = $product->defaultVariant;
    session()->put('cart_token', 'guest-cart-token');
    $carts = app(CartService::class);

    $carts->add($variant->id, 2);
    expect(Cart::where('session_token', 'guest-cart-token')->firstOrFail()->items)->toHaveCount(1);

    $data = [
        'customer_name' => 'Guest Customer',
        'customer_email' => 'guest@example.test',
        'customer_phone' => '01700000000',
        'address_line' => 'House 12, Road 7',
        'city' => 'Dhaka',
        'district' => 'Dhanmondi',
        'postal_code' => '1205',
        'country' => 'BD',
        'delivery_method' => 'standard',
        'payment_method' => 'cod',
        'checkout_token' => (string) Str::uuid(),
    ];
    $orders = app(OrderService::class);
    $order = $orders->place($data);
    $duplicate = $orders->place($data);

    expect($duplicate->id)->toBe($order->id)
        ->and($order->payment->status)->toBe('pending')
        ->and($order->payment_status)->toBe('unpaid')
        ->and($order->total_minor)->toBe(5000)
        ->and($variant->fresh('inventory')->inventory->quantity_on_hand)->toBe(3);

    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin);
    $orders->transition($order, 'cancelled');

    expect($variant->fresh('inventory')->inventory->quantity_on_hand)->toBe(5)
        ->and($order->fresh()->status)->toBe('cancelled');
});

it('merges guest and customer carts by variant with stock revalidation', function () {
    $product = commerceProduct();
    $variant = $product->defaultVariant;
    $customer = User::factory()->create();
    $customerCart = Cart::create(['user_id' => $customer->id, 'status' => 'active']);
    $customerCart->items()->create(['product_variant_id' => $variant->id, 'quantity' => 2]);
    session()->put('cart_token', 'guest-merge-token');
    $guestCart = Cart::create(['session_token' => 'guest-merge-token', 'status' => 'active']);
    $guestCart->items()->create(['product_variant_id' => $variant->id, 'quantity' => 4]);

    $this->actingAs($customer);
    $merged = app(CartService::class)->merge($customer);

    expect($merged->items->first()->quantity)->toBe(5)
        ->and($guestCart->fresh()->status)->toBe('converted');
});

it('rejects unavailable cart quantities and supports authenticated reviews', function () {
    $product = commerceProduct(['inventory_quantity' => 1]);
    $variant = $product->defaultVariant;
    session()->put('cart_token', 'stock-cart-token');
    $carts = app(CartService::class);
    $carts->add($variant->id, 1);

    expect(fn () => $carts->add($variant->id, 1))->toThrow(ValidationException::class);

    $customer = User::factory()->create();
    $this->actingAs($customer);
    $reviews = app(ReviewService::class);
    $review = $reviews->submit($product, $customer, ['rating' => 5, 'title' => 'Great', 'review' => 'A useful showcase review.']);
    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin);
    $reviews->approve($review);

    expect($reviews->summary($product->fresh()))->toMatchArray(['average' => 5.0, 'count' => 1]);
});

it('scopes customer orders and transitions COD payment state for admins', function () {
    $product = commerceProduct();
    $customer = User::factory()->create();
    $this->actingAs($customer);
    session()->put('cart_token', 'customer-order-token');
    app(CartService::class)->add($product->defaultVariant->id);

    $order = app(OrderService::class)->place([
        'customer_name' => $customer->name,
        'customer_email' => $customer->email,
        'customer_phone' => '01700000000',
        'address_line' => 'Customer address',
        'city' => 'Dhaka',
        'delivery_method' => 'express',
        'payment_method' => 'cod',
        'checkout_token' => (string) Str::uuid(),
    ], $customer);

    $otherCustomer = User::factory()->create();
    $this->actingAs($otherCustomer)->get(route('account.order', $order->order_number))->assertNotFound();
    $this->actingAs($customer)->get('/account/orders/'.$order->order_number)->assertSuccessful();

    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin)->get('/admin/orders')->assertSuccessful();
    Livewire::test(AdminOrderShow::class, ['order' => $order->order_number]);
    $this->actingAs($admin)->get('/admin/orders/'.$order->order_number)->assertSuccessful();
    app(OrderService::class)->transition($order, 'completed');

    expect($order->fresh()->payment_status)->toBe('paid')
        ->and($order->fresh()->payment->status)->toBe('paid')
        ->and($order->shipping_minor)->toBe(6000);
});

it('renders real order tracking history for its customer', function () {
    $product = commerceProduct();
    $customer = User::factory()->create();
    $this->actingAs($customer);
    session()->put('cart_token', 'tracking-cart-token');
    app(CartService::class)->add($product->defaultVariant->id);
    $order = app(OrderService::class)->place([
        'customer_name' => $customer->name,
        'customer_email' => $customer->email,
        'customer_phone' => '01700000000',
        'address_line' => 'Tracking address',
        'city' => 'Dhaka',
        'delivery_method' => 'standard',
        'payment_method' => 'cod',
        'checkout_token' => (string) Str::uuid(),
    ], $customer);

    $this->get(route('account.tracking', $order->order_number))
        ->assertSuccessful()
        ->assertSee($order->order_number)
        ->assertSee('Pending');
});

it('hides disabled showcase controls while preserving working catalog pages', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get('/admin/catalog/products')
        ->assertSuccessful()
        ->assertDontSee('Import', false)
        ->assertDontSee('Export', false)
        ->assertDontSee('Top Categories', false)
        ->assertDontSee('Inventory Alerts', false)
        ->assertDontSee('Recently Added Products', false)
        ->assertDontSee('Total Product Value', false)
        ->assertDontSee('xl:grid-cols-[minmax(0,1fr)_280px]', false)
        ->assertSee('Products');

    config(['features.catalog_import_export' => true]);
    $this->actingAs($admin)->get('/admin/catalog/products')->assertSee('Import', false);
});

it('does not render the inactive admin header search control', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertSuccessful()
        ->assertDontSee('Search anything...')
        ->assertDontSee('Ctrl K')
        ->assertDontSee('Quick Add')
        ->assertDontSee('Notifications');
});

it('creates a Stripe Checkout session only when enabled and reconciles signed webhooks idempotently', function () {
    config([
        'services.stripe.api_url' => 'https://stripe.test',
    ]);
    PaymentProviderCredential::create([
        'provider' => 'stripe',
        'enabled' => true,
        'mode' => 'test',
        'credentials' => [
            'publishable_key' => 'pk_test_demo',
            'secret_key' => 'sk_test_demo',
            'webhook_secret' => 'whsec_demo',
        ],
    ]);
    Http::fake([
        'https://stripe.test/v1/checkout/sessions' => Http::response([
            'id' => 'cs_test_showcase',
            'url' => 'https://checkout.stripe.test/cs_test_showcase',
        ]),
        'https://stripe.test/v1/checkout/sessions/cs_test_showcase' => Http::response([
            'id' => 'cs_test_showcase',
            'payment_status' => 'paid',
            'metadata' => ['order_id' => '1'],
        ]),
    ]);

    $product = commerceProduct();
    $customer = User::factory()->create();
    $this->actingAs($customer);
    session()->put('cart_token', 'stripe-cart-token');
    app(CartService::class)->add($product->defaultVariant->id);

    $order = app(OrderService::class)->place([
        'customer_name' => $customer->name,
        'customer_email' => $customer->email,
        'customer_phone' => '01700000000',
        'address_line' => 'Stripe test address',
        'city' => 'Dhaka',
        'delivery_method' => 'standard',
        'payment_method' => 'stripe',
        'checkout_token' => (string) Str::uuid(),
    ], $customer);

    Http::assertSent(fn (HttpRequest $request): bool => $request->url() === 'https://stripe.test/v1/checkout/sessions'
        && $request->hasHeader('Idempotency-Key')
        && $request['mode'] === 'payment'
        && $request['line_items[0][price_data][currency]'] === 'bdt');

    expect($order->payment->provider)->toBe('stripe')
        ->and($order->payment->status)->toBe('pending')
        ->and($order->payment->metadata['checkout_url'])->toBe('https://checkout.stripe.test/cs_test_showcase');

    $payload = json_encode([
        'type' => 'checkout.session.completed',
        'data' => ['object' => [
            'id' => 'cs_test_showcase',
            'payment_status' => 'paid',
            'metadata' => ['order_id' => (string) $order->id],
        ]],
    ], JSON_THROW_ON_ERROR);
    $timestamp = now()->timestamp;
    $signature = 't='.$timestamp.',v1='.hash_hmac('sha256', $timestamp.'.'.$payload, 'whsec_demo');

    $this->call('POST', route('stripe.webhook'), [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_STRIPE_SIGNATURE' => $signature,
    ], $payload)->assertSuccessful();
    $this->call('POST', route('stripe.webhook'), [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_STRIPE_SIGNATURE' => $signature,
    ], $payload)->assertSuccessful();

    expect($order->fresh()->payment_status)->toBe('paid')
        ->and($order->fresh()->payment->status)->toBe('paid');

    $this->get(route('stripe.checkout.success', ['session_id' => 'cs_test_showcase']))
        ->assertRedirect(route('store.order-success', ['order' => $order->order_number]));
});

it('protects admin payment settings and stores Stripe secrets encrypted', function () {
    $this->get(route('admin.settings.payments'))->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create())->get(route('admin.settings.payments'))->assertForbidden();

    $admin = User::factory()->create(['is_admin' => true]);
    Livewire::actingAs($admin)
        ->test(AdminPaymentSettings::class)
        ->set('enabled', true)
        ->set('mode', 'test')
        ->set('publishable_key', 'pk_test_admin')
        ->set('secret_key', 'sk_test_admin')
        ->set('webhook_secret', 'whsec_admin')
        ->call('save')
        ->assertHasNoErrors();

    $rawCredentials = DB::table('payment_provider_credentials')->value('credentials');
    expect($rawCredentials)->not->toContain('sk_test_admin')
        ->and(PaymentProviderCredential::firstOrFail()->credentials['secret_key'])->toBe('sk_test_admin');

    $this->actingAs($admin)
        ->get(route('admin.settings.payments'))
        ->assertSuccessful()
        ->assertDontSee('sk_test_admin')
        ->assertSee('••••••••dmin');
});

it('preserves saved Stripe secrets when blank and supports clearing credentials', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    PaymentProviderCredential::create([
        'provider' => 'stripe',
        'enabled' => true,
        'mode' => 'test',
        'credentials' => [
            'secret_key' => 'sk_test_existing',
            'webhook_secret' => 'whsec_existing',
        ],
    ]);

    Livewire::actingAs($admin)
        ->test(AdminPaymentSettings::class)
        ->set('enabled', false)
        ->call('save')
        ->assertHasNoErrors();

    expect(PaymentProviderCredential::firstOrFail()->credentials['secret_key'])->toBe('sk_test_existing');

    Livewire::actingAs($admin)
        ->test(AdminPaymentSettings::class)
        ->set('clear_secret_key', true)
        ->call('save')
        ->assertHasNoErrors();

    expect(PaymentProviderCredential::firstOrFail()->credentials)->not->toHaveKey('secret_key');
});

it('keeps Stripe hidden until enabled with mode-matched credentials and tests the saved connection', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin);

    expect(app(PaymentManager::class)->available())->not->toHaveKey('stripe');

    Livewire::test(AdminPaymentSettings::class)
        ->set('enabled', true)
        ->set('mode', 'live')
        ->set('secret_key', 'sk_test_wrong_mode')
        ->set('webhook_secret', 'whsec_valid')
        ->call('save')
        ->assertHasErrors('secret_key');

    Livewire::test(AdminPaymentSettings::class)
        ->set('enabled', true)
        ->set('mode', 'test')
        ->set('secret_key', 'sk_test_connection')
        ->set('webhook_secret', 'whsec_connection')
        ->call('save')
        ->assertHasNoErrors();

    config(['services.stripe.api_url' => 'https://stripe.test']);
    Http::fake(['https://stripe.test/v1/account' => Http::response(['id' => 'acct_demo'])]);

    Livewire::test(AdminPaymentSettings::class)
        ->call('testConnection')
        ->assertHasNoErrors();

    expect(app(PaymentManager::class)->available())->toHaveKey('stripe');
});
