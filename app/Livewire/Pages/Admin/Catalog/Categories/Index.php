<?php

namespace App\Livewire\Pages\Admin\Catalog\Categories;

use App\Livewire\Pages\Admin\Catalog\ResourceIndex;
use App\Models\Category;
use App\Models\MediaAsset;
use App\Services\CategoryService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class Index extends ResourceIndex
{
    public string $status = '';

    public ?int $parentFilter = null;

    public ?int $parent_id = null;

    public ?int $editingId = null;

    public ?int $media_asset_id = null;

    public bool $is_active = true;

    public int $sort_order = 0;

    public ?string $description = null;

    public string $slug = '';

    public bool $showMediaPicker = false;

    protected CategoryService $categories;

    public function boot(CategoryService $categories): void
    {
        $this->categories = $categories;
    }

    protected function model(): string
    {
        return Category::class;
    }

    protected function title(): string
    {
        return 'Categories';
    }

    protected function rows()
    {
        return Category::query()
            ->with('parent')
            ->withCount('products')
            ->when($this->searchQuery, fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', '%'.$this->searchQuery.'%')
                ->orWhere('slug', 'like', '%'.$this->searchQuery.'%')))
            ->when($this->status !== '', fn ($query) => $query->where('is_active', $this->status === 'active'))
            ->when($this->parentFilter, fn ($query) => $query->where('parent_id', $this->parentFilter))
            ->when($this->sortBy !== '', fn ($query) => $this->applyCatalogSorting($query))
            ->when($this->sortBy === '', fn ($query) => $query
                ->orderByRaw('COALESCE(parent_id, 0) asc')
                ->orderBy('sort_order')
                ->orderBy('name'));
    }

    public function openCreate(): void
    {
        $this->authorize('create', Category::class);
        $this->resetEditor();
        $this->dispatch('open-modal', id: 'category-editor');
    }

    public function openEdit(int $id): void
    {
        $category = Category::findOrFail($id);
        $this->authorize('update', $category);

        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->description = $category->description;
        $this->parent_id = $category->parent_id;
        $this->is_active = $category->is_active;
        $this->sort_order = $category->sort_order;
        $this->media_asset_id = $category->media_asset_id;
        $this->showMediaPicker = false;
        $this->resetValidation();
        $this->dispatch('open-modal', id: 'category-editor');
    }

    public function saveCategory(): void
    {
        $this->authorize($this->editingId ? 'update' : 'create', $this->editingId ? Category::findOrFail($this->editingId) : Category::class);
        $this->slug = $this->slug !== '' ? $this->slug : $this->name;

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($this->editingId)],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'media_asset_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'is_active' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        try {
            $this->categories->save($validated, $this->editingId ? Category::findOrFail($this->editingId) : null);
        } catch (InvalidArgumentException $exception) {
            $this->addError('parent_id', $exception->getMessage());

            return;
        }

        $this->dispatch('close-modal', id: 'category-editor');
        $this->resetEditor();
        $this->resetPage();
    }

    public function selectMedia(int $id): void
    {
        $asset = MediaAsset::findOrFail($id);
        Gate::authorize('view', $asset);
        $this->media_asset_id = $asset->id;
        $this->showMediaPicker = false;
    }

    public function toggleStatus(int $id): void
    {
        $category = Category::findOrFail($id);
        $this->authorize('update', $category);
        $category->update(['is_active' => ! $category->is_active]);
    }

    public function bulk(string $action): void
    {
        $this->validate(['selectedIds' => ['array']]);

        foreach (Category::whereKey($this->selectedIds)->get() as $category) {
            $this->authorize($action === 'delete' ? 'delete' : 'update', $category);

            if ($action === 'delete') {
                $category->loadCount(['children', 'products']);
                if ($category->children_count > 0 || $category->products_count > 0) {
                    $this->addError('delete', 'Categories with children or product assignments cannot be deleted.');

                    continue;
                }
                $category->delete();
            } elseif ($action === 'activate' || $action === 'deactivate') {
                $category->update(['is_active' => $action === 'activate']);
            }
        }

        $this->clearSelection();
        $this->resetPage();
    }

    public function deleteSelected(): void
    {
        $this->bulk('delete');
    }

    public function deleteCategory(int $id): void
    {
        $category = Category::withCount(['children', 'products'])->findOrFail($id);
        $this->authorize('delete', $category);

        if ($category->children_count > 0 || $category->products_count > 0) {
            $this->addError('delete', 'Remove child categories and product assignments before deleting this category.');

            return;
        }

        $category->delete();
        $this->selectedIds = array_values(array_diff($this->selectedIds, [$id, (string) $id]));
    }

    public function resetFilters(): void
    {
        $this->reset(['searchQuery', 'status', 'parentFilter']);
        $this->clearSelection();
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedParentFilter(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    private function resetEditor(): void
    {
        $this->reset(['editingId', 'name', 'slug', 'parent_id', 'description', 'media_asset_id', 'sort_order']);
        $this->is_active = true;
        $this->sort_order = 0;
        $this->showMediaPicker = false;
        $this->resetValidation();
    }

    public function render()
    {
        $this->authorize('viewAny', Category::class);
        $rows = $this->rows()->paginate($this->perPage);
        $parentIds = Category::pluck('parent_id', 'id');
        $depths = [];

        foreach ($rows as $row) {
            $depth = 0;
            $parentId = $row->parent_id;
            while ($parentId && $depth < 10) {
                $depth++;
                $parentId = $parentIds[$parentId] ?? null;
            }
            $depths[$row->id] = $depth;
        }

        return view('livewire.pages.admin.catalog.categories.index', [
            'rows' => tap($rows, fn ($paginator) => $this->syncVisibleIds($paginator)),
            'parents' => Category::orderBy('name')->get(),
            'mediaAssets' => MediaAsset::latest()->limit(18)->get(),
            'selectedMedia' => $this->media_asset_id ? MediaAsset::find($this->media_asset_id) : null,
            'depths' => $depths,
        ]);
    }
}
