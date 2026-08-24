<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class MediaUsage extends Model { protected $guarded=[]; public function asset(){return $this->belongsTo(MediaAsset::class,'media_asset_id');} public function usable(){return $this->morphTo();} }
