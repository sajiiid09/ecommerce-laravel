<?php

namespace App\Livewire\Concerns;

use Livewire\WithPagination;

trait WithCatalogPagination
{
    use WithPagination;

    public int $perPage = 15;

    public function updatedWithCatalogPagination(string $property): void
    {
        if ($property === 'perPage') {
            $this->resetPage();
        }
    }
}
