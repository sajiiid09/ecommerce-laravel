<?php

namespace App\Livewire\Concerns;

use Livewire\WithPagination;

trait WithAdminTable
{
    use WithPagination;

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public int $perPage = 15;

    public array $selected = [];

    public bool $selectPage = false;

    public function sortBy(string $field): void
    {
        $this->sortDirection = $this->sortField === $field && $this->sortDirection === 'asc' ? 'desc' : 'asc';
        $this->sortField = $field;
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedSelectPage(bool $selected): void
    {
        if (! method_exists($this, 'rows')) {
            return;
        }

        $this->selected = $selected ? $this->rows()->forPage($this->getPage(), $this->perPage)->pluck('id')->all() : [];
    }

    public function togglePageSelection(): void
    {
        $ids = method_exists($this, 'rows')
            ? $this->rows()->forPage($this->getPage(), $this->perPage)->pluck('id')->all()
            : [];

        $this->selected = count(array_diff($ids, $this->selected)) ? $ids : [];
        $this->selectPage = $this->selected !== [];
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectPage = false;
    }

    protected function resetTablePage(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }
}
