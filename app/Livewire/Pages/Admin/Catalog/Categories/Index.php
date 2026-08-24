<?php
namespace App\Livewire\Pages\Admin\Catalog\Categories;
use App\Livewire\Pages\Admin\Catalog\ResourceIndex; use App\Models\Category; use App\Services\CategoryService;
class Index extends ResourceIndex
{
    public ?int $parent_id=null; public int $sort_order=0; public bool $is_active=true;
    protected function model():string{return Category::class;} protected function title():string{return 'Categories';}
    public function createRecord():void{$this->validate(['name'=>'required|string|max:255','parent_id'=>'nullable|integer','sort_order'=>'integer|min:0']);app(CategoryService::class)->save(['name'=>$this->name,'parent_id'=>$this->parent_id,'sort_order'=>$this->sort_order,'is_active'=>$this->is_active]);$this->reset(['name','parent_id','sort_order']);}
    public function render(){return view('livewire.pages.admin.catalog.categories.index',['rows'=>$this->rows()->with('parent')->paginate($this->perPage),'parents'=>Category::orderBy('name')->get()]);}
}
