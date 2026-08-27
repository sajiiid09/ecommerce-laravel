<div class="p-5 sm:p-8">
    <div class="mx-auto max-w-[1480px]">
        <div class="mb-6">
            <p class="text-sm text-[#64748b]">StoreZ / Catalog / Categories</p>
            <h1 class="mt-1 text-[28px] font-extrabold tracking-tight text-[#111827]">Categories</h1>
            <p class="mt-1 text-sm text-[#64748b]">Organize products into storefront categories and subcategories.</p>
        </div>

        <div class="mb-5 flex flex-wrap items-center gap-2">
            <button wire:click="openCreate" class="rounded-lg bg-[#2563eb] px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#1d4ed8]">+ Add Category</button>
        </div>

        <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach([
                ['Total Categories', $stats['total'], '#2563eb'],
                ['Active Categories', $stats['active'], '#10b981'],
                ['Top-Level Categories', $stats['top_level'], '#8b5cf6'],
                ['Products Assigned', $stats['assigned'], '#f97316'],
            ] as [$label, $value, $color])
                <div wire:key="category-stat-{{ $label }}" class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold text-[#64748b]">{{ $label }}</p>
                    <p class="mt-2 text-[26px] font-extrabold text-[#111827]">{{ number_format($value) }}</p>
                    <button wire:click="$set('status', '{{ $label === 'Active Categories' ? 'active' : '' }}')" class="mt-1 text-xs font-bold text-[#2563eb]">View all -&gt;</button>
                    <span style="background: {{ $color }}15; color: {{ $color }}" class="float-right -mt-10 grid size-11 place-items-center rounded-full">&bull;</span>
                </div>
            @endforeach
        </div>

        <div class="mb-4 flex flex-wrap items-center gap-2 border-b border-[#e5e7eb]">
            @foreach(['' => 'All Categories', 'active' => 'Active', 'inactive' => 'Inactive'] as $value => $label)
                <button wire:key="category-tab-{{ $value ?: 'all' }}" wire:click="$set('status', '{{ $value }}')" class="border-b-2 px-3 pb-3 text-sm font-semibold {{ $status === $value ? 'border-[#2563eb] text-[#2563eb]' : 'border-transparent text-[#94a3b8] hover:text-[#374151]' }}">{{ $label }}</button>
            @endforeach
        </div>

        <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_280px]">
            <div>
                <div class="mb-4 flex flex-col gap-3 rounded-xl border border-[#e5e7eb] bg-white p-3 shadow-sm md:flex-row md:items-center">
                    <x-ui.input wire:model.live.debounce.300ms="searchQuery" type="search" placeholder="Search categories..." leftIcon="magnifying-glass" class="min-w-0 flex-1" />
                    <select wire:model.live="parentFilter" aria-label="Filter by parent" class="h-10 rounded-lg border border-[#e5e7eb] bg-white px-3 text-sm text-[#374151]"><option value="">All parents</option>@foreach($parents as $parent)<option wire:key="category-parent-filter-{{ $parent->id }}" value="{{ $parent->id }}">{{ $parent->name }}</option>@endforeach</select>
                    <select wire:model.live="perPage" aria-label="Rows per page" class="h-10 w-24 rounded-lg border border-[#e5e7eb] bg-white px-3 text-sm text-[#374151]"><option value="15">15</option><option value="30">30</option><option value="50">50</option></select>
                    <button wire:click="resetFilters" class="rounded-lg px-3 py-2 text-sm font-semibold text-[#64748b] hover:bg-[#f9fafb]">Reset Filters</button>
                </div>

                @if(count($selectedIds))
                    <div class="mb-4 flex flex-wrap items-center gap-2 rounded-xl border border-[#bfdbfe] bg-[#eff6ff] p-3 text-sm text-[#2563eb]">
                        <span class="mr-2 font-semibold">{{ count($selectedIds) }} selected</span>
                        <button wire:click="bulk('activate')" class="rounded-md border border-[#bfdbfe] bg-white px-3 py-1.5 font-semibold hover:bg-[#dbeafe]">Activate</button>
                        <button wire:click="bulk('deactivate')" class="rounded-md border border-[#bfdbfe] bg-white px-3 py-1.5 font-semibold hover:bg-[#dbeafe]">Deactivate</button>
                        <button wire:click="bulk('delete')" wire:confirm="Delete the selected categories?" class="rounded-md border border-[#fecaca] bg-white px-3 py-1.5 font-semibold text-[#b91c1c] hover:bg-[#fef2f2]">Delete</button>
                    </div>
                @endif

                @if($errors->has('delete'))<div class="mb-4 rounded-lg border border-[#fecaca] bg-[#fef2f2] px-4 py-3 text-sm text-[#b91c1c]">{{ $errors->first('delete') }}</div>@endif

                <x-ui.table :paginator="$rows" wire:loading loadOn="pagination, search, sorting" class="overflow-hidden rounded-xl border border-[#e5e7eb] bg-white shadow-sm" table:class="text-left">
                    <x-ui.table.header class="bg-[#f9fafb] text-[11px] font-semibold uppercase tracking-wider text-[#9ca3af]"><x-ui.table.columns withCheckAll>
                        <x-ui.table.head column="name" sortable :currentSortBy="$sortBy" :currentSortDir="$sortDir" class="px-5 py-3">Category</x-ui.table.head><x-ui.table.head class="px-4 py-3">Parent</x-ui.table.head><x-ui.table.head class="px-4 py-3">Products</x-ui.table.head><x-ui.table.head class="px-4 py-3">Status</x-ui.table.head><x-ui.table.head column="sort_order" sortable :currentSortBy="$sortBy" :currentSortDir="$sortDir" class="px-4 py-3">Sort Order</x-ui.table.head><x-ui.table.head column="updated_at" sortable :currentSortBy="$sortBy" :currentSortDir="$sortDir" class="px-4 py-3">Updated</x-ui.table.head><x-ui.table.head class="px-5 py-3">Actions</x-ui.table.head>
                    </x-ui.table.columns></x-ui.table.header>
                    <x-ui.table.rows>
                        @forelse($rows as $row)
                            <x-ui.table.row :checkboxId="$row->id" :key="'category-'.$row->id" class="text-[#374151] hover:bg-[#f9fafb]">
                                <x-ui.table.cell class="px-5 py-4"><div class="flex items-center gap-3"><div class="grid size-8 place-items-center rounded-lg bg-[#fef3c7]"><span class="text-[#d97706]">&#9670;</span></div><span class="font-bold text-[#111827]" style="padding-left: {{ min($depths[$row->id] ?? 0, 4) * 20 }}px">{{ $row->name }}</span></div></x-ui.table.cell>
                                <x-ui.table.cell class="px-4 py-4 text-[#64748b]">{{ $row->parent?->name ?? '—' }}</x-ui.table.cell>
                                <x-ui.table.cell class="px-4 py-4 font-semibold text-[#374151]">{{ number_format($row->products_count) }}</x-ui.table.cell>
                                <x-ui.table.cell class="px-4 py-4"><button wire:click="toggleStatus({{ $row->id }})" class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $row->is_active ? 'bg-[#dcfce7] text-[#16a34a]' : 'bg-[#f3f4f6] text-[#6b7280]' }}">{{ $row->is_active ? 'Active' : 'Inactive' }}</button></x-ui.table.cell>
                                <x-ui.table.cell class="px-4 py-4 text-[#64748b]">{{ $row->sort_order }}</x-ui.table.cell>
                                <x-ui.table.cell class="px-4 py-4 text-xs text-[#94a3b8]">{{ $row->updated_at?->format('M d, Y') }}<br>{{ $row->updated_at?->format('h:i A') }}</x-ui.table.cell>
                                <x-ui.table.cell class="px-5 py-4"><div class="flex items-center gap-1"><button wire:click="openEdit({{ $row->id }})" aria-label="Edit {{ $row->name }}" class="rounded-lg px-2 py-1 text-xs text-[#64748b] hover:bg-[#f3f4f6] hover:text-[#2563eb]">Edit</button><a href="{{ url('/category/'.$row->slug) }}" target="_blank" rel="noopener" aria-label="View {{ $row->name }}" class="rounded-lg px-2 py-1 text-xs text-[#64748b] hover:bg-[#f3f4f6] hover:text-[#2563eb]">View</a><button wire:click="deleteCategory({{ $row->id }})" wire:confirm="Delete this category?" aria-label="Delete {{ $row->name }}" class="rounded-lg px-2 py-1 text-xs text-[#64748b] hover:bg-[#fef2f2] hover:text-[#ef4444]">Delete</button></div></x-ui.table.cell>
                            </x-ui.table.row>
                        @empty
                            <x-ui.table.empty>{{ filled($searchQuery) || filled($status) || $parentFilter ? 'No categories match the selected search or filters.' : 'No categories found.' }}</x-ui.table.empty>
                        @endforelse
                    </x-ui.table.rows>
                </x-ui.table>
            </div>

            <div class="space-y-5">
                <div class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm"><h3 class="text-sm font-bold text-[#111827]">Category Overview</h3><p class="mt-1 text-xs text-[#9ca3af]">Top categories by product count</p><div class="mt-4 space-y-3">@foreach($overview as $category)<div class="flex items-center justify-between text-sm"><span class="text-[#374151]">{{ $category->name }}</span><span class="font-bold text-[#111827]">{{ number_format($category->products_count) }}</span></div>@endforeach</div></div>
                <div class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm"><h3 class="text-sm font-bold text-[#111827]">Category Health</h3><div class="mt-4 space-y-3 text-sm"><div class="flex justify-between"><span>Categories without products</span><span class="rounded-full bg-[#fef3c7] px-2 py-0.5 text-[11px] font-bold text-[#d97706]">{{ $health['without_products'] }}</span></div><div class="flex justify-between"><span>Categories without image</span><span class="rounded-full bg-[#fef3c7] px-2 py-0.5 text-[11px] font-bold text-[#d97706]">{{ $health['without_image'] }}</span></div><div class="flex justify-between"><span>Inactive categories</span><span class="rounded-full bg-[#fef3c7] px-2 py-0.5 text-[11px] font-bold text-[#d97706]">{{ $health['inactive'] }}</span></div></div></div>
            </div>
        </div>
    </div>

    <x-ui.modal id="category-editor" width="xl" heading="{{ $editingId ? 'Edit Category' : 'Add Category' }}" description="Keep your storefront category hierarchy organized.">
        <form wire:submit="saveCategory" class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2"><div><label class="text-xs font-bold text-[#374151]">Category Name *</label><input wire:model="name" class="mt-1 w-full rounded-lg border border-[#dbe2ea] px-3 py-2.5 text-sm" placeholder="e.g. Electronics">@error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div><div><label class="text-xs font-bold text-[#374151]">Slug *</label><input wire:model="slug" class="mt-1 w-full rounded-lg border border-[#dbe2ea] px-3 py-2.5 text-sm" placeholder="electronics">@error('slug')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div></div>
            <div><label class="text-xs font-bold text-[#374151]">Parent Category</label><select wire:model="parent_id" class="mt-1 w-full rounded-lg border border-[#dbe2ea] bg-white px-3 py-2.5 text-sm"><option value="">No parent (top level)</option>@foreach($parents as $parent)@if($parent->id !== $editingId)<option value="{{ $parent->id }}">{{ $parent->name }}</option>@endif @endforeach</select>@error('parent_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
            <div><label class="text-xs font-bold text-[#374151]">Description</label><textarea wire:model="description" rows="3" class="mt-1 w-full rounded-lg border border-[#dbe2ea] px-3 py-2.5 text-sm" placeholder="Short category description"></textarea>@error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
            <div><div class="flex items-center justify-between"><label class="text-xs font-bold text-[#374151]">Category Image</label><button type="button" wire:click="$toggle('showMediaPicker')" class="text-xs font-bold text-[#2563eb]">{{ $showMediaPicker ? 'Hide Library' : 'Select from Media Library' }}</button></div>@if($selectedMedia)<div class="mt-2 flex items-center gap-3 rounded-lg border border-[#e5e7eb] p-2"><img src="{{ $selectedMedia->url() }}" class="size-14 rounded object-cover" alt="{{ $selectedMedia->filename }}"><span class="text-xs text-[#64748b]">{{ $selectedMedia->filename }}</span></div>@endif @error('media_asset_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror @if($showMediaPicker)<div class="mt-3 grid max-h-40 grid-cols-6 gap-2 overflow-y-auto rounded-lg border border-[#e5e7eb] p-2">@forelse($mediaAssets as $asset)<button type="button" wire:click="selectMedia({{ $asset->id }})" class="rounded border border-transparent p-1 hover:border-[#2563eb]"><img src="{{ $asset->url() }}" class="aspect-square w-full rounded object-cover" alt="{{ $asset->filename }}"></button>@empty<p class="col-span-6 p-3 text-xs text-[#64748b]">No media assets available. Upload an image in Media Library first.</p>@endforelse</div>@endif</div>
            <div class="grid gap-4 sm:grid-cols-2"><x-ui.checkbox wire:model="is_active" label="Active category" size="sm" /><div><label class="text-xs font-bold text-[#374151]">Sort Order</label><input type="number" min="0" wire:model="sort_order" class="mt-1 w-full rounded-lg border border-[#dbe2ea] px-3 py-2.5 text-sm"></div></div>
            <div class="flex justify-end gap-2 border-t border-[#eef2f7] pt-4"><button type="button" x-on:click="$data.close()" class="rounded-lg border border-[#e5e7eb] px-4 py-2.5 text-sm font-semibold text-[#374151]">Cancel</button><button type="submit" class="rounded-lg bg-[#2563eb] px-4 py-2.5 text-sm font-bold text-white">{{ $editingId ? 'Save Changes' : 'Save Category' }}</button></div>
        </form>
    </x-ui.modal>
</div>
