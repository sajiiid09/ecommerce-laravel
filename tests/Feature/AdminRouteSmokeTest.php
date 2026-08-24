<?php
namespace Tests\Feature;
use App\Models\User; use Illuminate\Foundation\Testing\RefreshDatabase; use Tests\TestCase;
class AdminRouteSmokeTest extends TestCase
{
    use RefreshDatabase;
    public function test_static_admin_catalog_pages_render_for_admin():void
    {
        $admin=User::factory()->create(['is_admin'=>true]);
        $paths=['/admin','/admin/media','/admin/catalog/products','/admin/catalog/products/create','/admin/catalog/products/import','/admin/catalog/products/export','/admin/catalog/categories','/admin/catalog/brands','/admin/catalog/tags','/admin/catalog/attributes','/admin/catalog/variants','/admin/catalog/inventory'];

        foreach($paths as $path){
            $response=$this->actingAs($admin)->get($path);
            $response->assertOk()->assertSee('StoreZ Admin');
        }

        $this->actingAs($admin)->get('/admin')->assertSee('Admin Dashboard');
    }
}
