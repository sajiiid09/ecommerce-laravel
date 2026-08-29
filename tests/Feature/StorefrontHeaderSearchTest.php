<?php

use App\Livewire\Components\Store\HeaderSearch;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('renders the header search controls with accessible autocomplete markup', function () {
    $this->get(route('store.home'))
        ->assertSuccessful()
        ->assertSee('wire:model.live.debounce.300ms="query"', false)
        ->assertSee('role="combobox"', false)
        ->assertSee('role="listbox"', false)
        ->assertSee('header-search-form-desktop', false)
        ->assertSee('header-search-form-mobile', false)
        ->assertSee('relative hidden min-w-0 flex-1 md:flex', false)
        ->assertSee('relative pb-3 md:hidden', false);
});

it('returns a limited set of published public products for a search suggestion', function () {
    $productService = app(ProductService::class);

    foreach (range(1, 6) as $number) {
        $productService->save([
            'name' => "Suggestion Product {$number}",
            'product_type' => 'simple',
            'status' => 'published',
            'visibility' => 'visible',
            'regular_price_minor' => 1000 + $number,
        ]);
    }

    $productService->save([
        'name' => 'Suggestion Product Draft',
        'product_type' => 'simple',
        'status' => 'draft',
        'visibility' => 'visible',
        'regular_price_minor' => 2000,
    ]);

    $component = Livewire::test(HeaderSearch::class, ['mode' => 'desktop'])
        ->set('query', 'Suggestion Product');

    $component
        ->assertSee('Suggestion Product 6')
        ->assertSee('Suggestion Product 2')
        ->assertDontSee('Suggestion Product 1')
        ->assertDontSee('Suggestion Product Draft')
        ->assertSee('See all results');

    expect(Product::query()->where('name', 'like', 'Suggestion Product%')->count())->toBe(7);
});

it('does not query suggestions for an empty or one-character query', function () {
    Livewire::test(HeaderSearch::class)
        ->assertDontSee('See all results')
        ->set('query', 'a')
        ->assertSee('Keep typing to search products.');
});
