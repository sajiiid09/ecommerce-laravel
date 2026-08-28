<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'user_id', 'checkout_token', 'customer_name', 'customer_email', 'customer_phone',
        'status', 'payment_status', 'currency', 'subtotal_minor', 'shipping_minor', 'discount_minor',
        'coupon_id', 'coupon_code',
        'tax_minor', 'total_minor', 'delivery_method', 'payment_method', 'placed_at',
    ];

    protected $casts = [
        'subtotal_minor' => 'integer', 'shipping_minor' => 'integer', 'discount_minor' => 'integer',
        'tax_minor' => 'integer', 'total_minor' => 'integer', 'placed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(OrderAddress::class);
    }

    public function shippingAddress(): HasOne
    {
        return $this->hasOne(OrderAddress::class)->where('type', 'shipping');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }
}
