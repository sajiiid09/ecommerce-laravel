<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaFolder extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function assets()
    {
        return $this->hasMany(MediaAsset::class, 'folder_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }
}
