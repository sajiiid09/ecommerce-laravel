<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\SoftDeletes;
class Brand extends Model { use SoftDeletes; protected $guarded=[]; protected $casts=['is_active'=>'boolean','is_featured'=>'boolean']; public function products(){return $this->hasMany(Product::class);} public function scopeActive($q){return $q->where('is_active',true);} }
