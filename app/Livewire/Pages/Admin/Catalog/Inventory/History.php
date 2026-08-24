<?php
namespace App\Livewire\Pages\Admin\Catalog\Inventory; use App\Models\{InventoryMovement,ProductVariant}; use Livewire\Component; use Livewire\WithPagination;
class History extends Component { use WithPagination; public ProductVariant $variant; public function render(){return view('livewire.pages.admin.catalog.inventory.history',['rows'=>$this->variant->inventory?->movements()->latest('created_at')->paginate(30)]);} }
