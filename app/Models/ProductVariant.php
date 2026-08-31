<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id', 'sku', 'barcode', 'name', 'combination_key',
        'regular_price_minor', 'sale_price_minor', 'compare_at_price_minor',
        'cost_price_minor', 'weight_grams', 'is_active', 'is_default', 'sort_order',
    ];

    protected $casts = ['is_active' => 'boolean', 'is_default' => 'boolean'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function optionValues(): BelongsToMany
    {
        return $this->belongsToMany(ProductOptionValue::class, 'product_variant_option_value');
    }

    public function inventory(): HasOne
    {
        return $this->hasOne(InventoryItem::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(ProductMedia::class);
    }

    public function currentPriceMinor(): int
    {
        $regularPrice = (int) ($this->regular_price_minor ?? 0);

        return $this->sale_price_minor !== null && $this->sale_price_minor < $regularPrice
            ? (int) $this->sale_price_minor
            : $regularPrice;
    }

    public function compareAtPriceMinor(): ?int
    {
        $regularPrice = (int) ($this->regular_price_minor ?? 0);

        return $this->sale_price_minor !== null && $this->sale_price_minor < $regularPrice
            ? $regularPrice
            : null;
    }

    public function availableQuantity(): int
    {
        return (int) ($this->inventory?->quantity_on_hand ?? 0) - (int) ($this->inventory?->quantity_reserved ?? 0);
    }
}
