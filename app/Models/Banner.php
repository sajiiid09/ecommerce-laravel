<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'placement',
        'eyebrow',
        'title',
        'description',
        'cta_label',
        'destination_type',
        'destination_value',
        'desktop_media_id',
        'mobile_media_id',
        'side_media_id',
        'side_image_mode',
        'status',
        'starts_at',
        'ends_at',
        'sort_order',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function desktopMedia()
    {
        return $this->belongsTo(MediaAsset::class, 'desktop_media_id');
    }

    public function mobileMedia()
    {
        return $this->belongsTo(MediaAsset::class, 'mobile_media_id');
    }

    public function sideMedia()
    {
        return $this->belongsTo(MediaAsset::class, 'side_media_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->where(fn (Builder $query): Builder => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $query): Builder => $query->whereNull('ends_at')->orWhere('ends_at', '>', now()));
    }
}
