<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImport extends Model
{
    protected $fillable = [
        'filename', 'disk', 'path', 'mode', 'total_rows', 'valid_rows', 'warning_rows',
        'error_rows', 'created_count', 'updated_count', 'skipped_count', 'status',
        'validation_summary', 'mapping', 'started_at', 'completed_at',
    ];

    protected $casts = ['validation_summary' => 'array', 'mapping' => 'array', 'started_at' => 'datetime', 'completed_at' => 'datetime'];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
