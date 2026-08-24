<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ProductOptionValue extends Model { protected $guarded=[]; protected $casts=['metadata'=>'array']; public function option(){return $this->belongsTo(ProductOption::class,'product_option_id');} public function variants(){return $this->belongsToMany(ProductVariant::class,'product_variant_option_value');} }
