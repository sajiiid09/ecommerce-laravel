<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = ['order_id', 'provider', 'method', 'status', 'amount_minor', 'currency', 'provider_reference', 'metadata', 'paid_at', 'failed_at'];

    protected $casts = ['amount_minor' => 'integer', 'metadata' => 'array', 'paid_at' => 'datetime', 'failed_at' => 'datetime'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
