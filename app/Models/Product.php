<?php

namespace App\Models;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\ProductVisibility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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

    protected $casts = [
        'description_json' => 'array',
        'is_featured' => 'boolean',
        'taxable' => 'boolean',
        'is_indexable' => 'boolean',
        'published_at' => 'datetime',
        'product_type' => ProductType::class,
        'status' => ProductStatus::class,
        'visibility' => ProductVisibility::class,
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function primaryCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'primary_category_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function attributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(ProductOption::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function defaultVariant(): HasOne
    {
        return $this->hasOne(ProductVariant::class)->where('is_default', true);
    }

    public function media(): HasMany
    {
        return $this->hasMany(ProductMedia::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', ProductStatus::Published->value);
    }

    public function scopeFeatured(Builder $q): Builder
    {
        return $q->where('is_featured', true);
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        return $term ? $q->where(function (Builder $query) use ($term): void {
            $query->where('products.name', 'like', "%{$term}%")
                ->orWhere('products.slug', 'like', "%{$term}%")
                ->orWhereHas('brand', fn (Builder $brand): Builder => $brand
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('slug', 'like', "%{$term}%"))
                ->orWhereHas('variants', fn (Builder $variant): Builder => $variant->where('sku', 'like', "%{$term}%"));
        }) : $q;
    }
}
