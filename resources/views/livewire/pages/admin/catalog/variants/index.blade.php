<div class="p-5 sm:p-8">
    <div class="mx-auto max-w-[1480px]">
        <div class="mb-6">
            <p class="text-sm text-[#6b7280]">StoreZ / Catalog / Variants</p>
            <h1 class="mt-1 text-[28px] font-extrabold tracking-tight text-[#111827]">Variants</h1>
            <p class="mt-1 text-sm text-[#6b7280]">Manage sellable product combinations, SKU pricing and status across the catalog.</p>
        </div>

        <div class="mb-5 flex flex-wrap items-center gap-2">
            <button type="button" wire:click="toggleFilters" class="flex items-center gap-2 rounded-lg border border-[#e5e7eb] bg-white px-4 py-2.5 text-sm font-semibold text-[#374151] hover:bg-[#f9fafb]">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75"/></svg>
                {{ $filtersOpen ? 'Hide Filters' : 'Filters' }}
            </button>
        </div>

        {{-- Variant summary cards are intentionally hidden while the table is the primary view. --}}
        {{--
        <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach([
                ['Total Variants', number_format($rows->total()), '#2563eb', 'M21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9'],
                ['Active Variants', $rows->where('is_active', true)->count(), '#10b981', 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                ['Out of Stock', '0', '#ef4444', 'M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636'],
                ['SKU Conflicts', '0', '#f97316', 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126Z'],
            ] as [$label, $value, $color, $path])
                <div class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold text-[#6b7280]">{{ $label }}</p>
                            <p class="mt-2 text-[26px] font-extrabold text-[#111827]">{{ $value }}</p>
                            <a href="{{ url('/admin/catalog/products') }}" class="mt-1 inline-block text-xs font-bold text-[#2563eb]">View products →</a>
                        </div>
                        <span style="background: {{ $color }}15; color: {{ $color }}" class="grid size-11 place-items-center rounded-full">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/></svg>
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
        --}}

        @if($filtersOpen)
            <div class="mb-4 flex items-end gap-3 rounded-xl border border-[#e5e7eb] bg-white p-4 shadow-sm">
                <label class="text-xs font-semibold text-[#374151]">Status<select wire:model.live="status" class="mt-1 block h-10 rounded-lg border border-[#e5e7eb] bg-white px-3 text-sm"><option value="all">All variants</option><option value="active">Active</option><option value="inactive">Inactive</option><option value="low_stock">Low stock</option><option value="out_of_stock">Out of stock</option></select></label>
                <button type="button" wire:click="resetFilters" class="h-10 rounded-lg border border-[#e5e7eb] px-3 text-sm font-semibold text-[#374151]">Reset filters</button>
            </div>
        @endif

        <div class="mb-4 flex flex-wrap items-center gap-2 border-b border-[#e5e7eb]">
            @foreach(['all' => 'All', 'active' => 'Active', 'inactive' => 'Inactive', 'low_stock' => 'Low Stock', 'out_of_stock' => 'Out of Stock'] as $value => $tab)
                <button type="button" wire:click="$set('status', '{{ $value }}')" class="border-b-2 {{ $status === $value ? 'border-[#2563eb] text-[#2563eb]' : 'border-transparent text-[#9ca3af] hover:text-[#374151]' }} px-3 pb-3 text-sm font-semibold">{{ $tab }}</button>
            @endforeach
        </div>

        <div class="w-full">
            <div class="w-full">
                <div class="mb-4 flex flex-col gap-3 rounded-xl border border-[#e5e7eb] bg-white p-3 shadow-sm md:flex-row md:items-center">
                    <x-ui.input wire:model.live.debounce.300ms="searchQuery" type="search" placeholder="Search product or SKU..." leftIcon="magnifying-glass" class="min-w-0 flex-1" />
                    <select wire:model.live="perPage" aria-label="Rows per page" class="h-10 w-24 rounded-lg border border-[#e5e7eb] bg-white px-3 text-sm"><option value="20">20</option><option value="50">50</option></select>
                </div>

                <x-ui.table :paginator="$rows" wire:loading loadOn="pagination, search, sorting" table:class="w-full min-w-[1200px] table-fixed text-left" class="w-full overflow-hidden rounded-xl border border-[#e5e7eb] bg-white shadow-sm">
                    <colgroup>
                        <col class="w-[4%]">
                        <col class="w-[24%]">
                        <col class="w-[19%]">
                        <col class="w-[15%]">
                        <col class="w-[12%]">
                        <col class="w-[7%]">
                        <col class="w-[10%]">
                        <col class="w-[9%]">
                    </colgroup>
                            <x-ui.table.header class="bg-[#f9fafb] text-[11px] font-semibold uppercase tracking-wider text-[#9ca3af]"><x-ui.table.columns withCheckAll>
                                <x-ui.table.head column="created_at" sortable :currentSortBy="$sortBy" :currentSortDir="$sortDir">Variant</x-ui.table.head><x-ui.table.head>Parent Product</x-ui.table.head><x-ui.table.head>Options</x-ui.table.head><x-ui.table.head>Price</x-ui.table.head><x-ui.table.head>Stock</x-ui.table.head><x-ui.table.head column="is_active" sortable :currentSortBy="$sortBy" :currentSortDir="$sortDir">Status</x-ui.table.head><x-ui.table.head>Actions</x-ui.table.head>
                            </x-ui.table.columns></x-ui.table.header>
                            <x-ui.table.rows class="divide-y divide-[#f3f4f6]">
                                @forelse($rows as $row)
                                    <x-ui.table.row :checkboxId="$row->id" :key="$row->id" class="text-[#374151] hover:bg-[#f9fafb]">
                                        <td class="min-w-0 max-w-0 px-4 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="grid size-10 shrink-0 place-items-center rounded-lg bg-[#f3f4f6]">
                                                    <svg class="size-5 text-[#9ca3af]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <span class="block truncate text-sm font-semibold text-[#111827]" title="{{ $row->product?->name ?? '—' }}">{{ $row->product?->name ?? '—' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="min-w-0 max-w-0 px-4 py-4 text-sm text-[#6b7280]"><span class="block truncate" title="{{ $row->product?->name ?? '—' }}">{{ $row->product?->name ?? '—' }}</span></td>
                                        <td class="px-4 py-4 text-sm text-[#374151]">{{ $row->optionValues->pluck('value')->join(' / ') ?: '—' }}</td>
                                        <td class="px-4 py-4 font-semibold text-[#111827]">৳{{ number_format(($row->currentPriceMinor() ?? 0)/100,2) }}</td>
                                        <td class="px-4 py-4"><span class="font-semibold {{ $row->availableQuantity() < 5 ? 'text-[#ef4444]' : 'text-[#374151]' }}">{{ $row->availableQuantity() }}</span></td>
                                        <td class="px-4 py-4">
                                            @php $isActive = $row->is_active ?? true; @endphp
                                            <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $isActive ? 'bg-[#dcfce7] text-[#16a34a]' : 'bg-[#f3f4f6] text-[#6b7280]' }}">{{ $isActive ? 'Active' : 'Inactive' }}</span>
                                        </td>
                                        <td class="px-5 py-4">
                                            <x-ui.dropdown position="bottom-end">
                                                <x-slot:button>
                                                    <x-ui.button
                                                        type="button"
                                                        size="sm"
                                                        variant="outline"
                                                        color="slate"
                                                        icon-after="chevron-down"
                                                        aria-label="Actions for {{ $row->product?->name ?? 'variant' }}"
                                                    >Action</x-ui.button>
                                                </x-slot:button>
                                                <x-slot:menu>
                                                    <x-ui.dropdown.item wire:click="showVariant({{ $row->id }})" icon="eye">
                                                        View
                                                    </x-ui.dropdown.item>
                                                    <x-ui.dropdown.item
                                                        as="a"
                                                        href="{{ route('admin.catalog.products.variants', ['product' => $row->product_id]) }}"
                                                    >
                                                        Edit
                                                    </x-ui.dropdown.item>
                                                    <x-ui.dropdown.separator />
                                                    <x-ui.dropdown.item
                                                        wire:click="delete({{ $row->id }})"
                                                        wire:confirm="Delete this variant?"
                                                        variant="danger"
                                                    >
                                                        Delete
                                                    </x-ui.dropdown.item>
                                                </x-slot:menu>
                                            </x-ui.dropdown>
                                        </td>
                                    </x-ui.table.row>
                                @empty
                                    <x-ui.table.empty>{{ filled($searchQuery) || $status !== 'all' ? 'No variants match the selected search or filter.' : 'No variants found.' }}</x-ui.table.empty>
                                @endforelse
                            </x-ui.table.rows>
                </x-ui.table>
            </div>

            {{-- Variant summary cards are intentionally hidden; the table now uses the full content width. --}}
            {{--
            <div class="space-y-5">
                <div class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-[#111827]">Variant Summary</h3>
                    <p class="mt-1 text-xs text-[#9ca3af]">Variants by Product (Top 5)</p>
                    <div class="mt-4 space-y-3">
                        @foreach(['Wireless Headphones'=>9,'Redmi Note 13'=>6,'Orix Detergent'=>4,'Anker PowerCore'=>3,'Nivea Soft Cream'=>3] as $name=>$count)
                            <div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-[#374151]">{{ $name }}</span>
                                    <span class="font-bold text-[#111827]">{{ $count }}</span>
                                </div>
                                <div class="mt-1 h-1.5 rounded-full bg-[#f3f4f6]">
                                    <div class="h-1.5 rounded-full bg-[#2563eb]" style="width: {{ $count * 10 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold text-[#111827]">Low Stock Variants</h3>
                        <a href="{{ url('/admin/catalog/products') }}" class="text-xs font-bold text-[#2563eb]">View products</a>
                    </div>
                    <div class="mt-4 space-y-3">
                        @foreach(['Beige / Small'=>5,'128 GB / Blue'=>14,'256 GB / Green'=>7] as $variant=>$count)
                            <div class="flex items-center gap-3">
                                <div class="size-8 shrink-0 rounded bg-[#f3f4f6]"></div>
                                <div class="flex-1">
                                    <p class="text-xs font-bold text-[#111827]">{{ $variant }}</p>
                                </div>
                                <span class="rounded-full bg-[#fef3c7] px-2 py-0.5 text-[10px] font-bold text-[#d97706]">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            --}}
        </div>

        <x-ui.modal
            id="variant-details"
            width="2xl"
            heading="Variant details"
            description="Product, pricing, inventory, and option details."
        >
            @if($viewingVariant)
                <div class="space-y-6">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-lg font-bold text-slate-900">{{ $viewingVariant->product?->name ?? 'Unknown product' }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $viewingVariant->name ?: 'Default variant' }}</p>
                        </div>
                        <x-ui.badge variant="solid" :color="$viewingVariant->is_active ? 'emerald' : 'slate'" pill>
                            {{ $viewingVariant->is_active ? 'Active' : 'Inactive' }}
                        </x-ui.badge>
                    </div>

                    <div class="grid gap-4 rounded-xl bg-slate-50 p-4 sm:grid-cols-2">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">SKU</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $viewingVariant->sku }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Options</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $viewingVariant->optionValues->pluck('value')->join(' / ') ?: 'No options' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Price</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ html_entity_decode('&#2547;') }}{{ number_format($viewingVariant->currentPriceMinor() / 100, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Available stock</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ number_format($viewingVariant->availableQuantity()) }}</p>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Regular price</p>
                            <p class="mt-1 text-sm text-slate-700">{{ html_entity_decode('&#2547;') }}{{ number_format(($viewingVariant->regular_price_minor ?? 0) / 100, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Sale price</p>
                            <p class="mt-1 text-sm text-slate-700">{{ $viewingVariant->sale_price_minor !== null ? html_entity_decode('&#2547;').number_format($viewingVariant->sale_price_minor / 100, 2) : '—' }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </x-ui.modal>
    </div>
</div>
