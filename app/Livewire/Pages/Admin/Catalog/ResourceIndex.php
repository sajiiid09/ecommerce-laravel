<?php

namespace App\Livewire\Pages\Admin\Catalog;

use App\Livewire\Concerns\WithAdminTable;
use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Tag;
use App\Services\CatalogCache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
abstract class ResourceIndex extends Component
{
    use WithAdminTable;

    public string $search = '';

    public string $name = '';

    public bool $showCreateModal = false;

    public ?int $editingId = null;

    public string $editName = '';

    public string $editSlug = '';

    public string $editDescription = '';

    public bool $editIsActive = true;

    public bool $editIsFeatured = false;

    public int $editSortOrder = 0;

    abstract protected function model(): string;

    abstract protected function title(): string;

    public function updatedSearch(): void
    {
        $this->resetTablePage();
    }

    public function createRecord(): void
    {
        $model = $this->model();
        Gate::authorize('create', $model);
        $this->validate(['name' => ['required', 'string', 'max:255']]);

        $data = ['name' => $this->name, 'slug' => Str::slug($this->name)];
        if ($model === Tag::class) {
            $data['type'] = 'product';
        }
        if ($model === Attribute::class) {
            $data['type'] = 'text';
        }

        $model::create($data);
        $this->reset(['name', 'showCreateModal']);
        $this->forgetCatalogCache();
    }

    public function openCreate(): void
    {
        Gate::authorize('create', $this->model());
        $this->reset(['name', 'showCreateModal']);
        $this->dispatch('open-modal', id: 'resource-create');
    }

    public function openEdit(int $id): void
    {
        $record = $this->model()::findOrFail($id);
        Gate::authorize('update', $record);

        $this->editingId = $record->getKey();
        $this->editName = (string) $record->name;
        $this->editSlug = (string) $record->slug;
        $this->editDescription = (string) ($record->description ?? '');
        $this->editIsActive = (bool) ($record->is_active ?? true);
        $this->editIsFeatured = (bool) ($record->is_featured ?? false);
        $this->editSortOrder = (int) ($record->sort_order ?? 0);
        $this->resetValidation();
        $this->dispatch('open-modal', id: 'resource-edit');
    }

    public function updateRecord(): void
    {
        $record = $this->model()::findOrFail($this->editingId);
        Gate::authorize('update', $record);
        $this->editSlug = Str::slug($this->editSlug !== '' ? $this->editSlug : $this->editName);

        $validated = $this->validate([
            'editName' => ['required', 'string', 'max:255'],
            'editSlug' => ['required', 'string', 'max:255', Rule::unique($record->getTable(), 'slug')->ignore($record->getKey())],
        ]);

        $data = [
            'name' => $validated['editName'],
            'slug' => $validated['editSlug'],
        ];

        if ($record instanceof Brand) {
            $this->validate([
                'editDescription' => ['nullable', 'string', 'max:5000'],
                'editIsActive' => ['boolean'],
                'editIsFeatured' => ['boolean'],
                'editSortOrder' => ['required', 'integer', 'min:0'],
            ]);
            $data += [
                'description' => $this->editDescription ?: null,
                'is_active' => $this->editIsActive,
                'is_featured' => $this->editIsFeatured,
                'sort_order' => $this->editSortOrder,
            ];
        } elseif ($record instanceof Tag) {
            $this->validate(['editIsActive' => ['boolean']]);
            $data['is_active'] = $this->editIsActive;
        }

        $record->update($data);
        $this->forgetCatalogCache();
        $this->resetEditor();
        $this->dispatch('close-modal', id: 'resource-edit');
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $record = $this->model()::findOrFail($id);
        Gate::authorize('delete', $record);
        $record->delete();
        $this->selected = array_values(array_diff($this->selected, [$id]));
        $this->forgetCatalogCache();
    }

    public function deleteSelected(): void
    {
        $this->validate(['selected' => ['array']]);

        foreach ($this->model()::whereKey($this->selected)->get() as $record) {
            Gate::authorize('delete', $record);
            $record->delete();
        }

        $this->clearSelection();
        $this->forgetCatalogCache();
    }

    protected function rows()
    {
        $model = $this->model();
        $query = $model::query()
            ->when($this->search, fn ($query) => $query->where(fn ($search) => $search
                ->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('slug', 'like', '%'.$this->search.'%')))
            ->orderBy($this->sortField, $this->sortDirection);

        if (method_exists($model, 'products')) {
            $query->withCount('products');
        }

        return $query;
    }

    protected function forgetCatalogCache(): void
    {
        app(CatalogCache::class)->forgetAll();
    }

    public function publicUrlFor(object $record): ?string
    {
        return match ($this->model()) {
            Brand::class => route('store.brand', ['slug' => $record->slug]),
            Category::class => route('store.category', ['slug' => $record->slug]),
            default => null,
        };
    }

    public function isBrandResource(): bool
    {
        return $this->model() === Brand::class;
    }

    public function isTagResource(): bool
    {
        return $this->model() === Tag::class;
    }

    private function resetEditor(): void
    {
        $this->reset([
            'editingId',
            'editName',
            'editSlug',
            'editDescription',
            'editIsActive',
            'editIsFeatured',
            'editSortOrder',
        ]);
        $this->editIsActive = true;
        $this->resetValidation();
    }

    public function render()
    {
        $model = $this->model();
        $healthQuery = $model::query();
        if (method_exists($model, 'products')) {
            $healthQuery->withCount('products');
        }

        return view('livewire.pages.admin.catalog.resource-index', [
            'rows' => $this->rows()->paginate($this->perPage),
            'title' => $this->title(),
            'mostUsed' => method_exists($model, 'products')
                ? $model::query()->withCount('products')->orderByDesc('products_count')->limit(4)->get()
                : collect(),
            'health' => [
                'unused' => method_exists($model, 'products')
                    ? (clone $healthQuery)->get()->where('products_count', 0)->count()
                    : 0,
                'inactive' => (clone $healthQuery)->where('is_active', false)->count(),
            ],
        ]);
    }
}
