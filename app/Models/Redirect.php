<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Redirect extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'from_path',
        'to_url',
        'status_code',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'deleted_at' => 'datetime',
        'last_hit_at' => 'datetime',
    ];

    public function hits()
    {
        return $this->hasMany(RedirectHit::class);
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }
}
