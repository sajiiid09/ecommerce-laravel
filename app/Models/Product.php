<?php

namespace App\Models;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\ProductVisibility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'product_type', 'brand_id', 'primary_category_id',
        'short_description', 'description_json', 'description_html', 'status',
        'visibility', 'is_featured', 'taxable', 'meta_title', 'meta_description',
        'canonical_url', 'is_indexable', 'published_at',
    ];

    protected $casts = ['description_json' => 'array', 'is_featured' => 'boolean', 'taxable' => 'boolean', 'is_indexable' => 'boolean', 'published_at' => 'datetime', 'product_type' => ProductType::class, 'status' => ProductStatus::class, 'visibility' => ProductVisibility::class];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function primaryCategory()
    {
        return $this->belongsTo(Category::class, 'primary_category_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function attributeValues()
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    public function options()
    {
        return $this->hasMany(ProductOption::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function defaultVariant()
    {
        return $this->hasOne(ProductVariant::class)->where('is_default', true);
    }

    public function media()
    {
        return $this->hasMany(ProductMedia::class);
    }

    public function scopePublished(Builder $q)
    {
        return $q->where('status', ProductStatus::Published->value);
    }

    public function scopeFeatured(Builder $q)
    {
        return $q->where('is_featured', true);
    }

    public function scopeSearch(Builder $q, ?string $term)
    {
        return $term ? $q->where(fn ($q) => $q->where('products.name', 'like', "%{$term}%")->orWhere('products.slug', 'like', "%{$term}%")->orWhereHas('variants', fn ($q) => $q->where('sku', 'like', "%{$term}%"))) : $q;
    }
}
