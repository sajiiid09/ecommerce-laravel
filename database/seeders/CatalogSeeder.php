<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedBrands();
        $this->seedTags();
        $this->seedCategories();
        $this->seedAttributes();
    }

    private function seedBrands(): void
    {
        $brands = [
            ['name' => 'SoundMax', 'slug' => 'soundmax', 'is_featured' => false],
            ['name' => 'BrightHome', 'slug' => 'brighthome', 'is_featured' => true],
            ['name' => 'StoreZ Fresh', 'slug' => 'storez-fresh', 'is_featured' => true],
            ['name' => 'Teer', 'slug' => 'teer', 'is_featured' => false],
            ['name' => 'Fresh', 'slug' => 'fresh', 'is_featured' => false],
            ['name' => 'Orix', 'slug' => 'orix', 'is_featured' => false],
            ['name' => 'Xiaomi', 'slug' => 'xiaomi', 'is_featured' => false],
            ['name' => 'Nivea', 'slug' => 'nivea', 'is_featured' => false],
            ['name' => 'Miyako', 'slug' => 'miyako', 'is_featured' => false],
            ['name' => 'Aarong', 'slug' => 'aarong', 'is_featured' => true],
            ['name' => 'Samsung', 'slug' => 'samsung', 'is_featured' => false],
            ['name' => 'Walton', 'slug' => 'walton', 'is_featured' => false],
            ['name' => 'Apex', 'slug' => 'apex', 'is_featured' => false],
        ];

        foreach ($brands as $brand) {
            Brand::updateOrCreate(['slug' => $brand['slug']], $brand);
        }
    }

    private function seedTags(): void
    {
        $tags = [
            ['name' => 'New Arrival', 'slug' => 'new-arrival', 'type' => 'merchandising'],
            ['name' => 'Best Seller', 'slug' => 'best-seller', 'type' => 'merchandising'],
            ['name' => 'On Sale', 'slug' => 'on-sale', 'type' => 'merchandising'],
            ['name' => 'Clearance', 'slug' => 'clearance', 'type' => 'merchandising'],
            ['name' => 'Eco Friendly', 'slug' => 'eco-friendly', 'type' => 'product'],
            ['name' => 'Organic', 'slug' => 'organic', 'type' => 'product'],
            ['name' => 'Premium', 'slug' => 'premium', 'type' => 'product'],
            ['name' => 'Budget Friendly', 'slug' => 'budget-friendly', 'type' => 'product'],
            ['name' => 'Staff Pick', 'slug' => 'staff-pick', 'type' => 'internal'],
            ['name' => 'Back Soon', 'slug' => 'back-soon', 'type' => 'internal'],
        ];

        foreach ($tags as $tag) {
            Tag::updateOrCreate(['slug' => $tag['slug']], $tag);
        }
    }

    private function seedCategories(): void
    {
        $roots = [
            ['name' => 'Electronics', 'slug' => 'electronics', 'sort_order' => 1],
            ['name' => 'Fashion', 'slug' => 'fashion', 'sort_order' => 2],
            ['name' => 'Groceries', 'slug' => 'groceries', 'sort_order' => 3],
            ['name' => 'Home & Living', 'slug' => 'home-living', 'sort_order' => 4],
            ['name' => 'Beauty & Care', 'slug' => 'beauty', 'sort_order' => 5],
            ['name' => 'Baby & Toys', 'slug' => 'baby-toys', 'sort_order' => 6],
        ];

        $children = [
            'electronics' => [
                ['name' => 'Phones', 'slug' => 'phones', 'sort_order' => 1],
                ['name' => 'Audio', 'slug' => 'audio', 'sort_order' => 2],
            ],
            'fashion' => [
                ['name' => "Men's Fashion", 'slug' => 'mens-fashion', 'sort_order' => 1],
                ['name' => "Women's Fashion", 'slug' => 'womens-fashion', 'sort_order' => 2],
            ],
        ];

        foreach ($roots as $root) {
            $category = Category::updateOrCreate(['slug' => $root['slug']], $root);

            if (isset($children[$root['slug']])) {
                foreach ($children[$root['slug']] as $child) {
                    Category::updateOrCreate(
                        ['slug' => $child['slug']],
                        [...$child, 'parent_id' => $category->id]
                    );
                }
            }
        }
    }

    private function seedAttributes(): void
    {
        $attributes = [
            [
                'name' => 'Material', 'slug' => 'material', 'type' => 'text',
                'is_filterable' => true, 'is_active' => true, 'sort_order' => 1,
                'values' => ['Cotton', 'Polyester', 'Steel', 'Plastic'],
            ],
            [
                'name' => 'Weight', 'slug' => 'weight', 'type' => 'text',
                'is_filterable' => true, 'is_active' => true, 'sort_order' => 2,
                'values' => ['1kg', '5kg', '10kg'],
            ],
            [
                'name' => 'Warranty', 'slug' => 'warranty', 'type' => 'text',
                'is_filterable' => true, 'is_active' => true, 'sort_order' => 3,
                'values' => ['6 Months', '1 Year', '2 Years'],
            ],
            [
                'name' => 'Color', 'slug' => 'color', 'type' => 'select',
                'is_filterable' => true, 'is_active' => true, 'sort_order' => 4,
                'values' => ['Red', 'Blue', 'Black', 'White'],
            ],
            [
                'name' => 'Size', 'slug' => 'size', 'type' => 'select',
                'is_filterable' => true, 'is_active' => true, 'sort_order' => 5,
                'values' => ['S', 'M', 'L', 'XL'],
            ],
        ];

        foreach ($attributes as $attrData) {
            $values = $attrData['values'];
            unset($attrData['values']);

            $attribute = Attribute::updateOrCreate(['slug' => $attrData['slug']], $attrData);

            foreach ($values as $index => $value) {
                $valueSlug = Str::slug($value);
                AttributeValue::updateOrCreate(
                    ['attribute_id' => $attribute->id, 'slug' => $valueSlug],
                    ['value' => $value, 'slug' => $valueSlug, 'sort_order' => $index]
                );
            }
        }
    }
}
