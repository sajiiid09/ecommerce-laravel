<?php
namespace App\Livewire\Pages\Admin\Catalog\Products;
use App\Enums\ProductStatus; use App\Livewire\Pages\Admin\Catalog\ResourceIndex; use App\Models\Product;
class Index extends ResourceIndex
{
    public string $status=''; protected function model():string{return Product::class;} protected function title():string{return 'Products';}
    protected function rows(){return Product::with(['brand','primaryCategory','defaultVariant.inventory'])->when($this->search,fn($q)=>$q->search($this->search))->when($this->status,fn($q)=>$q->where('status',$this->status))->orderBy($this->sortField,$this->sortDirection);}
    public function bulk(string $action):void{$this->validate(['selected'=>'array']);$query=Product::whereKey($this->selected);match($action){'publish'=>$query->update(['status'=>ProductStatus::Published->value]),'draft'=>$query->update(['status'=>ProductStatus::Draft->value]),'archive'=>$query->update(['status'=>ProductStatus::Archived->value]),'feature'=>$query->update(['is_featured'=>true]),'unfeature'=>$query->update(['is_featured'=>false]),'delete'=>$query->delete(),default=>null};$this->selected=[];}
    public function render(){return view('livewire.pages.admin.catalog.products.index',['rows'=>$this->rows()->paginate($this->perPage),'title'=>$this->title()]);}
}
