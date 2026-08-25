<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryExport extends Model
{
    protected $fillable = [
        'type', 'format', 'scope', 'fields', 'record_count', 'disk', 'path', 'filename',
        'status', 'completed_at',
    ];

    protected $casts = ['fields' => 'array', 'completed_at' => 'datetime'];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
