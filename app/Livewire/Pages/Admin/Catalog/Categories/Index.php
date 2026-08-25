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
            ->when($this->search, fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('slug', 'like', '%'.$this->search.'%')))
            ->when($this->status !== '', fn ($query) => $query->where('is_active', $this->status === 'active'))
            ->when($this->parentFilter, fn ($query) => $query->where('parent_id', $this->parentFilter))
            ->orderByRaw('COALESCE(parent_id, 0) asc')
            ->orderBy('sort_order')
            ->orderBy('name');
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

    public function deleteCategory(int $id): void
    {
        $category = Category::withCount(['children', 'products'])->findOrFail($id);
        $this->authorize('delete', $category);

        if ($category->children_count > 0 || $category->products_count > 0) {
            $this->addError('delete', 'Remove child categories and product assignments before deleting this category.');

            return;
        }

        $category->delete();
        $this->selected = array_values(array_diff($this->selected, [$id]));
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'status', 'parentFilter']);
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
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
            'rows' => $rows,
            'parents' => Category::orderBy('name')->get(),
            'mediaAssets' => MediaAsset::latest()->limit(18)->get(),
            'selectedMedia' => $this->media_asset_id ? MediaAsset::find($this->media_asset_id) : null,
            'depths' => $depths,
            'stats' => [
                'total' => Category::count(),
                'active' => Category::where('is_active', true)->count(),
                'top_level' => Category::whereNull('parent_id')->count(),
                'assigned' => Category::withCount('products')->get()->sum('products_count'),
            ],
            'overview' => Category::withCount('products')->orderByDesc('products_count')->orderBy('name')->limit(5)->get(),
            'health' => [
                'without_products' => Category::whereDoesntHave('products')->count(),
                'without_image' => Category::whereNull('media_asset_id')->whereNull('image_url')->count(),
                'inactive' => Category::where('is_active', false)->count(),
            ],
        ]);
    }
}
