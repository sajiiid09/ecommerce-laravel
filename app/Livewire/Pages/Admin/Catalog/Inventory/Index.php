<?php
namespace App\Livewire\Pages\Admin\Catalog\Inventory;
use App\Models\InventoryItem; use App\Services\InventoryService; use Livewire\Component; use Livewire\WithPagination;
class Index extends Component
{
    use WithPagination; public string $search=''; public int $adjustment=1; public string $movementType='adjustment'; public string $note='';
    public function adjust(int $id):void{$this->validate(['adjustment'=>'required|integer|not_in:0','movementType'=>'required|in:restock,adjustment,damage,return,correction','note'=>'nullable|string|max:500']);$item=InventoryItem::with('variant')->findOrFail($id);app(InventoryService::class)->adjust($item->variant,$this->adjustment,$this->movementType,$this->note?:null);$this->reset(['adjustment','movementType','note']);}
    public function render(){$rows=InventoryItem::with('variant.product')->when($this->search,fn($q)=>$q->whereHas('variant',fn($v)=>$v->where('sku','like','%'.$this->search.'%')->orWhereHas('product',fn($p)=>$p->where('name','like','%'.$this->search.'%'))))->latest()->paginate(20);return view('livewire.pages.admin.catalog.inventory.index',compact('rows'));}
}
