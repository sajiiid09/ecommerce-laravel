<?php

namespace App\Models;

use App\Enums\CouponDiscountType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'discount_type', 'percentage', 'amount_minor', 'minimum_subtotal_minor',
        'starts_at', 'ends_at', 'usage_limit', 'per_customer_limit', 'is_active',
    ];

    protected $casts = [
        'discount_type' => CouponDiscountType::class,
        'percentage' => 'integer',
        'amount_minor' => 'integer',
        'minimum_subtotal_minor' => 'integer',
        'usage_limit' => 'integer',
        'per_customer_limit' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'coupon_category');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
