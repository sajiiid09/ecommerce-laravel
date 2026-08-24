<?php
namespace App\Livewire\Pages\Admin\Catalog\Brands; use App\Livewire\Pages\Admin\Catalog\ResourceIndex; use App\Models\Brand; class Index extends ResourceIndex { protected function model():string{return Brand::class;} protected function title():string{return 'Brands';} }
