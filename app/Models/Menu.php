<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = [
        'name',
        'key',
        'location',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(MenuItem::class);
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }
}
