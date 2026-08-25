<?php

namespace App\Livewire\Pages\Admin\Catalog\Attributes;

use App\Livewire\Concerns\WithAdminTable;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Index extends Component
{
    use WithAdminTable;

    public string $search = '';

    public string $name = '';

    public string $slug = '';

    public string $type = 'text';

    public string $unit = '';

    public bool $is_filterable = false;

    public bool $is_required = false;

    public bool $is_active = true;

    public int $sort_order = 0;

    public string $values_text = '';

    public ?int $editingId = null;

    public function mount(): void
    {
        $this->authorize('viewAny', Attribute::class);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->authorize('create', Attribute::class);
        $this->resetEditor();
        $this->dispatch('open-modal', id: 'attribute-editor');
    }

    public function openEdit(int $id): void
    {
        $attribute = Attribute::with('values')->findOrFail($id);
        $this->authorize('update', $attribute);
        $this->editingId = $attribute->id;
        $this->name = $attribute->name;
        $this->slug = $attribute->slug;
        $this->type = $attribute->type;
        $this->unit = (string) $attribute->unit;
        $this->is_filterable = $attribute->is_filterable;
        $this->is_required = $attribute->is_required;
        $this->is_active = $attribute->is_active;
        $this->sort_order = $attribute->sort_order;
        $this->values_text = $attribute->values->pluck('value')->implode(PHP_EOL);
        $this->resetValidation();
        $this->dispatch('open-modal', id: 'attribute-editor');
    }

    public function saveAttribute(): void
    {
        $attribute = $this->editingId ? Attribute::findOrFail($this->editingId) : null;
        $this->authorize($attribute ? 'update' : 'create', $attribute ?: Attribute::class);
        $this->slug = $this->slug !== '' ? Str::slug($this->slug) : Str::slug($this->name);
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('attributes', 'slug')->ignore($this->editingId)],
            'type' => ['required', Rule::in(['text', 'number', 'boolean', 'select', 'multi_select'])],
            'unit' => ['nullable', 'string', 'max:50'],
            'is_filterable' => ['boolean'],
            'is_required' => ['boolean'],
            'is_active' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'values_text' => ['nullable', 'string'],
        ]);

        try {
            $values = $this->normalizedValues($this->values_text);
            if (in_array($data['type'], ['select', 'multi_select'], true) && $values === []) {
                throw new InvalidArgumentException('Select attributes require at least one option value.');
            }

            DB::transaction(function () use ($attribute, $data, $values): void {
                $attribute ??= new Attribute;
                $attribute->fill([
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'type' => $data['type'],
                    'unit' => $data['unit'] ?: null,
                    'is_filterable' => $data['is_filterable'],
                    'is_required' => $data['is_required'],
                    'is_active' => $data['is_active'],
                    'sort_order' => $data['sort_order'],
                ])->save();

                $this->syncValues($attribute, $values, $data['type']);
            });
        } catch (InvalidArgumentException $exception) {
            $this->addError('values_text', $exception->getMessage());

            return;
        }

        $this->dispatch('close-modal', id: 'attribute-editor');
        $this->resetEditor();
        $this->resetPage();
    }

    public function toggleActive(int $id): void
    {
        $attribute = Attribute::findOrFail($id);
        $this->authorize('update', $attribute);
        $attribute->update(['is_active' => ! $attribute->is_active]);
    }

    public function deleteAttribute(int $id): void
    {
        $attribute = Attribute::withCount('products')->findOrFail($id);
        $this->authorize('delete', $attribute);

        if ($attribute->products_count > 0) {
            $this->addError('delete', 'Attributes assigned to products cannot be deleted. Deactivate the attribute instead.');

            return;
        }

        $attribute->delete();
    }

    public function render()
    {
        $rows = Attribute::query()
            ->withCount(['values', 'products'])
            ->when($this->search, fn ($query) => $query->where(fn ($search) => $search
                ->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('slug', 'like', '%'.$this->search.'%')))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.pages.admin.catalog.attributes.index', [
            'rows' => $rows,
            'stats' => [
                'Total attributes' => Attribute::count(),
                'Active' => Attribute::where('is_active', true)->count(),
                'Filterable' => Attribute::where('is_filterable', true)->count(),
                'With products' => Attribute::has('products')->count(),
            ],
        ]);
    }

    private function resetEditor(): void
    {
        $this->reset(['name', 'slug', 'unit', 'values_text', 'editingId']);
        $this->type = 'text';
        $this->is_filterable = false;
        $this->is_required = false;
        $this->is_active = true;
        $this->sort_order = 0;
        $this->resetValidation();
    }

    /** @return array<int, string> */
    private function normalizedValues(string $valuesText): array
    {
        $values = collect(preg_split('/\R/', $valuesText) ?: [])
            ->map(fn (?string $value): string => trim((string) $value))
            ->filter()
            ->values();

        if ($values->duplicates(fn (string $value): string => Str::lower($value))->isNotEmpty()) {
            throw new InvalidArgumentException('Attribute option values must be unique.');
        }

        return $values->all();
    }

    /** @param array<int, string> $values */
    private function syncValues(Attribute $attribute, array $values, string $type): void
    {
        if (! in_array($type, ['select', 'multi_select'], true)) {
            if ($attribute->values()->exists()) {
                throw new InvalidArgumentException('Remove option values before changing this attribute to a scalar type.');
            }

            return;
        }

        $existing = $attribute->values()->get()->keyBy('slug');
        $kept = [];
        foreach ($values as $sortOrder => $value) {
            $slug = Str::slug($value);
            if ($slug === '' || isset($kept[$slug])) {
                throw new InvalidArgumentException('Attribute option values must have unique slugs.');
            }

            $record = $existing->get($slug) ?: new AttributeValue(['attribute_id' => $attribute->id]);
            $record->fill(['value' => $value, 'slug' => $slug, 'sort_order' => $sortOrder])->save();
            $kept[$slug] = true;
        }

        foreach ($existing as $record) {
            if (! isset($kept[$record->slug]) && $record->products()->exists()) {
                throw new InvalidArgumentException('Assigned option values cannot be removed from an attribute.');
            }
        }

        $attribute->values()->whereNotIn('slug', array_keys($kept))->delete();
    }
}
