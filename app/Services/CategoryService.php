<?php
namespace App\Services;
use App\Models\Category; use Illuminate\Support\Facades\DB; use Illuminate\Support\Str; use InvalidArgumentException;
class CategoryService
{
    public function save(array $data, ?Category $category=null):Category{$parentId=$data['parent_id']??null;if($category&&$parentId&&($category->id===$parentId||$this->descendsFrom($parentId,$category->id)))throw new InvalidArgumentException('A category cannot be its own ancestor.');return DB::transaction(function()use($data,$category,$parentId){$category??=new Category;$category->fill(['name'=>$data['name'],'slug'=>Str::slug($data['slug']??$data['name']),'description'=>$data['description']??null,'parent_id'=>$parentId,'is_active'=>$data['is_active']??true,'sort_order'=>$data['sort_order']??0]);$category->save();return $category;});}
    private function descendsFrom(int $candidate,int $ancestor):bool{$current=Category::find($candidate);while($current){if((int)$current->parent_id===$ancestor)return true;$current=$current->parent_id?Category::find($current->parent_id):null;}return false;}
}
