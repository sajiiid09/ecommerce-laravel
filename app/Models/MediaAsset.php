<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class MediaAsset extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'folder_id',
        'disk',
        'path',
        'filename',
        'original_filename',
        'extension',
        'mime_type',
        'size',
        'width',
        'height',
        'checksum',
        'alt_text',
        'title',
        'caption',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'deleted_at' => 'datetime',
    ];

    public function folder()
    {
        return $this->belongsTo(MediaFolder::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function usages()
    {
        return $this->hasMany(MediaUsage::class);
    }

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function isUsed(): bool
    {
        return $this->usages()->exists();
    }
}
