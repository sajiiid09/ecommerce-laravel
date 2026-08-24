<?php
namespace App\Livewire\Pages\Admin\Catalog;
use Illuminate\Support\Str; use Livewire\Attributes\Layout; use Livewire\Component; use Livewire\WithPagination;
#[Layout('layouts.admin')]
abstract class ResourceIndex extends Component
{
    use WithPagination;
    public string $search=''; public string $name=''; public string $sortField='created_at'; public string $sortDirection='desc'; public int $perPage=15; public array $selected=[];
    abstract protected function model():string; abstract protected function title():string;
    public function updatedSearch():void{$this->resetPage();}
    public function sortBy(string $field):void{$this->sortDirection=$this->sortField===$field&&$this->sortDirection==='asc'?'desc':'asc';$this->sortField=$field;}
    public function togglePageSelection():void{$ids=$this->rows()->pluck('id')->all();$this->selected=count(array_diff($ids,$this->selected))?$ids:[];}
    public function createRecord():void{$this->validate(['name'=>'required|string|max:255']);$model=$this->model();$data=['name'=>$this->name,'slug'=>Str::slug($this->name)];if($model===\App\Models\Tag::class)$data['type']='product';if($model===\App\Models\Attribute::class)$data['type']='text';$model::create($data);$this->reset('name');}
    public function delete(int $id):void{$this->model()::findOrFail($id)->delete();$this->selected=array_values(array_diff($this->selected,[$id]));}
    protected function rows(){$model=$this->model();return $model::query()->when($this->search,fn($q)=>$q->where(fn($q)=>$q->where('name','like','%'.$this->search.'%')->orWhere('slug','like','%'.$this->search.'%')))->orderBy($this->sortField,$this->sortDirection);}
    public function render(){return view('livewire.pages.admin.catalog.resource-index',['rows'=>$this->rows()->paginate($this->perPage),'title'=>$this->title()]);}
}
