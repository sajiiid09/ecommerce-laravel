<?php

namespace App\Livewire\Concerns;

trait WithCatalogTable
{
    use WithCatalogPagination;
    use WithCatalogSearch;
    use WithCatalogSelection;
    use WithCatalogSorting;

    public bool $filtersOpen = false;

    public function toggleFilters(): void
    {
        $this->filtersOpen = ! $this->filtersOpen;
    }

    public function resetCatalogFilters(): void
    {
        $this->searchQuery = '';
        $this->sortBy = '';
        $this->sortDir = 'asc';
        $this->selectedIds = [];
        $this->resetPage();
    }
}
