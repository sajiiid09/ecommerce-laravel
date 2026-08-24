<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.store.home')->name('store.home');

Route::livewire('/category/{slug?}', new \App\Livewire\Pages\Store\Category)->name('store.category');

Route::livewire('/search', new \App\Livewire\Pages\Store\Search)->name('store.search');

Route::livewire('/product/{slug}', new \App\Livewire\Pages\Store\Product)->name('store.product');

Route::livewire('/offers', new \App\Livewire\Pages\Store\Offers)->name('store.offers');
Route::livewire('/brands/{slug}', new \App\Livewire\Pages\Store\Brand)->name('store.brand');

Route::livewire('/cart', new \App\Livewire\Pages\Store\Cart)->name('store.cart');
Route::livewire('/checkout', new \App\Livewire\Pages\Store\Checkout)->name('store.checkout');
Route::livewire('/checkout/success', new \App\Livewire\Pages\Store\OrderSuccess)->name('store.order-success');
Route::get('/order-success', fn () => redirect()->route('store.order-success', [], 301));
Route::livewire('/wishlist', new \App\Livewire\Pages\Store\Wishlist)->name('store.wishlist');
Route::livewire('/login', new \App\Livewire\Pages\Auth\Login)->name('login');
Route::livewire('/register', new \App\Livewire\Pages\Auth\Register)->name('register');
Route::livewire('/account', new \App\Livewire\Pages\Account\Dashboard)->name('account.dashboard');
Route::livewire('/orders', new \App\Livewire\Pages\Account\Orders)->name('account.orders');
Route::livewire('/account/orders/{order?}/track', new \App\Livewire\Pages\Account\Tracking)->name('account.tracking');
Route::get('/orders/SZ-100248/tracking', fn () => redirect()->route('account.tracking', ['order' => 'SZ-100248'], 301));

Route::livewire('/admin', new \App\Livewire\Pages\Admin\Dashboard)->middleware(['auth', 'admin'])->name('admin.dashboard');
Route::livewire('/admin/media', new \App\Livewire\Pages\Admin\Media\Index)->middleware(['auth', 'admin'])->name('admin.media');

Route::prefix('admin/catalog')->middleware(['auth', 'admin'])->group(function (): void {
    Route::livewire('/products', new \App\Livewire\Pages\Admin\Catalog\Products\Index)->name('admin.catalog.products');
    Route::livewire('/products/create', new \App\Livewire\Pages\Admin\Catalog\Products\Edit)->name('admin.catalog.products.create');
    Route::livewire('/products/{product}/edit', new \App\Livewire\Pages\Admin\Catalog\Products\Edit)->name('admin.catalog.products.edit');
    Route::livewire('/products/{product}/variants', new \App\Livewire\Pages\Admin\Catalog\Products\Variants)->name('admin.catalog.products.variants');
    Route::livewire('/products/import', new \App\Livewire\Pages\Admin\Catalog\Products\Import)->name('admin.catalog.products.import');
    Route::livewire('/products/export', new \App\Livewire\Pages\Admin\Catalog\Products\Export)->name('admin.catalog.products.export');
    Route::livewire('/categories', new \App\Livewire\Pages\Admin\Catalog\Categories\Index)->name('admin.catalog.categories');
    Route::livewire('/brands', new \App\Livewire\Pages\Admin\Catalog\Brands\Index)->name('admin.catalog.brands');
    Route::livewire('/tags', new \App\Livewire\Pages\Admin\Catalog\Tags\Index)->name('admin.catalog.tags');
    Route::livewire('/attributes', new \App\Livewire\Pages\Admin\Catalog\Attributes\Index)->name('admin.catalog.attributes');
    Route::livewire('/variants', new \App\Livewire\Pages\Admin\Catalog\Variants\Index)->name('admin.catalog.variants');
    Route::livewire('/inventory', new \App\Livewire\Pages\Admin\Catalog\Inventory\Index)->name('admin.catalog.inventory');
    Route::livewire('/inventory/{variant}/history', new \App\Livewire\Pages\Admin\Catalog\Inventory\History)->name('admin.catalog.inventory.history');
});

Route::post('/logout', function (): \Illuminate\Http\RedirectResponse { auth()->logout(); request()->session()->invalidate(); request()->session()->regenerateToken(); return redirect()->route('login'); })->middleware('auth')->name('logout');
