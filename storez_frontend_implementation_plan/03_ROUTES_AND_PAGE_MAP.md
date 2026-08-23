# 03 — Routes and Page Map

## Routing style

Use Livewire v4 page routes and `wire:navigate` for same-origin storefront links.

Illustrative route map:

```php
Route::livewire('/', 'pages::store.home')->name('home');
Route::livewire('/category/{slug}', 'pages::store.category')->name('category.show');
Route::livewire('/search', 'pages::store.search')->name('search');
Route::livewire('/product/{slug}', 'pages::store.product')->name('product.show');

Route::livewire('/cart', 'pages::store.cart')->name('cart');
Route::livewire('/checkout', 'pages::store.checkout')->name('checkout');
Route::livewire('/checkout/success', 'pages::store.checkout-success')->name('checkout.success');

Route::livewire('/wishlist', 'pages::store.wishlist')->name('wishlist');
Route::livewire('/offers', 'pages::store.offers')->name('offers');
Route::livewire('/brands/{slug}', 'pages::store.brand')->name('brands.show');

Route::livewire('/account', 'pages::account.dashboard')->name('account');
Route::livewire('/account/orders', 'pages::account.orders')->name('account.orders');
Route::livewire('/account/orders/{order}/track', 'pages::account.order-tracking')->name('account.orders.track');

Route::livewire('/login', 'pages::auth.login')->name('login');
Route::livewire('/register', 'pages::auth.register')->name('register');
```

During the no-database phase, route parameters are demo identifiers only. Do not use model binding yet.

## Page/reference matrix

| Page | Route | Primary design reference |
|---|---|---|
| Home | `/` | `homepage(1).png` |
| Product listing / category | `/category/grocery` | `product_lisitng(1).png` |
| Search results | `/search?q=rice` | `search_result_page.png` |
| Product detail | `/product/teer-premium-basmati-rice-5kg` | `produtc_detail(1).png` |
| Cart drawer | global overlay | `cart_drawer(1).png` |
| Cart | `/cart` | StoreZ cart mockup supplied with the set |
| Checkout | `/checkout` | `checkout.png` |
| Checkout success | `/checkout/success` | `order_success.png` |
| Login | `/login` | `login_page.png` |
| Register | `/register` | `register_page.png` |
| Wishlist | `/wishlist` | `wislist.png` |
| My Account | `/account` | `account_page.png` |
| My Orders | `/account/orders` | `my_order.png` |
| Order tracking | `/account/orders/SZ-2405-00016/track` | `order_tracking.png` |
| Offers & Deals | `/offers` | `offerdeal_page.png` |
| Brand | `/brands/teer` | supplied demo HTML / StoreZ visual language |

## Shared page chrome

Every normal storefront page should receive:

1. promotional top strip
2. StoreZ header
3. category nav
4. main page content
5. trust/service strip near footer
6. footer
7. cart drawer root

Exceptions:

- auth pages still keep the StoreZ header/footer because the mockups show full storefront chrome
- checkout and success keep the same global chrome

## Page title/breadcrumb conventions

Examples:

```text
Home / Grocery & Essentials
Home / Search
Home / Grocery & Essentials / Rice / Teer Premium Basmati Rice 5kg
Home / Cart / Checkout
Home / Account / My Orders
```

Use one shared breadcrumb component.

## Navigation behavior

Use `wire:navigate` on ordinary internal links:

```blade
<a href="{{ route('wishlist') }}" wire:navigate>Wishlist</a>
```

Do not use it for:

- external URLs
- downloads
- `mailto:`
- `tel:`

## URL state to add during the frontend phase

Even without a database, the following should be represented in the URL where practical:

- `/search?q=rice`
- category slug
- product slug
- brand slug
- sort query: `?sort=popular`
- page query: `?page=2`
- selected filter query parameters if convenient

This makes the prototype behave like a real commerce frontend and reduces later rework.
