<?php

use App\Livewire\Pages\Admin\Customers\Index as CustomersIndex;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function customerManagementOrder(?int $userId, string $orderNumber, int $totalMinor = 2500): Order
{
    return Order::create([
        'order_number' => $orderNumber,
        'user_id' => $userId,
        'checkout_token' => Str::uuid()->toString(),
        'customer_name' => 'Customer Management Test',
        'customer_email' => 'customer-management@example.test',
        'customer_phone' => '01700000000',
        'status' => 'completed',
        'payment_status' => 'paid',
        'currency' => 'BDT',
        'subtotal_minor' => $totalMinor,
        'shipping_minor' => 0,
        'discount_minor' => 0,
        'tax_minor' => 0,
        'total_minor' => $totalMinor,
        'delivery_method' => 'standard',
        'payment_method' => 'cod',
        'placed_at' => now(),
    ]);
}

it('protects the customer management page and exposes it to admins', function () {
    $this->get(route('admin.customers'))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create())
        ->get(route('admin.customers'))
        ->assertForbidden();

    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get(route('admin.customers'))
        ->assertSuccessful()
        ->assertSee('Customers')
        ->assertSee('href="'.route('admin.customers').'"', false);
});

it('lists only registered customers and supports search and pagination', function () {
    $admin = User::factory()->create(['is_admin' => true, 'name' => 'Admin Account']);
    $searchable = User::factory()->create([
        'name' => 'Searchable Customer',
        'email' => 'searchable@example.test',
        'phone' => '01811111111',
    ]);
    User::factory()->create(['name' => 'Another Customer']);

    foreach (range(0, 15) as $index) {
        User::factory()->create([
            'name' => 'Paginated Customer '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
        ]);
    }

    Livewire::actingAs($admin)
        ->test(CustomersIndex::class)
        ->assertDontSee('Admin Account', false)
        ->set('searchQuery', 'searchable@example.test')
        ->assertSee('Searchable Customer', false)
        ->assertSee('01811111111', false)
        ->assertDontSee('Another Customer', false)
        ->set('searchQuery', '')
        ->set('perPage', 10)
        ->assertSee('Paginated Customer 00', false)
        ->call('nextPage')
        ->assertSee('Paginated Customer 15', false)
        ->assertDontSee('Paginated Customer 00', false);

    expect($searchable->fresh()->is_admin)->toBeFalse();
});

it('opens customer details with paginated orders and reviews without including guest orders', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $customer = User::factory()->create([
        'name' => 'Detail Customer',
        'email' => 'detail@example.test',
        'phone' => '01922222222',
    ]);
    $product = Product::create([
        'name' => 'Customer Detail Product',
        'slug' => 'customer-detail-product',
        'product_type' => 'simple',
        'status' => 'published',
        'visibility' => 'visible',
    ]);

    $firstOrder = customerManagementOrder($customer->id, 'SZ-CUSTOMER-001');
    $guestOrder = customerManagementOrder(null, 'SZ-GUEST-001');

    ProductReview::create([
        'product_id' => $product->id,
        'user_id' => $customer->id,
        'name' => $customer->name,
        'email' => $customer->email,
        'rating' => 5,
        'title' => 'Excellent customer review',
        'review' => 'This review belongs to the registered customer.',
        'status' => 'approved',
        'approved_at' => now(),
    ]);

    foreach (range(2, 6) as $index) {
        customerManagementOrder($customer->id, 'SZ-CUSTOMER-00'.$index);
        $reviewProduct = Product::create([
            'name' => 'Customer Review Product '.$index,
            'slug' => 'customer-review-product-'.$index,
            'product_type' => 'simple',
            'status' => 'published',
            'visibility' => 'visible',
        ]);
        ProductReview::create([
            'product_id' => $reviewProduct->id,
            'user_id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'rating' => 4,
            'title' => 'Review '.$index,
            'review' => 'Additional review for pagination coverage.',
            'status' => 'pending',
        ]);
    }

    Livewire::actingAs($admin)
        ->test(CustomersIndex::class)
        ->call('viewCustomer', $customer->id)
        ->assertSet('viewingCustomerId', $customer->id)
        ->assertDispatched('open-modal', id: 'customer-details')
        ->assertSee('modal-overlay', false)
        ->assertSee('scrollbar-hidden', false)
        ->assertSee('Detail Customer', false)
        ->assertSee('detail@example.test', false)
        ->assertSee('01922222222', false)
        ->assertSee('Order history', false)
        ->assertSee('w-full min-w-[640px] table-fixed text-left', false)
        ->assertSee('<col class="w-[30%]">', false)
        ->assertSee('<col class="w-[25%]">', false)
        ->assertSee('<col class="w-[20%]">', false)
        ->assertSee($firstOrder->order_number, false)
        ->assertDontSee($guestOrder->order_number, false)
        ->assertSee('Review history', false)
        ->assertSee('Excellent customer review', false)
        ->call('nextCustomerOrdersPage')
        ->assertSee('SZ-CUSTOMER-006', false)
        ->assertDontSee('SZ-CUSTOMER-001', false)
        ->call('nextCustomerReviewsPage')
        ->assertSee('Review 6', false)
        ->assertDontSee('Excellent customer review', false);
});

it('shows empty states for customers without order or review history', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $customer = User::factory()->create(['name' => 'New Customer']);

    Livewire::actingAs($admin)
        ->test(CustomersIndex::class)
        ->call('viewCustomer', $customer->id)
        ->assertSee('No orders found for this customer.', false)
        ->assertSee('No reviews found for this customer.', false);
});
