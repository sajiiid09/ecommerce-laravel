<?php
namespace App\Livewire\Pages\Admin\Catalog\Variants; use App\Models\ProductVariant; use Livewire\Component; use Livewire\WithPagination;
class Index extends Component { use WithPagination; public string $search=''; public function render(){ $rows=ProductVariant::with(['product','inventory'])->when($this->search,fn($q)=>$q->where('sku','like','%'.$this->search.'%')->orWhereHas('product',fn($p)=>$p->where('name','like','%'.$this->search.'%')))->latest()->paginate(20); return view('livewire.pages.admin.catalog.variants.index',compact('rows')); } }
