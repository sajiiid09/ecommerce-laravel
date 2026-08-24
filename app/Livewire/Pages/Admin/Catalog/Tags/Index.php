<?php
namespace App\Livewire\Pages\Admin\Catalog\Tags; use App\Livewire\Pages\Admin\Catalog\ResourceIndex; use App\Models\Tag; class Index extends ResourceIndex { protected function model():string{return Tag::class;} protected function title():string{return 'Tags';} }
