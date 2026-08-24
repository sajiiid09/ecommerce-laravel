<?php
namespace App\Livewire\Pages\Admin\Catalog\Products;
use App\Models\{Product,ProductOption}; use App\Services\ProductVariantService; use Illuminate\Support\Str; use Livewire\Attributes\Layout; use Livewire\Component;
#[Layout('layouts.admin')]
class Variants extends Component
{
    public Product $product; public string $optionName=''; public string $optionValue=''; public ?int $activeOption=null;
    public function mount(Product $product):void{$this->product=$product;}
    public function addOption():void{$this->validate(['optionName'=>'required|string|max:100']);$this->product->options()->create(['name'=>$this->optionName,'slug'=>Str::slug($this->optionName)]);$this->reset('optionName');$this->product->refresh();}
    public function addValue(int $optionId):void{$this->validate(['optionValue'=>'required|string|max:100']);$option=ProductOption::whereBelongsTo($this->product)->findOrFail($optionId);$option->values()->create(['value'=>$this->optionValue,'slug'=>Str::slug($this->optionValue)]);$this->reset('optionValue');$this->product->refresh();}
    public function generate():void{app(ProductVariantService::class)->generate($this->product);$this->product->refresh();}
    public function toggle(int $id):void{$variant=$this->product->variants()->findOrFail($id);$variant->update(['is_active'=>!$variant->is_active]);}
    public function render(){return view('livewire.pages.admin.catalog.products.variants',['options'=>$this->product->options()->with('values')->orderBy('sort_order')->get(),'variants'=>$this->product->variants()->with('inventory','optionValues')->get()]);}
}
