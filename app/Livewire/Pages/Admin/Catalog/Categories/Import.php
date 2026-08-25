<?php

namespace App\Livewire\Pages\Admin\Catalog\Categories;

use App\Models\Category;
use App\Models\CategoryImport;
use App\Services\CategoryImportService;
use InvalidArgumentException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class Import extends Component
{
    use WithFileUploads;

    public $file;

    public string $mode = 'create';

    public int $step = 1;

    public ?CategoryImport $import = null;

    public array $summary = [];

    protected CategoryImportService $categories;

    public function boot(CategoryImportService $categories): void
    {
        $this->categories = $categories;
    }

    public function upload(): void
    {
        $this->authorize('import', Category::class);
        $this->validate(['file' => 'required|file|mimes:csv,txt|max:10240', 'mode' => 'required|in:create,update_by_slug']);
        $this->import = $this->categories->upload($this->file, $this->mode);
        $this->step = 2;
    }

    public function validateRows(): void
    {
        $this->authorize('import', Category::class);
        try {
            $this->summary = $this->categories->validate($this->import);
            $this->step = 3;
        } catch (InvalidArgumentException $exception) {
            $this->addError('file', $exception->getMessage());
        }
    }

    public function runImport(): void
    {
        $this->authorize('import', Category::class);
        try {
            $this->summary = $this->categories->import($this->import);
            $this->step = 4;
        } catch (InvalidArgumentException $exception) {
            $this->addError('file', $exception->getMessage());
        }
    }

    public function render()
    {
        $this->authorize('viewAny', Category::class);

        return view('livewire.pages.admin.catalog.categories.import');
    }
}
