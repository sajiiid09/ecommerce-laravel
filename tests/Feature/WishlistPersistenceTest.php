<?php

namespace Tests\Feature;

use App\Livewire\Pages\Auth\Login;
use App\Livewire\Pages\Auth\Register;
use App\Models\Product;
use App\Models\User;
use App\Models\WishlistItem;
use App\Services\WishlistService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

use function Pest\Laravel\get;

function createWishlistProduct(string $name, string $status = 'published', string $visibility = 'visible'): Product
{
    return Product::create([
        'name' => $name,
        'slug' => str($name)->slug(),
        'product_type' => 'simple',
        'status' => $status,
        'visibility' => $visibility,
        'published_at' => $status === 'published' ? now() : null,
    ]);
}

it('persists guest wishlist changes in the session and across page reloads', function () {
    $product = createWishlistProduct('Guest Wishlist Product');

    $this->postJson(route('store.wishlist.add', ['productId' => $product->id]))
        ->assertOk()
        ->assertJson(['ids' => [$product->id]]);

    expect(session(WishlistService::SESSION_KEY))->toBe([$product->id]);

    get(route('store.wishlist'))
        ->assertOk()
        ->assertSee("wishlist: JSON.parse('[".$product->id."]')", false);

    $this->deleteJson(route('store.wishlist.remove', ['productId' => $product->id]))
        ->assertOk()
        ->assertJson(['ids' => []]);

    expect(session(WishlistService::SESSION_KEY))->toBe([]);

    get(route('store.wishlist'))
        ->assertOk()
        ->assertSee('wishlist: []', false);
});

it('persists authenticated wishlist changes and prevents duplicates', function () {
    $user = User::factory()->create();
    $product = createWishlistProduct('Account Wishlist Product');
    $secondProduct = createWishlistProduct('Second Account Wishlist Product');
    $this->actingAs($user);

    $this->postJson(route('store.wishlist.add', ['productId' => $product->id]))
        ->assertOk()
        ->assertJson(['ids' => [$product->id]]);

    $this->postJson(route('store.wishlist.add', ['productId' => $product->id]))
        ->assertOk()
        ->assertJson(['ids' => [$product->id]]);

    expect(WishlistItem::whereBelongsTo($user)->count())->toBe(1);

    $this->deleteJson(route('store.wishlist.remove', ['productId' => $product->id]))
        ->assertOk()
        ->assertJson(['ids' => []]);

    expect(WishlistItem::whereBelongsTo($user)->exists())->toBeFalse();

    $this->postJson(route('store.wishlist.add', ['productId' => $product->id]));
    $this->postJson(route('store.wishlist.add', ['productId' => $secondProduct->id]));

    $this->deleteJson(route('store.wishlist.clear'))
        ->assertOk()
        ->assertJson(['ids' => []]);

    expect(WishlistItem::whereBelongsTo($user)->exists())->toBeFalse();
});

it('rejects unavailable products and filters them from guest synchronization', function () {
    $draft = createWishlistProduct('Draft Wishlist Product', 'draft');
    $published = createWishlistProduct('Published Wishlist Product');

    $this->postJson(route('store.wishlist.add', ['productId' => $draft->id]))
        ->assertNotFound();

    $this->postJson(route('store.wishlist.sync'), [
        'ids' => [$draft->id, $published->id, 999999],
    ])->assertOk()->assertJson(['ids' => [$published->id]]);

    expect(session(WishlistService::SESSION_KEY))->toBe([$published->id]);
});

it('merges guest and account wishlists during login', function () {
    $user = User::factory()->create(['password' => 'password']);
    $guestProduct = createWishlistProduct('Login Guest Wishlist Product');
    $accountProduct = createWishlistProduct('Login Account Wishlist Product');
    WishlistItem::create(['user_id' => $user->id, 'product_id' => $accountProduct->id]);
    session()->put(WishlistService::SESSION_KEY, [$guestProduct->id]);

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'password')
        ->call('login');

    expect(WishlistItem::whereBelongsTo($user)->pluck('product_id')->all())
        ->toEqualCanonicalizing([$guestProduct->id, $accountProduct->id])
        ->and(session()->has(WishlistService::SESSION_KEY))->toBeFalse();
});

it('merges a guest wishlist into a newly registered account', function () {
    $guestProduct = createWishlistProduct('Register Guest Wishlist Product');
    session()->put(WishlistService::SESSION_KEY, [$guestProduct->id]);

    Livewire::test(Register::class)
        ->set('name', 'Wishlist Customer')
        ->set('email', 'wishlist-customer@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register');

    $user = User::where('email', 'wishlist-customer@example.com')->firstOrFail();

    expect($user->wishlistItems()->pluck('product_id')->all())->toBe([$guestProduct->id])
        ->and(session()->has(WishlistService::SESSION_KEY))->toBeFalse();
});

it('uses persisted wishlist state on the wishlist and product pages', function () {
    Cache::flush();
    $user = User::factory()->create();
    $product = createWishlistProduct('Shared Wishlist Product');
    WishlistItem::create(['user_id' => $user->id, 'product_id' => $product->id]);
    $this->actingAs($user);

    get(route('store.wishlist'))
        ->assertOk()
        ->assertSee("wishlist: JSON.parse('[".$product->id."]')", false);

    get(route('store.product', ['slug' => $product->slug]))
        ->assertOk()
        ->assertSee('toggleWishlist('.$product->id.')', false)
        ->assertSee('wishlist.includes('.$product->id.')', false);
});

it('renders a valid Alpine layout expression for wishlist requests', function () {
    $response = get(route('store.wishlist'))->assertOk();

    expect($response->getContent())
        ->toContain("document.querySelector('meta[name=csrf-token]')")
        ->not->toContain("document.querySelector('meta[name=\"csrf-token\"]')");
});

it('enforces the wishlist user and product uniqueness constraint', function () {
    $user = User::factory()->create();
    $product = createWishlistProduct('Unique Wishlist Product');

    WishlistItem::create(['user_id' => $user->id, 'product_id' => $product->id]);

    expect(fn () => WishlistItem::create(['user_id' => $user->id, 'product_id' => $product->id]))
        ->toThrow(QueryException::class);
});
