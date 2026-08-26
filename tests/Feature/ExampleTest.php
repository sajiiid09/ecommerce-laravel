<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use PHPUnit\Framework\Attributes\DataProvider;
// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    #[DataProvider('storefrontPages')]
    public function test_storefront_pages_render(string $url): void
    {
        $response = $this->get($url);

        if (in_array($url, ['/account', '/orders', '/account/orders/SZ-100248/track'], true)) {
            $response->assertRedirect(route('login'));

            return;
        }

        $response->assertOk();
    }

    public static function storefrontPages(): array
    {
        return [
            'home' => ['/'],
            'category' => ['/category/electronics'],
            'search' => ['/search?q=wireless'],
            'product' => ['/product/wireless-noise-cancelling-headphones'],
            'offers' => ['/offers'],
            'brand' => ['/brands/soundmax'],
            'cart' => ['/cart'],
            'checkout' => ['/checkout'],
            'order success' => ['/checkout/success'],
            'wishlist' => ['/wishlist'],
            'login' => ['/login'],
            'register' => ['/register'],
            'account' => ['/account'],
            'orders' => ['/orders'],
            'tracking' => ['/account/orders/SZ-100248/track'],
        ];
    }

    public function test_sheaf_select_component_compiles(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-ui.select placeholder="Choose a product">
                <x-ui.select.option value="headphones">Headphones</x-ui.select.option>
            </x-ui.select>
        BLADE);

        $this->assertStringContainsString('Choose a product', $html);
        $this->assertStringContainsString('x-rover', $html);
    }
}
