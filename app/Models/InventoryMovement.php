<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class InventoryMovement extends Model { public $timestamps=false; protected $guarded=[]; protected $casts=['created_at'=>'datetime']; public function item(){return $this->belongsTo(InventoryItem::class,'inventory_item_id');} public function variant(){return $this->belongsTo(ProductVariant::class,'product_variant_id');} }
