<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class MediaAsset extends Model { protected $guarded=[]; public function folder(){return $this->belongsTo(MediaFolder::class);} public function usages(){return $this->hasMany(MediaUsage::class);} public function url(){return \Illuminate\Support\Facades\Storage::disk($this->disk)->url($this->path);} }
