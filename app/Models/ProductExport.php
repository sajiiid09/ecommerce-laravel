<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductExport extends Model
{
    protected $fillable = [
        'type', 'format', 'scope', 'filters', 'fields', 'include_variants', 'record_count',
        'disk', 'path', 'filename', 'status', 'completed_at',
    ];

    protected $casts = ['filters' => 'array', 'fields' => 'array', 'include_variants' => 'boolean', 'completed_at' => 'datetime'];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
