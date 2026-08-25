<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'parent_id',
        'title',
        'slug',
        'page_type',
        'template',
        'excerpt',
        'content_json',
        'content_html',
        'status',
        'visibility',
        'show_in_navigation',
        'is_indexable',
        'open_graph_enabled',
        'published_at',
        'scheduled_at',
        'featured_media_id',
        'meta_title',
        'meta_description',
        'canonical_url',
    ];

    protected $casts = [
        'content_json' => 'array',
        'show_in_navigation' => 'boolean',
        'is_indexable' => 'boolean',
        'open_graph_enabled' => 'boolean',
        'published_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function revisions()
    {
        return $this->hasMany(PageRevision::class);
    }

    public function featuredMedia()
    {
        return $this->belongsTo(MediaAsset::class, 'featured_media_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->where(fn (Builder $query): Builder => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->where(fn (Builder $query): Builder => $query->whereNull('scheduled_at')->orWhere('scheduled_at', '<=', now()));
    }
}
