<?php

use App\Enums\CouponDiscountType;
use App\Livewire\Pages\Admin\Coupons\Index as CouponsIndex;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\OrderService;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function couponTestProduct(array $attributes = []): Product
{
    return app(ProductService::class)->save(array_merge([
        'name' => 'Coupon Test Product',
        'product_type' => 'simple',
        'status' => 'published',
        'visibility' => 'visible',
        'regular_price_minor' => 10000,
        'inventory_quantity' => 10,
    ], $attributes))->load('defaultVariant');
}

function couponTestCart(Product $product, int $quantity = 1): void
{
    session()->put('cart_token', 'coupon-cart-'.Str::random(8));
    app(CartService::class)->add($product->defaultVariant->id, $quantity);
}

it('authorizes the admin coupon route and excludes non-admin users', function () {
    $this->get(route('admin.coupons'))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create())->get(route('admin.coupons'))->assertForbidden();

    $admin = User::factory()->create(['is_admin' => true]);
    Coupon::factory()->create(['code' => 'UI10']);

    $this->actingAs($admin)
        ->get(route('admin.coupons'))
        ->assertSuccessful()
        ->assertSee('Coupon code')
        ->assertSee('Percentage discount')
        ->assertSee('Minimum subtotal')
        ->assertSee('Coupon scope')
        ->assertSee('Starts at')
        ->assertSee('Global usage limit')
        ->assertSee('Per-customer usage limit')
        ->assertSee('type="number"', false)
        ->assertSee('Edit')
        ->assertSee('x-teleport="body"', false);
});

it('creates normalized coupons and supports generated codes through the admin component', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    Livewire::actingAs($admin)->test(CouponsIndex::class)
        ->call('generateCode')
        ->assertSet('code', fn (string $code): bool => preg_match('/^STOREZ-[A-Z0-9]{8}$/', $code) === 1)
        ->set('code', ' save10 ')
        ->set('discountType', 'percentage')
        ->set('percentage', '10')
        ->call('saveCoupon')
        ->assertHasNoErrors();

    expect(Coupon::query()->value('code'))->toBe('SAVE10');
});

it('calculates percentage and fixed discounts from current sale prices', function () {
    $product = couponTestProduct(['regular_price_minor' => 10000, 'sale_price_minor' => 8000]);
    couponTestCart($product, 2);
    $service = app(CouponService::class);

    $percentage = Coupon::factory()->create(['code' => 'PERCENT10', 'percentage' => 10, 'discount_type' => CouponDiscountType::Percentage]);
    $fixed = Coupon::factory()->create(['code' => 'FIXED50', 'percentage' => null, 'amount_minor' => 5000, 'discount_type' => CouponDiscountType::Fixed]);

    expect($service->quote('percent10', app(CartService::class)->current())['discount_minor'])->toBe(1600)
        ->and($service->quote('FIXED50', app(CartService::class)->current())['discount_minor'])->toBe(5000);
});

it('targets products and descendant categories and enforces minimum and lifecycle rules', function () {
    $parent = Category::create(['name' => 'Electronics', 'slug' => 'electronics', 'is_active' => true]);
    $child = Category::create(['name' => 'Audio', 'slug' => 'audio', 'parent_id' => $parent->id, 'is_active' => true]);
    $product = couponTestProduct();
    $product->update(['primary_category_id' => $child->id]);
    $product->categories()->sync([$child->id]);
    couponTestCart($product);

    $coupon = Coupon::factory()->create(['code' => 'AUDIO20', 'percentage' => 20, 'minimum_subtotal_minor' => 9000]);
    $coupon->categories()->attach($parent);

    expect(app(CouponService::class)->quote('audio20', app(CartService::class)->current())['discount_minor'])->toBe(2000);

    $coupon->update(['is_active' => false]);
    expect(fn () => app(CouponService::class)->quote('AUDIO20', app(CartService::class)->current()))->toThrow(ValidationException::class);
});

it('revalidates and snapshots a coupon when placing an order', function () {
    $product = couponTestProduct();
    couponTestCart($product, 2);
    $coupon = Coupon::factory()->create(['code' => 'ORDER10', 'percentage' => 10, 'per_customer_limit' => 1]);

    $data = [
        'customer_name' => 'Coupon Customer', 'customer_email' => 'coupon@example.test', 'customer_phone' => '01700000000',
        'address_line' => 'House 1', 'city' => 'Dhaka', 'delivery_method' => 'standard', 'payment_method' => 'cod',
        'checkout_token' => (string) Str::uuid(), 'coupon_code' => ' order10 ',
    ];
    $order = app(OrderService::class)->place($data);

    expect($order->coupon_id)->toBe($coupon->id)
        ->and($order->coupon_code)->toBe('ORDER10')
        ->and($order->discount_minor)->toBe(2000)
        ->and($order->total_minor)->toBe(18000)
        ->and($order->payment->amount_minor)->toBe($order->total_minor);

    $coupon->delete();
    expect(Order::query()->whereKey($order->id)->value('coupon_code'))->toBe('ORDER10');
});

it('does not count cancelled orders against coupon usage limits', function () {
    $product = couponTestProduct();
    couponTestCart($product);
    $coupon = Coupon::factory()->create(['code' => 'LIMIT1', 'percentage' => 10, 'usage_limit' => 1]);
    $order = app(OrderService::class)->place([
        'customer_name' => 'Guest', 'customer_email' => 'guest-limit@example.test', 'customer_phone' => '01700000000',
        'address_line' => 'House 1', 'city' => 'Dhaka', 'delivery_method' => 'standard', 'payment_method' => 'cod',
        'checkout_token' => (string) Str::uuid(), 'coupon_code' => 'LIMIT1',
    ]);

    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin);
    app(OrderService::class)->transition($order, 'cancelled');
    expect($coupon->fresh()->orders()->where('status', '!=', 'cancelled')->count())->toBe(0);
});
