<?php
namespace App\Livewire\Pages\Admin\Catalog\Attributes; use App\Livewire\Pages\Admin\Catalog\ResourceIndex; use App\Models\Attribute; class Index extends ResourceIndex { protected function model():string{return Attribute::class;} protected function title():string{return 'Attributes / Options';} }
