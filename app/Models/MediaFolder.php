<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class MediaFolder extends Model { protected $guarded=[]; public function parent(){return $this->belongsTo(self::class,'parent_id');} public function children(){return $this->hasMany(self::class,'parent_id');} public function assets(){return $this->hasMany(MediaAsset::class,'folder_id');} }
