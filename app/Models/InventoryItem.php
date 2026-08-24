<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class InventoryItem extends Model { protected $guarded=[]; protected $casts=['track_quantity'=>'boolean','allow_backorders'=>'boolean']; public function variant(){return $this->belongsTo(ProductVariant::class,'product_variant_id');} public function movements(){return $this->hasMany(InventoryMovement::class);} public function availableQuantity(){return $this->quantity_on_hand-$this->quantity_reserved;} public function isLowStock(){return $this->track_quantity && $this->availableQuantity() <= $this->low_stock_threshold && $this->availableQuantity() > 0;} }
