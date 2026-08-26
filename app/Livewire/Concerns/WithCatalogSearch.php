<?php

namespace App\Livewire\Concerns;

trait WithCatalogSearch
{
    public string $searchQuery = '';

    public function updatedWithCatalogSearch(string $property): void
    {
        if ($property === 'searchQuery') {
            $this->resetPage();
            $this->selectedIds = [];
        }
    }
}
