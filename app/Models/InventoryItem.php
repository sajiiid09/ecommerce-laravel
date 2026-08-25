<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    protected $fillable = [
        'product_variant_id', 'quantity_on_hand', 'quantity_reserved',
        'low_stock_threshold', 'track_quantity', 'allow_backorders',
    ];

    protected $casts = ['track_quantity' => 'boolean', 'allow_backorders' => 'boolean'];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function availableQuantity(): int
    {
        return (int) $this->quantity_on_hand - (int) $this->quantity_reserved;
    }

    public function isLowStock(): bool
    {
        return $this->track_quantity && $this->availableQuantity() <= $this->low_stock_threshold && $this->availableQuantity() > 0;
    }
}
