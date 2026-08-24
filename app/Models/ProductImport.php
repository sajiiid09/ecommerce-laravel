<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class ProductImport extends Model { protected $guarded=[]; protected $casts=['validation_summary'=>'array','mapping'=>'array','started_at'=>'datetime','completed_at'=>'datetime']; public function uploader(){return $this->belongsTo(User::class,'uploaded_by');} }
