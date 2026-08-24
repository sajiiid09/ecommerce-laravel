<?php
namespace App\Livewire\Concerns;
trait WithAdminTable { public string $sortField='created_at'; public string $sortDirection='desc'; public int $perPage=15; public array $selected=[]; public bool $selectPage=false; public function sortBy(string $field):void{$this->sortDirection=$this->sortField===$field&&$this->sortDirection==='asc'?'desc':'asc';$this->sortField=$field;} public function clearSelection():void{$this->selected=[];$this->selectPage=false;} }
