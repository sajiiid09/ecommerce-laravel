<?php

namespace App\Livewire\Concerns;

use Illuminate\Pagination\LengthAwarePaginator;

trait WithCatalogSelection
{
    public array $selectedIds = [];

    public array $visibleIds = [];

    protected function syncVisibleIds(LengthAwarePaginator $rows): void
    {
        $this->visibleIds = $rows->getCollection()
            ->pluck('id')
            ->map(static fn (int|string $id): string => (string) $id)
            ->all();

        $visibleIds = $this->visibleIds;
        $this->selectedIds = array_values(array_filter(
            $this->selectedIds,
            static fn (int|string $id): bool => in_array((string) $id, $visibleIds, true)
        ));
    }

    public function deselectAll(): void
    {
        $this->selectedIds = [];
    }

    public function clearSelection(): void
    {
        $this->deselectAll();
    }
}
