<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attribute extends Model
{
    protected $table = 'attributes';

    protected $fillable = [
        'name', 'slug', 'type', 'unit', 'is_filterable', 'is_required',
        'is_active', 'sort_order',
    ];

    protected $casts = ['is_filterable' => 'boolean', 'is_required' => 'boolean', 'is_active' => 'boolean'];

    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_attribute_values');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
