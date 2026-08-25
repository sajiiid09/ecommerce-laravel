<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'inventory_item_id', 'product_variant_id', 'type', 'quantity_delta',
        'quantity_before', 'quantity_after', 'reference_type', 'reference_id',
        'note', 'created_by', 'created_at',
    ];

    protected $casts = ['created_at' => 'datetime'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
