<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class ProductExport extends Model { protected $guarded=[]; protected $casts=['filters'=>'array','fields'=>'array','include_variants'=>'boolean','completed_at'=>'datetime']; public function requester(){return $this->belongsTo(User::class,'requested_by');} }
