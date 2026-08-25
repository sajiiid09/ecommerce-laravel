<?php

use App\Livewire\Pages\Account\Dashboard;
use App\Livewire\Pages\Account\Orders;
use App\Livewire\Pages\Account\Tracking;
use App\Livewire\Pages\Admin\Catalog\Inventory\History;
use App\Livewire\Pages\Admin\Catalog\Products\Edit;
use App\Livewire\Pages\Admin\Catalog\Products\Export;
use App\Livewire\Pages\Admin\Catalog\Products\Import;
use App\Livewire\Pages\Admin\Catalog\Products\Variants;
use App\Livewire\Pages\Admin\Content\Homepage\Builder;
use App\Livewire\Pages\Admin\Content\Navigation\Manager;
use App\Livewire\Pages\Admin\Media\Index;
use App\Livewire\Pages\Auth\Login;
use App\Livewire\Pages\Auth\Register;
use App\Livewire\Pages\Store\Brand;
use App\Livewire\Pages\Store\Cart;
use App\Livewire\Pages\Store\Category;
use App\Livewire\Pages\Store\Checkout;
use App\Livewire\Pages\Store\Offers;
use App\Livewire\Pages\Store\OrderSuccess;
use App\Livewire\Pages\Store\Product;
use App\Livewire\Pages\Store\Search;
use App\Livewire\Pages\Store\Wishlist;
use App\Models\CategoryExport;
use App\Models\Page;
use App\Models\Redirect;
use App\Services\CatalogQueryService;
use App\Services\ContentResolver;
use App\Services\HomepageService;
use App\Services\RedirectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

Route::get('/', function (HomepageService $homepage) {
    return view('pages.store.home-cms', ['sections' => $homepage->sections()]);
})->name('store.home');

Route::livewire('/category/{slug?}', new Category)->name('store.category');

Route::livewire('/search', new Search)->name('store.search');

Route::livewire('/product/{slug}', new Product)->name('store.product');

Route::livewire('/offers', new Offers)->name('store.offers');
Route::livewire('/brands/{slug}', new Brand)->name('store.brand');

Route::livewire('/cart', new Cart)->name('store.cart');
Route::livewire('/checkout', new Checkout)->name('store.checkout');
Route::livewire('/checkout/success', new OrderSuccess)->name('store.order-success');
Route::get('/order-success', fn () => redirect()->route('store.order-success', [], 301));
Route::livewire('/wishlist', new Wishlist)->name('store.wishlist');
Route::livewire('/login', new Login)->name('login');
Route::livewire('/register', new Register)->name('register');
Route::livewire('/account', new Dashboard)->name('account.dashboard');
Route::livewire('/orders', new Orders)->name('account.orders');
Route::livewire('/account/orders/{order?}/track', new Tracking)->name('account.tracking');
Route::get('/orders/SZ-100248/tracking', fn () => redirect()->route('account.tracking', ['order' => 'SZ-100248'], 301));

Route::livewire('/admin', new App\Livewire\Pages\Admin\Dashboard)->middleware(['auth', 'admin'])->name('admin.dashboard');
Route::livewire('/admin/media', new Index)->middleware(['auth', 'admin'])->name('admin.media');

Route::prefix('admin/catalog')->middleware(['auth', 'admin'])->group(function (): void {
    Route::livewire('/products', new App\Livewire\Pages\Admin\Catalog\Products\Index)->name('admin.catalog.products');
    Route::livewire('/products/create', new Edit)->name('admin.catalog.products.create');
    Route::livewire('/products/{product}/edit', new Edit)->name('admin.catalog.products.edit');
    Route::livewire('/products/{product}/variants', new Variants)->name('admin.catalog.products.variants');
    Route::livewire('/products/import', new Import)->name('admin.catalog.products.import');
    Route::livewire('/products/export', new Export)->name('admin.catalog.products.export');
    Route::livewire('/categories', new App\Livewire\Pages\Admin\Catalog\Categories\Index)->name('admin.catalog.categories');
    Route::livewire('/categories/import', new App\Livewire\Pages\Admin\Catalog\Categories\Import)->name('admin.catalog.categories.import');
    Route::livewire('/categories/export', new App\Livewire\Pages\Admin\Catalog\Categories\Export)->name('admin.catalog.categories.export');
    Route::get('/categories/export/download/{categoryExport}', function (CategoryExport $categoryExport): BinaryFileResponse {
        Gate::authorize('export', App\Models\Category::class);
        abort_unless($categoryExport->status === 'ready' && $categoryExport->disk === 'local', 404);

        return response()->download(Storage::disk($categoryExport->disk)->path($categoryExport->path), $categoryExport->filename);
    })->name('admin.catalog.categories.export.download');
    Route::livewire('/brands', new App\Livewire\Pages\Admin\Catalog\Brands\Index)->name('admin.catalog.brands');
    Route::livewire('/tags', new App\Livewire\Pages\Admin\Catalog\Tags\Index)->name('admin.catalog.tags');
    Route::livewire('/attributes', new App\Livewire\Pages\Admin\Catalog\Attributes\Index)->name('admin.catalog.attributes');
    Route::livewire('/variants', new App\Livewire\Pages\Admin\Catalog\Variants\Index)->name('admin.catalog.variants');
    Route::livewire('/inventory', new App\Livewire\Pages\Admin\Catalog\Inventory\Index)->name('admin.catalog.inventory');
    Route::livewire('/inventory/{variant}/history', new History)->name('admin.catalog.inventory.history');
});

Route::prefix('admin/content')->middleware(['auth', 'admin'])->group(function (): void {
    Route::livewire('/', new App\Livewire\Pages\Admin\Content\Dashboard)->name('admin.content.dashboard');
    Route::livewire('/pages', new App\Livewire\Pages\Admin\Content\Pages\Index)->name('admin.content.pages');
    Route::livewire('/pages/create', new App\Livewire\Pages\Admin\Content\Pages\Edit)->name('admin.content.pages.create');
    Route::get('/pages/{page}/preview', function (Page $page) {
        Gate::authorize('view', $page);

        return view('pages.store.cms-page', ['page' => $page, 'preview' => true]);
    })->middleware('signed')->name('admin.content.pages.preview');
    Route::livewire('/pages/{page}/edit', new App\Livewire\Pages\Admin\Content\Pages\Edit)->name('admin.content.pages.edit');
    Route::livewire('/homepage', new Builder)->name('admin.content.homepage');
    Route::livewire('/banners', new App\Livewire\Pages\Admin\Content\Banners\Index)->name('admin.content.banners');
    Route::livewire('/banners/create', new App\Livewire\Pages\Admin\Content\Banners\Edit)->name('admin.content.banners.create');
    Route::livewire('/banners/{banner}/edit', new App\Livewire\Pages\Admin\Content\Banners\Edit)->name('admin.content.banners.edit');
    Route::livewire('/navigation', new Manager)->name('admin.content.navigation');
    Route::get('/header/preview', function (CatalogQueryService $catalog) {
        return view('pages.store.header-preview', ['categories' => $catalog->categoryOptions()]);
    })->name('admin.content.header.preview');
    Route::livewire('/header', new App\Livewire\Pages\Admin\Content\Header\Edit)->name('admin.content.header');
    Route::get('/footer/preview', function (CatalogQueryService $catalog) {
        return view('pages.store.footer-preview', ['categories' => $catalog->categoryOptions()]);
    })->name('admin.content.footer.preview');
    Route::livewire('/footer', new App\Livewire\Pages\Admin\Content\Footer\Edit)->name('admin.content.footer');
    Route::livewire('/redirects', new App\Livewire\Pages\Admin\Content\Redirects\Index)->name('admin.content.redirects');
    Route::get('/redirects/export', function (RedirectService $redirects) {
        Gate::authorize('export', Redirect::class);
        $path = $redirects->export();

        return response()->download(Storage::disk('local')->path($path), 'redirects.csv');
    })->name('admin.content.redirects.export');
    Route::livewire('/announcements', new App\Livewire\Pages\Admin\Content\Announcements\Index)->name('admin.content.announcements');
});

Route::get('/{slug}', function (string $slug, ContentResolver $resolver) {
    $resolved = $resolver->resolve($slug);
    if (($resolved['type'] ?? null) === 'page') {
        return view('pages.store.cms-page', ['page' => $resolved['page']]);
    }
    if (($resolved['type'] ?? null) === 'redirect') {
        return redirect($resolved['url'], $resolved['status']);
    }
    abort(404);
})->where('slug', '[A-Za-z0-9][A-Za-z0-9\-_]*')->name('store.cms-page');

Route::post('/logout', function (): RedirectResponse {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');
