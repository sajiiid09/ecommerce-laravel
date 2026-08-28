<?php

namespace Database\Seeders;

use App\Enums\ImagePreset;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Tag;
use Database\Seeders\Concerns\SeedsDemoMedia;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    use SeedsDemoMedia;

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
            ['name' => 'Sony', 'slug' => 'sony', 'is_featured' => true, 'logo' => 'brands/sony.png'],
            ['name' => 'WiZ', 'slug' => 'wiz', 'is_featured' => true, 'logo' => 'brands/wiz.png'],
            ['name' => 'Fresh', 'slug' => 'fresh', 'is_featured' => true, 'logo' => 'brands/fresh.png'],
            ['name' => 'Teer', 'slug' => 'teer', 'is_featured' => false, 'logo' => 'brands/teer.png'],
            ['name' => 'Orix', 'slug' => 'orix', 'is_featured' => false, 'logo' => 'brands/orix.png'],
            ['name' => 'Xiaomi', 'slug' => 'xiaomi', 'is_featured' => true, 'logo' => 'brands/xiaomi.png'],
            ['name' => 'Nivea', 'slug' => 'nivea', 'is_featured' => false, 'logo' => 'brands/nivea.png'],
            ['name' => 'Miyako', 'slug' => 'miyako', 'is_featured' => false, 'logo' => 'brands/miyako.png'],
            ['name' => 'Samsung', 'slug' => 'samsung', 'is_featured' => true, 'logo' => 'brands/samsung.png'],
            ['name' => 'Walton', 'slug' => 'walton', 'is_featured' => true, 'logo' => 'brands/walton.png'],
            ['name' => 'Apex', 'slug' => 'apex', 'is_featured' => true, 'logo' => 'brands/apex.png'],
            ['name' => 'PRAN', 'slug' => 'pran', 'is_featured' => false, 'logo' => 'brands/pran.png'],
            ['name' => 'IKEA', 'slug' => 'ikea', 'is_featured' => true, 'logo' => 'brands/ikea.png'],
        ];

        foreach ($brands as $data) {
            $logo = $data['logo'];
            unset($data['logo']);

            $brand = Brand::updateOrCreate(['slug' => $data['slug']], $data);
            $media = $this->seedLocalImage($logo, ImagePreset::Logo);

            if ($media) {
                $brand->forceFill(['logo_media_id' => $media['id']])->save();
            }
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
            ['name' => 'Electronics', 'slug' => 'electronics', 'sort_order' => 1, 'image' => 'categories/electronics.jpg'],
            ['name' => 'Fashion', 'slug' => 'fashion', 'sort_order' => 2, 'image' => 'categories/fashion.jpg'],
            ['name' => 'Groceries', 'slug' => 'groceries', 'sort_order' => 3, 'image' => 'categories/groceries.jpg'],
            ['name' => 'Home & Living', 'slug' => 'home-living', 'sort_order' => 4, 'image' => 'categories/home-living.jpg'],
            ['name' => 'Beauty & Care', 'slug' => 'beauty', 'sort_order' => 5, 'image' => 'categories/beauty.jpg'],
            ['name' => 'Baby & Toys', 'slug' => 'baby-toys', 'sort_order' => 6, 'image' => 'categories/baby-toys.jpg'],
        ];

        $children = [
            'electronics' => [
                ['name' => 'Phones', 'slug' => 'phones', 'sort_order' => 1, 'image' => 'categories/phones.jpg'],
                ['name' => 'Audio', 'slug' => 'audio', 'sort_order' => 2, 'image' => 'categories/audio.jpg'],
            ],
            'fashion' => [
                ['name' => "Men's Fashion", 'slug' => 'mens-fashion', 'sort_order' => 1, 'image' => 'categories/mens-fashion.jpg'],
                ['name' => "Women's Fashion", 'slug' => 'womens-fashion', 'sort_order' => 2, 'image' => 'categories/womens-fashion.jpg'],
            ],
        ];

        foreach ($roots as $rootData) {
            $image = $rootData['image'];
            unset($rootData['image']);

            $category = Category::updateOrCreate(['slug' => $rootData['slug']], $rootData);
            $this->attachCategoryImage($category, $image);

            foreach ($children[$rootData['slug']] ?? [] as $childData) {
                $childImage = $childData['image'];
                unset($childData['image']);

                $child = Category::updateOrCreate(
                    ['slug' => $childData['slug']],
                    [...$childData, 'parent_id' => $category->id]
                );

                $this->attachCategoryImage($child, $childImage);
            }
        }
    }

    private function attachCategoryImage(Category $category, string $relativePath): void
    {
        $media = $this->seedLocalImage($relativePath, ImagePreset::Category);

        if (! $media) {
            return;
        }

        $category->forceFill([
            'media_asset_id' => $media['id'],
            'image_url' => Storage::disk('public')->url($media['path']),
        ])->save();
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
                'values' => ['1kg', '2kg', '5kg', '10kg'],
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
