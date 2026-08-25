<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRouteSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_static_admin_catalog_pages_render_for_admin(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $paths = ['/admin', '/admin/media', '/admin/catalog/products', '/admin/catalog/products/create', '/admin/catalog/products/import', '/admin/catalog/products/export', '/admin/catalog/categories', '/admin/catalog/categories/import', '/admin/catalog/categories/export', '/admin/catalog/brands', '/admin/catalog/tags', '/admin/catalog/attributes', '/admin/catalog/variants', '/admin/catalog/inventory', '/admin/content', '/admin/content/pages', '/admin/content/pages/create', '/admin/content/homepage', '/admin/content/banners', '/admin/content/banners/create', '/admin/content/navigation', '/admin/content/header', '/admin/content/footer', '/admin/content/redirects', '/admin/content/announcements'];

        foreach ($paths as $path) {
            $response = $this->actingAs($admin)->get($path);
            $response->assertOk()->assertSee('StoreZ Admin');
        }

        $this->actingAs($admin)->get('/admin')->assertSee('Admin Dashboard');
    }
}
