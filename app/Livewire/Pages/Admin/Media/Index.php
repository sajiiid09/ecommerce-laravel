<?php
namespace App\Livewire\Pages\Admin\Media;
use App\Models\MediaAsset; use App\Services\MediaService; use Livewire\Component; use Livewire\WithFileUploads;
class Index extends Component { use WithFileUploads; public $file; public function upload():void{$this->validate(['file'=>'required|image|max:10240']);app(MediaService::class)->upload($this->file);$this->reset('file');} public function render(){return view('livewire.pages.admin.media.index',['assets'=>MediaAsset::latest()->paginate(24)]);} }
