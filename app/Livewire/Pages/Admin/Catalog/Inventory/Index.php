<?php

namespace App\Livewire\Pages\Admin\Catalog\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = 'all';

    public int $perPage = 20;

    public ?int $adjustingItemId = null;

    public int $adjustment = 1;

    public string $movementType = 'adjustment';

    public string $note = '';

    private InventoryService $inventory;

    public function boot(InventoryService $inventory): void
    {
        $this->inventory = $inventory;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function openAdjust(int $id): void
    {
        $item = InventoryItem::with('variant.product')->findOrFail($id);
        Gate::authorize('update', $item->variant->product);

        $this->adjustingItemId = $item->id;
        $this->adjustment = 1;
        $this->movementType = 'adjustment';
        $this->note = '';
    }

    public function closeAdjust(): void
    {
        $this->reset(['adjustingItemId', 'adjustment', 'movementType', 'note']);
        $this->movementType = 'adjustment';
        $this->adjustment = 1;
    }

    public function adjust(): void
    {
        $data = $this->validate([
            'adjustingItemId' => ['required', 'integer', 'exists:inventory_items,id'],
            'adjustment' => ['required', 'integer', 'not_in:0'],
            'movementType' => ['required', 'in:restock,adjustment,damage,return,correction'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $item = InventoryItem::with('variant.product')->findOrFail($data['adjustingItemId']);
        Gate::authorize('update', $item->variant->product);
        $this->inventory->adjust($item->variant, $data['adjustment'], $data['movementType'], $data['note'] ?: null);

        session()->flash('status', 'Inventory adjusted successfully.');
        $this->closeAdjust();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'status']);
        $this->resetPage();
    }

    public function render()
    {
        $base = InventoryItem::query()->with('variant.product');
        $this->applyFilters($base);

        $rows = (clone $base)->latest('inventory_items.updated_at')->paginate($this->perPage);
        $all = InventoryItem::query();

        $stats = [
            'total_units' => (int) (clone $all)->sum('quantity_on_hand'),
            'inventory_value_minor' => (int) (clone $all)->join('product_variants', 'product_variants.id', '=', 'inventory_items.product_variant_id')->sum(DB::raw('inventory_items.quantity_on_hand * COALESCE(product_variants.cost_price_minor, product_variants.regular_price_minor, 0)')),
            'low_stock' => (int) (clone $all)->where('track_quantity', true)->whereRaw('(quantity_on_hand - quantity_reserved) > 0')->whereRaw('(quantity_on_hand - quantity_reserved) <= low_stock_threshold')->count(),
            'out_of_stock' => (int) (clone $all)->whereRaw('(quantity_on_hand - quantity_reserved) <= 0')->count(),
            'backorders' => (int) (clone $all)->where('allow_backorders', true)->count(),
            'not_tracked' => (int) (clone $all)->where('track_quantity', false)->count(),
        ];

        $alerts = [
            'Out of Stock' => $stats['out_of_stock'],
            'Low Stock' => $stats['low_stock'],
            'Backorders' => $stats['backorders'],
        ];

        $recentMovements = InventoryMovement::query()
            ->with('variant.product')
            ->latest('created_at')
            ->limit(5)
            ->get();

        $adjustingItem = $this->adjustingItemId
            ? InventoryItem::with('variant.product')->find($this->adjustingItemId)
            : null;

        return view('livewire.pages.admin.catalog.inventory.index', compact('rows', 'stats', 'alerts', 'recentMovements', 'adjustingItem'));
    }

    private function applyFilters($query): void
    {
        $query->when($this->search, function ($query): void {
            $term = '%'.$this->search.'%';
            $query->whereHas('variant', function ($variant) use ($term): void {
                $variant->where('sku', 'like', $term)
                    ->orWhereHas('product', fn ($product) => $product->where('name', 'like', $term));
            });
        });

        match ($this->status) {
            'in_stock' => $query->whereRaw('(quantity_on_hand - quantity_reserved) > low_stock_threshold'),
            'low_stock' => $query->where('track_quantity', true)->whereRaw('(quantity_on_hand - quantity_reserved) > 0')->whereRaw('(quantity_on_hand - quantity_reserved) <= low_stock_threshold'),
            'out_of_stock' => $query->whereRaw('(quantity_on_hand - quantity_reserved) <= 0'),
            'backorder' => $query->where('allow_backorders', true),
            'not_tracked' => $query->where('track_quantity', false),
            default => null,
        };
    }
}
