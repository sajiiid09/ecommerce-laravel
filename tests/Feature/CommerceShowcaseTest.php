<?php

use App\Livewire\Components\Store\CartDrawer;
use App\Livewire\Components\Store\ProductReviews;
use App\Livewire\Pages\Admin\Orders\Show as AdminOrderShow;
use App\Livewire\Pages\Admin\Settings\Payments as AdminPaymentSettings;
use App\Livewire\Pages\Auth\Login;
use App\Livewire\Pages\Auth\Register;
use App\Livewire\Pages\Store\Cart as StoreCart;
use App\Livewire\Pages\Store\Product as StoreProduct;
use App\Models\Cart;
use App\Models\Order;
use App\Models\PaymentProviderCredential;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use App\Services\CatalogCache;
use App\Services\CatalogQueryService;
use App\Services\OrderService;
use App\Services\PaymentManager;
use App\Services\ProductService;
use App\Services\ProductVariantService;
use App\Services\ReviewService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Cache;
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

it('removes cart lines for soft-deleted variants before presenting the cart', function () {
    $product = commerceProduct();
    $variant = $product->defaultVariant;
    session()->put('cart_token', 'stale-variant-cart-token');

    app(CartService::class)->add($variant->id);
    $variant->delete();

    $this->get(route('store.cart'))->assertSuccessful();

    expect(app(CartService::class)->present())->toBe([])
        ->and(app(CartService::class)->subtotal())->toBe(0)
        ->and(Cart::where('session_token', 'stale-variant-cart-token')->firstOrFail()->items)->toBeEmpty();

    Livewire::test(CartDrawer::class)
        ->call('loadCart')
        ->assertSet('items', [])
        ->assertSet('subtotal', 0);
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

    expect($review->status)->toBe('approved')
        ->and($review->approved_at)->not->toBeNull();

    $this->get(route('store.product', ['slug' => $product->slug]))
        ->assertSuccessful()
        ->assertSee('Rated 5 out of 5, 1 reviews', false)
        ->assertSee('A useful showcase review.', false);

    Livewire::withoutLazyLoading();
    Livewire::actingAs($customer)
        ->test(ProductReviews::class, ['productId' => $product->id])
        ->assertSee('A useful showcase review.', false)
        ->assertSee('1 reviews · 5.0/5 average rating', false);

    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin);
    $reviews->approve($review);

    expect($reviews->summary($product->fresh()))->toMatchArray(['average' => 5.0, 'count' => 1]);

    $this->get(route('store.category'))
        ->assertSuccessful()
        ->assertSee('Rated 5 out of 5, 1 reviews', false)
        ->assertSee('(1)', false);

    $this->get(route('store.product', ['slug' => $product->slug]))
        ->assertSuccessful()
        ->assertSee('Rated 5 out of 5, 1 reviews', false)
        ->assertSee('A useful showcase review.', false);

    $approvedReviewsKey = app(CatalogCache::class)->approvedReviews($product->id);
    expect(Cache::has($approvedReviewsKey))->toBeTrue();

    $reviews->reject($review);

    expect(Cache::has($approvedReviewsKey))->toBeFalse();
});

it('renders a scrollable review list and accepts star-only reviews without titles', function () {
    $product = commerceProduct();

    foreach (range(1, 5) as $index) {
        $reviewer = User::factory()->create(['name' => "Review Customer {$index}"]);
        ProductReview::create([
            'product_id' => $product->id,
            'user_id' => $reviewer->id,
            'name' => $reviewer->name,
            'email' => $reviewer->email,
            'rating' => 5,
            'title' => "Legacy title {$index}",
            'review' => "Review body {$index}.",
            'status' => 'approved',
            'approved_at' => now()->addSeconds($index),
        ]);
    }

    $customer = User::factory()->create();
    Livewire::withoutLazyLoading();
    $component = Livewire::actingAs($customer)->test(ProductReviews::class, ['productId' => $product->id]);
    $approvedReviews = app(ReviewService::class)->approved($product);

    $component
        ->assertSee('data-review-list', false)
        ->assertSee('scrollbar-hidden', false)
        ->assertSee('max-h-[30rem]', false)
        ->assertSee('overflow-y-auto', false)
        ->assertSee('data-review-item', false)
        ->assertSee('Review body 1.', false)
        ->assertSee('Review body 5.', false)
        ->assertSee('Review', false)
        ->assertSee('(optional)', false)
        ->assertDontSee('Legacy title 1', false)
        ->assertDontSee('wire:model="reviewTitle"', false)
        ->assertSee('aria-label="1 out of 5 stars"', false);

    expect($approvedReviews)->toBeInstanceOf(Collection::class)
        ->and(Cache::get(app(CatalogCache::class)->approvedReviews($product->id)))->toBeArray()->toHaveCount(5);

    $component
        ->call('setReviewRating', 3)
        ->assertSet('reviewRating', 3)
        ->assertSee('text-store-muted', false)
        ->assertSee('aria-pressed="true"', false)
        ->set('reviewBody', '')
        ->call('submitReview');

    $starOnlyReview = ProductReview::query()->where('user_id', $customer->id)->firstOrFail();
    expect($starOnlyReview->rating)->toBe(3)
        ->and($starOnlyReview->review)->toBeNull()
        ->and($starOnlyReview->title)->toBeNull();

    Livewire::actingAs(User::factory()->create())->test(ProductReviews::class, ['productId' => $product->id])
        ->set('reviewRating', 6)
        ->call('submitReview')
        ->assertHasErrors(['reviewRating']);
});

it('starts loading cart contents after storefront initialization without rendering them initially', function () {
    $product = commerceProduct();
    $variant = $product->defaultVariant;
    session()->put('cart_token', 'lazy-cart-token');
    app(CartService::class)->add($variant->id);

    $response = $this->get(route('store.category'));

    $response
        ->assertSuccessful()
        ->assertSee('cartLoaded: false', false)
        ->assertSee("window.Livewire?.dispatch('cart-initialized')", false)
        ->assertSee('x-on:livewire:navigated.window="initializeCart()"', false)
        ->assertSee('animate-spin', false)
        ->assertSee('role="dialog"', false)
        ->assertSee('aria-modal="true"', false)
        ->assertSee('x-ref="cartPanel"', false)
        ->assertSee('x-ref="cartClose"', false)
        ->assertSee('@click="closeCart()"', false)
        ->assertSee('@keydown.tab="trapCartFocus($event)"', false)
        ->assertSee('x-show="!cartLoaded"', false)
        ->assertSee('x-show="cartLoaded"', false)
        ->assertDontSee('wire:key="drawer-item-', false);

    expect(substr_count($response->getContent(), 'id="cart-drawer"'))->toBe(1);

    Livewire::test(CartDrawer::class)
        ->assertSet('loaded', false)
        ->call('loadCart')
        ->assertSet('loaded', true)
        ->assertSee($product->name, false)
        ->assertSee('aria-label="Remove '.$product->name.' from cart"', false)
        ->assertDontSee('>Remove<', false)
        ->call('loadCart')
        ->assertSet('loaded', true)
        ->assertSee($product->name, false);

    $cartItem = Cart::where('session_token', 'lazy-cart-token')->firstOrFail()->items()->firstOrFail();

    Livewire::test(CartDrawer::class)
        ->call('loadCart')
        ->call('updateItem', $cartItem->id, 2, 'increase')
        ->assertSet('items.0.quantity', 2)
        ->assertDispatched('notify', content: 'Quantity increased to 2.', type: 'success')
        ->call('updateItem', $cartItem->id, 1, 'decrease')
        ->assertDispatched('notify', content: 'Quantity decreased to 1.', type: 'error')
        ->call('removeItem', $cartItem->id)
        ->assertSet('items', []);
});

it('refreshes the cart when a cached storefront page is restored', function () {
    $firstProduct = commerceProduct(['name' => 'First Cached Product']);
    $secondProduct = commerceProduct(['name' => 'Second Cached Product']);
    session()->put('cart_token', 'cached-navigation-token');

    app(CartService::class)->add($firstProduct->defaultVariant->id);

    $drawer = Livewire::test(CartDrawer::class)
        ->call('loadCart')
        ->assertSet('loaded', true)
        ->assertSee($firstProduct->name, false)
        ->assertDontSee($secondProduct->name, false);

    app(CartService::class)->add($secondProduct->defaultVariant->id);

    $drawer
        ->call('initializeCart')
        ->assertSet('loaded', true)
        ->assertDispatched('cart-updated')
        ->assertSee($firstProduct->name, false)
        ->assertSee($secondProduct->name, false);
});

it('waits for cart confirmation before completing add feedback', function () {
    $product = commerceProduct();
    $variant = $product->defaultVariant;

    $drawer = Livewire::test(CartDrawer::class)
        ->call('addToCart', $variant->id, 1)
        ->assertSet('loaded', true)
        ->assertDispatched('cart-updated')
        ->assertDispatched('cart-item-added', variant_id: $variant->id, quantity: 1)
        ->assertDispatched('open-cart');

    $drawer->assertSee($product->name, false);

    $this->get(route('store.category'))
        ->assertSuccessful()
        ->assertSee('cartAddPending: false', false)
        ->assertSee('x-show="!cartLoaded || cartAddPending"', false)
        ->assertSee('x-show="cartLoaded && !cartAddPending"', false)
        ->assertSee('@cart-item-added.window="completeCartAdd()"', false)
        ->assertSee('@cart-add-failed.window="failCartAdd($event.detail.message)"', false)
        ->assertSee('Adding item to your cart', false);
});

it('clears cart add loading and preserves the cart when adding fails', function () {
    $product = commerceProduct(['inventory_quantity' => 1]);
    $variant = $product->defaultVariant;
    session()->put('cart_token', 'failed-add-token');
    app(CartService::class)->add($variant->id);

    Livewire::test(CartDrawer::class)
        ->call('addToCart', $variant->id, 1)
        ->assertSet('loaded', true)
        ->assertSet('items.0.quantity', 1)
        ->assertDispatched('cart-updated')
        ->assertDispatched('cart-add-failed')
        ->assertDispatched('open-cart');
});

it('notifies shoppers when the cart page quantity changes', function () {
    $product = commerceProduct();
    $variant = $product->defaultVariant;
    session()->put('cart_token', 'cart-page-quantity-token');
    app(CartService::class)->add($variant->id);

    $cartItem = Cart::where('session_token', 'cart-page-quantity-token')->firstOrFail()->items()->firstOrFail();

    Livewire::test(StoreCart::class)
        ->call('updateItem', $cartItem->id, 2, 'increase')
        ->assertDispatched('notify', content: 'Quantity increased to 2.', type: 'success')
        ->call('updateItem', $cartItem->id, 1, 'decrease')
        ->assertDispatched('notify', content: 'Quantity decreased to 1.', type: 'error')
        ->call('updateItem', $cartItem->id, 1, 'decrease')
        ->assertDispatched('notify', content: 'Quantity is already at the minimum of 1.', type: 'warning');

    $this->get(route('store.cart'))
        ->assertSee('aria-label="Remove '.$product->name.' from cart"', false)
        ->assertDontSee('>Remove<', false);
});

it('shows the derived regular price across cart and checkout pricing', function () {
    $product = commerceProduct([
        'regular_price_minor' => 5000,
        'sale_price_minor' => 3500,
    ]);
    session()->put('cart_token', 'discount-cart-token');
    app(CartService::class)->add($product->defaultVariant->id);

    $cart = $this->get(route('store.cart'));
    $checkout = $this->get(route('store.checkout'));
    $drawer = Livewire::test(CartDrawer::class)->call('loadCart');

    expect(app(CartService::class)->present()[0]['old_price'])->toBe(5000)
        ->and($cart->getContent())->toContain('৳50.00')
        ->and($cart->getContent())->toContain('৳35.00')
        ->and($checkout->getContent())->toContain('Sale')
        ->and($checkout->getContent())->toContain('৳50.00')
        ->and($drawer->html())->toContain('৳50.00');
});

it('keeps selected shirt variants distinct through cart, buy now, checkout, and order placement', function () {
    $product = Product::create([
        'name' => 'Commerce Shirt',
        'slug' => 'commerce-shirt',
        'product_type' => 'variable',
        'status' => 'draft',
        'visibility' => 'visible',
    ]);
    $color = $product->options()->create(['name' => 'Color', 'slug' => 'color']);
    $size = $product->options()->create(['name' => 'Size', 'slug' => 'size']);
    $color->values()->create(['value' => 'Black', 'slug' => 'black']);
    $color->values()->create(['value' => 'White', 'slug' => 'white']);
    $size->values()->create(['value' => 'Small', 'slug' => 'small']);
    $size->values()->create(['value' => 'Large', 'slug' => 'large']);

    $variantService = app(ProductVariantService::class);
    $generated = $variantService->generate($product);
    foreach ($generated as $index => $variant) {
        $variantService->save($variant, [
            'sku' => 'COMMERCE-SHIRT-'.($index + 1),
            'regular_price_minor' => 1000 + ($index * 100),
            'quantity_on_hand' => 3,
            'low_stock_threshold' => 1,
        ]);
    }
    $product->update(['status' => 'published']);
    Cache::flush();

    $blackSmall = ProductVariant::query()->where('combination_key', 'color=black|size=small')->firstOrFail();
    $blackLarge = ProductVariant::query()->where('combination_key', 'color=black|size=large')->firstOrFail();
    $mapped = app(CatalogQueryService::class)->product($product->slug);

    expect($mapped['options'])->toHaveCount(2)
        ->and($mapped['variants'])->toHaveCount(4)
        ->and(collect($mapped['variants'])->pluck('id')->all())->toContain($blackSmall->id, $blackLarge->id);

    session()->put('cart_token', 'commerce-shirt-cart');
    Livewire::test(StoreProduct::class, ['slug' => $product->slug])
        ->call('addToCart', $blackSmall->id, 1);
    Livewire::test(StoreProduct::class, ['slug' => $product->slug])
        ->call('buyNow', $blackLarge->id, 2)
        ->assertRedirect(route('store.checkout'));

    $cart = app(CartService::class)->current();
    expect($cart->items)->toHaveCount(2)
        ->and($cart->items->pluck('product_variant_id')->all())->toContain($blackSmall->id, $blackLarge->id);

    $order = app(OrderService::class)->place([
        'customer_name' => 'Variant Customer',
        'customer_email' => 'variant@example.test',
        'customer_phone' => '01700000000',
        'address_line' => 'House 1, Road 1',
        'city' => 'Dhaka',
        'district' => 'Dhanmondi',
        'postal_code' => '1205',
        'country' => 'BD',
        'delivery_method' => 'standard',
        'payment_method' => 'cod',
        'checkout_token' => (string) Str::uuid(),
    ]);

    expect($order->items)->toHaveCount(2)
        ->and($order->items->pluck('product_variant_id')->all())->toContain($blackSmall->id, $blackLarge->id)
        ->and($order->items->pluck('variant_name')->filter()->count())->toBe(2)
        ->and($blackSmall->fresh('inventory')->inventory->quantity_on_hand)->toBe(2)
        ->and($blackLarge->fresh('inventory')->inventory->quantity_on_hand)->toBe(1);
});

it('uses semantic colors for order statuses in admin order views', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $statuses = [
        'pending' => 'bg-amber-100 text-amber-700',
        'completed' => 'bg-green-100 text-green-700',
        'cancelled' => 'bg-red-100 text-red-700',
    ];

    foreach ($statuses as $status => $classes) {
        Order::create([
            'order_number' => 'SZ-STATUS-'.strtoupper($status),
            'checkout_token' => (string) Str::uuid(),
            'customer_name' => 'Status Customer',
            'customer_email' => $status.'@example.test',
            'customer_phone' => '01700000000',
            'status' => $status,
            'payment_status' => $status === 'completed' ? 'paid' : 'unpaid',
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
    }

    $orders = $this->actingAs($admin)->get(route('admin.orders'));
    $dashboard = $this->actingAs($admin)->get(route('admin.dashboard'));

    foreach ($statuses as $classes) {
        $orders->assertSee($classes, false);
        $dashboard->assertSee($classes, false);
    }
});

it('uses native lazy loading for storefront images while prioritizing the main product image', function () {
    $product = commerceProduct();

    $this->get(route('store.category'))
        ->assertSuccessful()
        ->assertSee('loading="lazy" decoding="async"', false);

    $this->get(route('store.product', ['slug' => $product->slug]))
        ->assertSuccessful()
        ->assertSee('loading="lazy" decoding="async"', false)
        ->assertSee('loading="eager" fetchpriority="high" decoding="async"', false);
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
        ->assertRedirect(route('store.order-success', ['order' => $order->order_number]))
        ->assertSessionHas('notify', [
            'content' => 'Payment completed successfully. Order placed.',
            'type' => 'success',
        ]);
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
