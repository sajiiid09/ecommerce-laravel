<?php

namespace App\Livewire\Concerns;

trait WithCatalogSorting
{
    public string $sortBy = '';

    public string $sortDir = 'asc';

    public function sortByColumn(string $column, ?string $direction = null): void
    {
        if (! $this->isSortableColumn($column)) {
            return;
        }

        if ($direction === '') {
            $this->clearSorting();

            return;
        }

        if ($direction !== null) {
            $this->sortBy = $column;
            $this->sortDir = $direction === 'desc' ? 'desc' : 'asc';
            $this->resetPage();

            return;
        }

        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = 'asc';
        }

        $this->resetPage();
    }

    public function clearSorting(): void
    {
        $this->sortBy = '';
        $this->sortDir = 'asc';
        $this->resetPage();
    }

    protected function applyCatalogSorting($query)
    {
        if ($this->sortBy === '' || ! $this->isSortableColumn($this->sortBy)) {
            return $query;
        }

        return $query->orderBy($this->sortBy, $this->sortDir === 'desc' ? 'desc' : 'asc');
    }

    protected function isSortableColumn(string $column): bool
    {
        return in_array($column, $this->sortableColumns(), true);
    }

    /** @return array<int, string> */
    protected function sortableColumns(): array
    {
        return [];
    }
}
