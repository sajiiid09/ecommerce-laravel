<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryImport extends Model
{
    protected $fillable = [
        'filename', 'disk', 'path', 'mode', 'status', 'total_rows', 'valid_rows',
        'error_rows', 'created_count', 'updated_count', 'validation_summary', 'completed_at',
    ];

    protected $casts = ['validation_summary' => 'array', 'completed_at' => 'datetime'];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
