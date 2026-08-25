<?php

namespace App\Livewire\Pages\Admin\Catalog\Categories;

use App\Models\Category;
use App\Models\CategoryExport;
use App\Services\CategoryExportService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Export extends Component
{
    public ?CategoryExport $export = null;

    protected CategoryExportService $categories;

    public function boot(CategoryExportService $categories): void
    {
        $this->categories = $categories;
    }

    public function export(): void
    {
        $this->authorize('export', Category::class);
        $this->export = $this->categories->export();
        session()->flash('status', 'Category export created successfully.');
    }

    public function render()
    {
        $this->authorize('viewAny', Category::class);

        return view('livewire.pages.admin.catalog.categories.export');
    }
}
