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
