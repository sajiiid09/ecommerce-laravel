<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\SoftDeletes;
class Category extends Model { use SoftDeletes; protected $guarded=[]; protected $casts=['is_active'=>'boolean']; public function parent(){return $this->belongsTo(self::class,'parent_id');} public function children(){return $this->hasMany(self::class,'parent_id');} public function products(){return $this->belongsToMany(Product::class);} public function scopeActive($q){return $q->where('is_active',true);} }
