<?php
namespace Tests\Feature;
use App\Models\Category; use App\Services\ProductService; use App\Support\StorefrontCatalog; use Illuminate\Foundation\Testing\RefreshDatabase; use Tests\TestCase;
class StorefrontCatalogTest extends TestCase
{
    use RefreshDatabase;
    public function test_published_catalog_product_is_mapped_for_storefront():void{$category=Category::create(['name'=>'Audio','slug'=>'audio','is_active'=>true]);$product=app(ProductService::class)->save(['name'=>'DB Headphones','product_type'=>'simple','status'=>'published','visibility'=>'visible','primary_category_id'=>$category->id,'regular_price_minor'=>4999]);$mapped=StorefrontCatalog::product($product->slug);$this->assertSame('DB Headphones',$mapped['name']);$this->assertSame('audio',$mapped['categorySlug']);$this->assertSame(4999,$mapped['price']);$this->assertSame($product->defaultVariant->sku,$mapped['sku']);}
}
