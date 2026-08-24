<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ProductAttributeValue extends Model { protected $guarded=[]; protected $casts=['number_value'=>'decimal:4','boolean_value'=>'boolean']; public function product(){return $this->belongsTo(Product::class);} public function attribute(){return $this->belongsTo(Attribute::class);} public function attributeValue(){return $this->belongsTo(AttributeValue::class);} }
