<div class="p-5 sm:p-8">
    <div class="mx-auto max-w-[1480px]">
        <div class="mb-6">
            <p class="text-sm text-[#6b7280]">StoreZ / Catalog / Products</p>
            <h1 class="mt-1 text-[28px] font-extrabold tracking-tight text-[#111827]">Product Management</h1>
            <p class="mt-1 text-sm text-[#6b7280]">Manage your catalog, pricing, and product visibility.</p>
        </div>

        <div class="mb-5 flex flex-wrap items-center gap-2">
            <a href="{{ url('/admin/catalog/products/create') }}" class="rounded-lg bg-[#2563eb] px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#1d4ed8]">+ Add New Product</a>
            @if(config('features.catalog_import_export'))
            <a href="{{ url('/admin/catalog/products/import') }}" class="flex items-center gap-2 rounded-lg border border-[#e5e7eb] bg-white px-4 py-2.5 text-sm font-semibold text-[#374151] hover:bg-[#f9fafb]">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/></svg>
                Import
            </a>
            <a href="{{ url('/admin/catalog/products/export') }}" class="flex items-center gap-2 rounded-lg border border-[#e5e7eb] bg-white px-4 py-2.5 text-sm font-semibold text-[#374151] hover:bg-[#f9fafb]">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Export
            </a>
            @endif
            <button type="button" wire:click="toggleFilters" class="flex items-center gap-2 rounded-lg border border-[#e5e7eb] bg-white px-4 py-2.5 text-sm font-semibold text-[#374151] hover:bg-[#f9fafb]">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75"/></svg>
                {{ $filtersOpen ? 'Hide Filters' : 'Filters' }}
            </button>
        </div>

        <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach([
                ['Total Products', number_format($stats['total']), '#2563eb', 'm21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9'],
                ['Active Products', number_format($stats['active']), '#10b981', 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                ['Low Stock Products', number_format($stats['low_stock']), '#f97316', 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126Z'],
                ['Draft Products', App\Models\Product::where('status','draft')->count(), '#8b5cf6', 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z'],
            ] as [$label, $value, $color, $path])
                <div class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold text-[#6b7280]">{{ $label }}</p>
                            <p class="mt-2 text-[26px] font-extrabold text-[#111827]">{{ $value }}</p>
                        </div>
                        <span style="background: {{ $color }}15; color: {{ $color }}" class="grid size-11 place-items-center rounded-full">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/></svg>
                        </span>
                    </div>
                    <div class="mt-4 flex items-center gap-2 text-xs">
                        <span class="font-bold text-[#10b981]">+0.0% &rarr;</span>
                        <span class="text-[#9ca3af]">vs last 7 days</span>
                        <span class="ml-auto">
                            <svg class="h-6 w-16" viewBox="0 0 64 24" fill="none"><polyline points="0,20 8,16 16,18 24,10 32,12 40,6 48,8 56,2 64,4" stroke="{{ $color }}" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mb-4 flex flex-wrap items-center gap-2 border-b border-[#e5e7eb]">
            @foreach([''=>'All Products','published'=>'Published','draft'=>'Draft','archived'=>'Archived'] as $value=>$label)
                <button wire:click="$set('status', '{{ $value }}')" class="border-b-2 px-3 pb-3 text-sm font-semibold {{ $status === $value ? 'border-[#2563eb] text-[#2563eb]' : 'border-transparent text-[#9ca3af] hover:text-[#374151]' }}">{{ $label }}</button>
            @endforeach
        </div>

        @if($filtersOpen)
            <div class="mb-4 flex flex-wrap items-end gap-3 rounded-xl border border-[#e5e7eb] bg-white p-4 shadow-sm">
                <label class="text-xs font-semibold text-[#374151]">Category<select wire:model.live="categoryFilter" class="mt-1 block h-10 rounded-lg border border-[#e5e7eb] bg-white px-3 text-sm"><option value="">All categories</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></label>
                <label class="text-xs font-semibold text-[#374151]">Brand<select wire:model.live="brandFilter" class="mt-1 block h-10 rounded-lg border border-[#e5e7eb] bg-white px-3 text-sm"><option value="">All brands</option>@foreach($brands as $brand)<option value="{{ $brand->id }}">{{ $brand->name }}</option>@endforeach</select></label>
                <button type="button" wire:click="resetFilters" class="h-10 rounded-lg border border-[#e5e7eb] px-3 text-sm font-semibold text-[#374151]">Reset filters</button>
            </div>
        @endif

        <x-ui.table.container border class="mb-4 bg-white shadow-sm">
                <div class="mb-4 flex flex-col gap-3 rounded-xl border border-[#e5e7eb] bg-white p-3 shadow-sm md:flex-row md:items-center">
                    <x-ui.input wire:model.live.debounce.300ms="searchQuery" type="search" placeholder="Search products by name, SKU..." leftIcon="magnifying-glass" class="min-w-0 flex-1" />
                    <select wire:model.live="perPage" aria-label="Rows per page" class="h-10 w-24 rounded-lg border border-[#e5e7eb] bg-white px-3 text-sm"><option value="15">15</option><option value="30">30</option><option value="50">50</option></select>
                </div>

                @if(count($selectedIds))
                    <div class="mb-4 flex flex-wrap items-center gap-2 rounded-xl border border-[#bfdbfe] bg-[#eff6ff] p-3 text-sm text-[#2563eb]">
                        <span class="mr-2 font-semibold">{{ count($selectedIds) }} selected</span>
                        @foreach(['publish'=>'Publish','draft'=>'Draft','archive'=>'Archive','delete'=>'Delete'] as $action=>$label)
                            <button wire:click="bulk('{{ $action }}')" class="rounded-md border border-[#bfdbfe] bg-white px-3 py-1.5 font-semibold hover:bg-[#dbeafe]">{{ $label }}</button>
                        @endforeach
                    </div>
                @endif

                <x-ui.table :paginator="$rows" pagination:variant="full" wire:loading loadOn="pagination, search, sorting" class="overflow-hidden rounded-xl border border-[#e5e7eb] bg-white shadow-sm" table:class="table-fixed">
                            <x-ui.table.header class="bg-[#f9fafb] text-[10px] font-semibold tracking-wide text-[#9ca3af] sm:text-[11px]"><x-ui.table.columns withCheckAll>
                                <x-ui.table.head column="name" sortable :currentSortBy="$sortBy" :currentSortDir="$sortDir" class="w-[42%] px-2 text-[10px] sm:px-3 sm:text-xs md:w-[28%]">Product</x-ui.table.head><x-ui.table.head class="hidden w-[10%] px-2 text-[10px] sm:text-xs md:table-cell">SKU</x-ui.table.head><x-ui.table.head class="hidden w-[11%] px-2 text-[10px] sm:text-xs md:table-cell">Category</x-ui.table.head><x-ui.table.head class="hidden w-[10%] px-2 text-[10px] sm:text-xs md:table-cell">Brand</x-ui.table.head><x-ui.table.head class="w-[16%] px-2 text-[10px] sm:text-xs md:w-[12%]">Price</x-ui.table.head><x-ui.table.head class="w-[11%] px-2 text-[10px] sm:text-xs md:w-[8%]">Stock</x-ui.table.head><x-ui.table.head column="status" sortable :currentSortBy="$sortBy" :currentSortDir="$sortDir" class="w-[15%] px-2 text-[10px] sm:text-xs md:w-[10%]">Status</x-ui.table.head><x-ui.table.head class="w-[12%] px-2 text-[10px] sm:text-xs md:w-[8%]">Actions</x-ui.table.head>
                            </x-ui.table.columns></x-ui.table.header>
                            <x-ui.table.rows class="divide-y divide-[#f3f4f6]">
                                @forelse($rows as $row)
                                    <x-ui.table.row :checkboxId="$row->id" :key="$row->id" class="text-[#374151] hover:bg-[#f9fafb]">
                                        <x-ui.table.cell class="w-[42%] px-2 py-2 text-xs sm:px-3 sm:py-3 md:w-[28%]">
                                            <div class="flex min-w-0 items-center gap-2 sm:gap-3">
                                                <div class="grid size-8 shrink-0 rounded-lg bg-[#f3f4f6] sm:size-9 md:size-10">
                                                    <svg class="size-5 text-[#9ca3af]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="truncate text-[11px] font-bold text-[#111827] sm:text-xs">{{ $row->name }}</p>
                                                    <p class="mt-0.5 truncate text-[10px] text-[#9ca3af] sm:text-[11px]">{{ $row->brand?->name ?? html_entity_decode('&mdash;') }}</p>
                                                </div>
                                            </div>
                                        </x-ui.table.cell>
                                        <x-ui.table.cell class="hidden w-[10%] px-2 py-3 text-[11px] font-medium text-[#6b7280] md:table-cell">{{ $row->defaultVariant?->sku ?? html_entity_decode('&mdash;') }}</x-ui.table.cell>
                                        <x-ui.table.cell class="hidden w-[11%] px-2 py-3 text-xs text-[#374151] md:table-cell">{{ $row->primaryCategory?->name ?? html_entity_decode('&mdash;') }}</x-ui.table.cell>
                                        <x-ui.table.cell class="hidden w-[10%] px-2 py-3 text-xs text-[#374151] md:table-cell">{{ $row->brand?->name ?? html_entity_decode('&mdash;') }}</x-ui.table.cell>
                                        <x-ui.table.cell class="w-[16%] px-2 py-3 text-[11px] font-semibold text-[#111827] sm:text-xs md:w-[12%]">&#2547;{{ number_format(($row->defaultVariant?->currentPriceMinor() ?? 0)/100,2) }}</x-ui.table.cell>
                                        <x-ui.table.cell class="w-[11%] px-2 py-3 text-[11px] sm:text-xs md:w-[8%]"><span class="font-semibold {{ ($row->defaultVariant?->availableQuantity() ?? 0) < 5 ? 'text-[#ef4444]' : 'text-[#374151]' }}">{{ $row->defaultVariant?->availableQuantity() ?? 0 }}</span></x-ui.table.cell>
                                        <x-ui.table.cell class="w-[15%] px-2 py-3 md:w-[10%]">
                                            @php $st = $row->status?->value ?? $row->status; @endphp
                                            <span class="inline-flex rounded-full px-1.5 py-0.5 text-[10px] font-bold sm:px-2 sm:text-[11px] {{ $st === 'published' ? 'bg-[#dcfce7] text-[#16a34a]' : ($st === 'draft' ? 'bg-[#fef3c7] text-[#d97706]' : 'bg-[#f3f4f6] text-[#6b7280]') }}">{{ ucfirst($st) }}</span>
                                        </x-ui.table.cell>
                                        <x-ui.table.cell class="w-[12%] px-1 py-2 md:w-[8%]">
                                            <div class="flex items-center justify-end gap-0.5 sm:gap-1">
                                                <a href="{{ url('/admin/catalog/products/'.$row->id.'/edit') }}" class="grid size-7 place-items-center rounded-lg text-[#6b7280] hover:bg-[#f3f4f6] hover:text-[#2563eb] sm:size-8"><svg class="size-3.5 sm:size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg></a>
                                                <button class="grid size-7 place-items-center rounded-lg text-[#6b7280] hover:bg-[#fef2f2] hover:text-[#ef4444] sm:size-8"><svg class="size-3.5 sm:size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg></button>
                                            </div>
                                        </x-ui.table.cell>
                                    </x-ui.table.row>
                                @empty
                                    <x-ui.table.empty>{{ filled($searchQuery) || filled($status) || $categoryFilter || $brandFilter ? 'No products match the selected search or filters.' : 'No products found.' }}</x-ui.table.empty>
                                @endforelse
                            </x-ui.table.rows>
                </x-ui.table>
        </x-ui.table.container>
    </div>
</div>
