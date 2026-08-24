<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Attribute extends Model { protected $table='attributes'; protected $guarded=[]; protected $casts=['is_filterable'=>'boolean','is_required'=>'boolean','is_active'=>'boolean']; public function values(){return $this->hasMany(AttributeValue::class);} public function products(){return $this->belongsToMany(Product::class,'product_attribute_values');} }
