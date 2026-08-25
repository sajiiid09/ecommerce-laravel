<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RedirectHit extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'redirect_id',
        'path',
        'ip_hash',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function redirect()
    {
        return $this->belongsTo(Redirect::class);
    }
}
