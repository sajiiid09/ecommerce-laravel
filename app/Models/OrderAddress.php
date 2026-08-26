<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderAddress extends Model
{
    protected $fillable = ['order_id', 'type', 'name', 'phone', 'address_line', 'city', 'district', 'postal_code', 'country'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
