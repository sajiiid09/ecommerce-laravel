<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaUsage extends Model
{
    protected $fillable = [
        'media_asset_id',
        'usable_type',
        'usable_id',
        'role',
    ];

    public function asset()
    {
        return $this->belongsTo(MediaAsset::class, 'media_asset_id');
    }

    public function usable()
    {
        return $this->morphTo();
    }
}
