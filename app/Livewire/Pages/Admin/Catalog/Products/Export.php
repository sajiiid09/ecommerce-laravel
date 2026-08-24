<?php
namespace App\Livewire\Pages\Admin\Catalog\Products;
use App\Services\ProductExportService; use Livewire\Component;
class Export extends Component { public bool $includeVariants=false; public ?string $download=null; public function export():void{$file=app(ProductExportService::class)->export(includeVariants:$this->includeVariants);$this->download=\Illuminate\Support\Facades\Storage::disk('local')->url($file->path);session()->flash('status','Export created: '.$file->filename);} public function render(){return view('livewire.pages.admin.catalog.products.export');} }
