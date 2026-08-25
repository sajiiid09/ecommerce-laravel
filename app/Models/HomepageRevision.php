<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageRevision extends Model
{
    protected $fillable = [
        'version',
        'snapshot',
        'status',
        'published_at',
    ];

    protected $casts = [
        'snapshot' => 'array',
        'published_at' => 'datetime',
    ];
}
