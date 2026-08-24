<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Tag extends Model { protected $guarded=[]; protected $casts=['is_active'=>'boolean']; public function products(){return $this->belongsToMany(Product::class);} }
