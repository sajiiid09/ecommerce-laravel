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
        $paths = ['/admin', '/admin/media', '/admin/settings/general', '/admin/settings/payments', '/admin/catalog/products', '/admin/catalog/products/create', '/admin/catalog/products/import', '/admin/catalog/products/export', '/admin/catalog/categories', '/admin/catalog/categories/import', '/admin/catalog/categories/export', '/admin/catalog/brands', '/admin/catalog/tags', '/admin/catalog/attributes', '/admin/catalog/variants', '/admin/catalog/inventory', '/admin/content', '/admin/content/pages', '/admin/content/pages/create', '/admin/content/homepage', '/admin/content/banners', '/admin/content/banners/create', '/admin/content/navigation', '/admin/content/header', '/admin/content/footer', '/admin/content/redirects'];

        foreach ($paths as $path) {
            $response = $this->actingAs($admin)->get($path);
            $response->assertOk()->assertSee('StoreZ Admin')->assertDontSee('Need Help?');
        }

        $dashboard = $this->actingAs($admin)->get('/admin');
        $dashboard
            ->assertSee('Admin Dashboard')
            ->assertSee('sidebarCollapsed', false)
            ->assertSee('toggleSidebar', false)
            ->assertSee('lg:w-[76px]', false)
            ->assertSee('lg:pl-[76px]', false)
            ->assertSee('data-icon="sidebar-simple"', false);
        $dashboard->assertDontSee('+ Add Product');
        $this->assertSame(1, substr_count($dashboard->getContent(), 'x-on:click="toggleSidebar()"'));
    }

    public function test_announcement_admin_page_is_available_under_content(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get('/admin/content/announcements')
            ->assertOk()
            ->assertSee('Announcements')
            ->assertSee('Announcements</a>', false);

        $this->actingAs($admin)->get('/admin/content/header')
            ->assertOk()
            ->assertSee('Show announcements')
            ->assertDontSee('header-announcement-editor', false)
            ->assertDontSee('Manage the single message', false);
    }

    public function test_admin_catalog_pages_hide_nonfunctional_placeholder_links(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        foreach (['/admin/catalog/brands', '/admin/catalog/products/import', '/admin/catalog/products/export'] as $path) {
            $response = $this->actingAs($admin)->get($path);

            $response
                ->assertOk()
                ->assertDontSee('href="#"', false);
        }

        $brands = $this->actingAs($admin)->get('/admin/catalog/brands');
        $brands
            ->assertDontSee('View Report')
            ->assertDontSee('View Full Report');

        $import = $this->actingAs($admin)->get('/admin/catalog/products/import');
        $import->assertDontSee('Download CSV Template');
    }
}
