<?php

namespace App\Support;

use App\Models\Brand as BrandModel;
use App\Models\Category as CategoryModel;
use App\Models\Product as ProductModel;
use Illuminate\Support\Facades\Schema;

final class StorefrontCatalog
{
    public static function products(): array
    {
        if (! Schema::hasTable('products')) {
            return StorefrontDemoData::products();
        }
        $placeholderImage = asset('images/placeholders/no-image.svg');
        $products = ProductModel::with(['brand', 'primaryCategory', 'variants.inventory', 'variants.optionValues.option'])->where('status', 'published')->whereIn('visibility', ['visible', 'catalog_search', 'catalog_only'])->get();
        if ($products->isEmpty()) {
            return StorefrontDemoData::products();
        }

        return $products->map(function (ProductModel $p) use ($placeholderImage) {
            $variants = $p->variants->where('is_active', true)->sortByDesc('is_default')->values();
            $v = $variants->first();
            $price = (int) ($v?->currentPriceMinor() ?? 0);
            $old = (int) ($v?->compare_at_price_minor ?? 0);
            $options = $p->options()->with('values')->orderBy('sort_order')->get()->map(fn ($o) => ['label' => $o->name, 'name' => $o->slug, 'values' => $o->values->map(fn ($value) => ['label' => $value->value, 'value' => $value->slug])->all()])->all();

            return ['id' => $p->id, 'slug' => $p->slug, 'name' => $p->name, 'brand' => $p->brand?->name ?? 'StoreZ', 'categorySlug' => $p->primaryCategory?->slug, 'image' => $placeholderImage, 'price' => $price, 'oldPrice' => $old ?: null, 'discount' => $old > 0 ? max(0, (int) round(($old - $price) / $old * 100)) : null, 'rating' => 0, 'reviews' => 0, 'inStock' => ($v?->availableQuantity() ?? 0) > 0, 'sku' => $v?->sku, 'variantId' => $v?->id, 'options' => $options, 'variants' => $variants->map(fn ($variant) => ['id' => $variant->id, 'sku' => $variant->sku, 'price' => $variant->currentPriceMinor(), 'stock' => $variant->availableQuantity(), 'optionValues' => $variant->optionValues->pluck('slug')->all()])->all()];
        })->all();
    }

    public static function categories(): array
    {
        if (! Schema::hasTable('categories')) {
            return StorefrontDemoData::categories();
        }$rows = CategoryModel::active()->orderBy('sort_order')->orderBy('name')->get();

        return $rows->isEmpty() ? StorefrontDemoData::categories() : $rows->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'slug' => $c->slug])->all();
    }

    public static function brands(): array
    {
        if (! Schema::hasTable('brands')) {
            return StorefrontDemoData::brands();
        }$rows = BrandModel::active()->orderBy('sort_order')->orderBy('name')->get();

        return $rows->isEmpty() ? StorefrontDemoData::brands() : $rows->map(fn ($b) => ['id' => $b->id, 'name' => $b->name])->all();
    }

    public static function product(string $slug): ?array
    {
        return collect(self::products())->firstWhere('slug', $slug);
    }
}
