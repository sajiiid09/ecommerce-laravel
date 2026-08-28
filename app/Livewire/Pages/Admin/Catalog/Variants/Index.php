<?php

namespace App\Livewire\Pages\Admin\Catalog\Variants;

use App\Livewire\Concerns\WithCatalogTable;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Index extends Component
{
    use WithCatalogTable;

    public string $status = 'all';

    public ?ProductVariant $viewingVariant = null;

    public function mount(): void
    {
        $this->perPage = 20;
    }

    public function updatedSearchQuery(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function resetFilters(): void
    {
        $this->reset(['searchQuery', 'status']);
        $this->clearSelection();
        $this->resetPage();
    }

    public function showVariant(int $variantId): void
    {
        $variant = ProductVariant::query()
            ->with(['product', 'inventory', 'optionValues'])
            ->findOrFail($variantId);

        Gate::authorize('view', $variant);

        $this->viewingVariant = $variant;
        $this->dispatch('open-modal', id: 'variant-details');
    }

    public function delete(int $variantId): void
    {
        $variant = ProductVariant::query()->with('product')->findOrFail($variantId);
        Gate::authorize('delete', $variant);

        $product = $variant->product;
        $hasOtherVariant = $product?->variants()
            ->where('id', '!=', $variant->id)
            ->exists();

        if (! $hasOtherVariant) {
            $this->addError('variant', 'A product must retain at least one variant.');

            return;
        }

        if ($variant->is_default) {
            $replacement = $product->variants()
                ->where('id', '!=', $variant->id)
                ->orderByDesc('is_active')
                ->orderBy('sort_order')
                ->first();

            $replacement?->update(['is_default' => true]);
        }

        $variant->delete();
        $this->viewingVariant = null;
        $this->clearSelection();
        $this->resetPage();
        session()->flash('status', 'Variant deleted.');
    }

    /** @return array<int, string> */
    protected function sortableColumns(): array
    {
        return ['sku', 'is_active', 'created_at'];
    }

    protected function rows(): Builder
    {
        return ProductVariant::query()
            ->with(['product', 'inventory', 'optionValues'])
            ->when($this->searchQuery, fn (Builder $query): Builder => $query->where(function (Builder $search): void {
                $term = '%'.$this->searchQuery.'%';
                $search->where('sku', 'like', $term)
                    ->orWhereHas('product', fn (Builder $product): Builder => $product->where('name', 'like', $term));
            }))
            ->when($this->status === 'active', fn (Builder $query): Builder => $query->where('is_active', true))
            ->when($this->status === 'inactive', fn (Builder $query): Builder => $query->where('is_active', false))
            ->when($this->status === 'low_stock', fn (Builder $query): Builder => $query->whereHas('inventory', function (Builder $inventory): void {
                $inventory->where('track_quantity', true)
                    ->whereRaw('(quantity_on_hand - quantity_reserved) > 0')
                    ->whereRaw('(quantity_on_hand - quantity_reserved) <= low_stock_threshold');
            }))
            ->when($this->status === 'out_of_stock', fn (Builder $query): Builder => $query->whereHas('inventory', fn (Builder $inventory): Builder => $inventory->whereRaw('(quantity_on_hand - quantity_reserved) <= 0')));
    }

    public function render()
    {
        $rows = $this->rows();
        $rows = $this->sortBy !== '' ? $this->applyCatalogSorting($rows) : $rows->latest();
        $rows = $rows->paginate($this->perPage);
        $this->syncVisibleIds($rows);

        return view('livewire.pages.admin.catalog.variants.index', compact('rows'));
    }
}
