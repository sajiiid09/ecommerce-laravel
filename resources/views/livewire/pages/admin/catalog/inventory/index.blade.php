<div class="p-5 sm:p-8">
    <div class="mx-auto max-w-[1480px]">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm text-slate-500">StoreZ / Catalog / Inventory</p>
                <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-slate-950">Inventory</h1>
                <p class="mt-1 text-sm text-slate-500">Monitor stock levels and manage product variant inventory.</p>
            </div>
            <button type="button" wire:click="openAdjust({{ $rows->first()?->id ?? 0 }})" @disabled(! $rows->first()) wire:loading.attr="disabled" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm disabled:cursor-not-allowed disabled:opacity-50">Adjust stock</button>
        </div>

        @if (session('status'))
            <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('status') }}</div>
        @endif

        <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['label' => 'Total Units', 'value' => number_format($stats['total_units']), 'tone' => 'blue'],
                ['label' => 'Inventory Value', 'value' => '৳'.number_format($stats['inventory_value_minor'] / 100, 2), 'tone' => 'emerald'],
                ['label' => 'Low Stock', 'value' => number_format($stats['low_stock']), 'tone' => 'orange'],
                ['label' => 'Out of Stock', 'value' => number_format($stats['out_of_stock']), 'tone' => 'red'],
            ] as $card)
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold text-slate-500">{{ $card['label'] }}</p>
                    <p class="mt-2 text-2xl font-extrabold text-slate-950">{{ $card['value'] }}</p>
                    <button type="button" wire:click="$set('status', '{{ str($card['label'])->snake() }}')" class="mt-1 text-xs font-bold text-blue-600">View details →</button>
                </div>
            @endforeach
        </div>

        <div class="mb-4 flex flex-wrap items-center gap-2 border-b border-slate-200">
            @foreach (['all' => 'All Inventory', 'in_stock' => 'In Stock', 'low_stock' => 'Low Stock', 'out_of_stock' => 'Out of Stock', 'backorder' => 'Backorder', 'not_tracked' => 'Not Tracked'] as $key => $label)
                <button type="button" wire:click="$set('status', '{{ $key }}')" class="border-b-2 px-3 pb-3 text-sm font-semibold {{ $status === $key ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-400 hover:text-slate-700' }}">{{ $label }}</button>
            @endforeach
        </div>

        <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_280px]">
            <div>
                <div class="mb-4 flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-3 shadow-sm md:flex-row md:items-center">
                    <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search product or SKU..." class="min-w-0 flex-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-blue-500">
                    <select wire:model.live="perPage" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm"><option value="20">20</option><option value="50">50</option></select>
                    <button type="button" wire:click="resetFilters" class="text-sm font-semibold text-slate-500 hover:text-slate-900">Reset</button>
                </div>

                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="bg-slate-50 text-[11px] font-semibold uppercase tracking-wider text-slate-400"><tr><th class="px-5 py-3">Product / Variant</th><th class="px-4 py-3">SKU</th><th class="px-4 py-3">On Hand</th><th class="px-4 py-3">Reserved</th><th class="px-4 py-3">Available</th><th class="px-4 py-3">Status</th><th class="px-5 py-3">Actions</th></tr></thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($rows as $row)
                                    @php($available = $row->availableQuantity())
                                    @php($rowStatus = $available <= 0 ? 'Out of Stock' : ($row->isLowStock() ? 'Low Stock' : 'In Stock'))
                                    <tr wire:key="inventory-item-{{ $row->id }}" class="text-slate-700 hover:bg-slate-50">
                                        <td class="px-5 py-4"><p class="font-bold text-slate-950">{{ $row->variant?->product?->name ?? '—' }}</p><p class="text-xs text-slate-400">{{ $row->variant?->name ?: 'Default variant' }}</p></td>
                                        <td class="px-4 py-4 text-xs font-medium text-slate-500">{{ $row->variant?->sku ?? '—' }}</td>
                                        <td class="px-4 py-4 font-semibold text-slate-950">{{ $row->quantity_on_hand }}</td>
                                        <td class="px-4 py-4 text-slate-500">{{ $row->quantity_reserved }}</td>
                                        <td class="px-4 py-4 font-semibold text-slate-950">{{ $available }}</td>
                                        <td class="px-4 py-4"><span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $rowStatus === 'In Stock' ? 'bg-emerald-100 text-emerald-700' : ($rowStatus === 'Low Stock' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">{{ $rowStatus }}</span></td>
                                        <td class="px-5 py-4"><div class="flex items-center gap-2"><button type="button" wire:click="openAdjust({{ $row->id }})" wire:loading.attr="disabled" wire:target="openAdjust({{ $row->id }})" class="text-xs font-bold text-blue-600 disabled:opacity-50">Adjust</button><a href="{{ url('/admin/catalog/inventory/'.$row->variant_id.'/history') }}" class="text-xs font-semibold text-slate-500">History</a></div></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="px-5 py-12 text-center text-sm text-slate-500">No inventory records found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-slate-100 px-5 py-4">{{ $rows->links() }}</div>
                </div>
            </div>

            <div class="space-y-5">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><h3 class="text-sm font-bold text-slate-950">Inventory Alerts</h3><div class="mt-4 space-y-3">@foreach ($alerts as $label => $count)<div wire:key="inventory-alert-{{ $label }}" class="flex items-center justify-between text-sm"><span class="text-slate-600">{{ $label }}</span><span class="font-bold text-slate-950">{{ $count }}</span></div>@endforeach</div></div>
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><h3 class="text-sm font-bold text-slate-950">Recent Stock Movements</h3><div class="mt-4 space-y-3">@forelse ($recentMovements as $movement)<div wire:key="recent-movement-{{ $movement->id }}" class="flex items-center justify-between gap-3"><div><p class="text-xs font-bold text-slate-950">{{ ucfirst($movement->type?->value ?? $movement->type) }}</p><p class="text-[10px] text-slate-400">{{ $movement->variant?->product?->name ?? 'Product' }}</p></div><span class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold {{ $movement->quantity_delta >= 0 ? 'text-emerald-700' : 'text-red-700' }}">{{ $movement->quantity_delta > 0 ? '+' : '' }}{{ $movement->quantity_delta }}</span></div>@empty<p class="text-xs text-slate-500">No stock movements yet.</p>@endforelse</div></div>
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-xs text-slate-500">Tracked units</p><p class="mt-1 text-lg font-extrabold text-slate-950">{{ number_format($stats['total_units']) }}</p><p class="mt-1 text-xs text-slate-500">{{ $stats['not_tracked'] }} item(s) are not tracking quantity.</p></div>
            </div>
        </div>
    </div>

    @if ($adjustingItemId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4" role="dialog" aria-modal="true">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <div class="flex items-start justify-between gap-4"><div><h2 class="text-lg font-bold text-slate-950">Adjust stock</h2><p class="mt-1 text-sm text-slate-500">Add or remove units from this inventory item.</p></div><button type="button" wire:click="closeAdjust" class="text-2xl leading-none text-slate-400">×</button></div>
                <div class="mt-5 space-y-4"><label class="block text-sm font-semibold text-slate-700">Adjustment<input type="number" wire:model="adjustment" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5"></label><label class="block text-sm font-semibold text-slate-700">Movement type<select wire:model="movementType" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5"><option value="restock">Restock</option><option value="adjustment">Adjustment</option><option value="damage">Damage</option><option value="return">Return</option><option value="correction">Correction</option></select></label><label class="block text-sm font-semibold text-slate-700">Note<textarea wire:model="note" rows="3" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5"></textarea></label></div>
                <div class="mt-6 flex justify-end gap-2"><button type="button" wire:click="closeAdjust" class="rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600">Cancel</button><button type="button" wire:click="adjust" wire:loading.attr="disabled" wire:target="adjust" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-bold text-white disabled:opacity-60"><span wire:loading.remove wire:target="adjust">Save adjustment</span><span wire:loading wire:target="adjust">Saving...</span></button></div>
            </div>
        </div>
    @endif
</div>
