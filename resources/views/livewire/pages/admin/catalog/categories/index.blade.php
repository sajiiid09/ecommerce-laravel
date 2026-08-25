<div class="p-5 sm:p-8">
    <div class="mx-auto max-w-[1480px]">
        <div class="mb-6">
            <p class="text-sm text-[#64748b]">StoreZ / Catalog / Categories</p>
            <h1 class="mt-1 text-[28px] font-extrabold tracking-tight text-[#111827]">Categories</h1>
            <p class="mt-1 text-sm text-[#64748b]">Organize products into storefront categories and subcategories.</p>
        </div>

        <div class="mb-5 flex flex-wrap items-center gap-2">
            <button wire:click="openCreate" class="rounded-lg bg-[#2563eb] px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#1d4ed8]">+ Add Category</button>
            <a href="{{ url('/admin/catalog/categories/import') }}" class="flex items-center gap-2 rounded-lg border border-[#e5e7eb] bg-white px-4 py-2.5 text-sm font-semibold text-[#374151] hover:bg-[#f9fafb]">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/></svg>
                Import
            </a>
            <a href="{{ url('/admin/catalog/categories/export') }}" class="flex items-center gap-2 rounded-lg border border-[#e5e7eb] bg-white px-4 py-2.5 text-sm font-semibold text-[#374151] hover:bg-[#f9fafb]">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Export
            </a>
        </div>

        <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach([
                ['Total Categories', $stats['total'], '#2563eb', 'M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z'],
                ['Active Categories', $stats['active'], '#10b981', 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                ['Top-Level Categories', $stats['top_level'], '#8b5cf6', 'M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25a2.25 2.25 0 0 1-2.25 2.25h-2.25a2.25 2.25 0 0 1-2.25-2.25V6Z'],
                ['Products Assigned', $stats['assigned'], '#f97316', 'm21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9'],
            ] as [$label, $value, $color, $path])
                <div wire:key="category-stat-{{ $label }}" class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold text-[#64748b]">{{ $label }}</p>
                            <p class="mt-2 text-[26px] font-extrabold text-[#111827]">{{ number_format($value) }}</p>
                            <button wire:click="$set('status', '{{ $label === 'Active Categories' ? 'active' : '' }}')" class="mt-1 text-xs font-bold text-[#2563eb]">View all →</button>
                        </div>
                        <span style="background: {{ $color }}15; color: {{ $color }}" class="grid size-11 place-items-center rounded-full"><svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/></svg></span>
                    </div>
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
                    <div class="flex min-w-0 flex-1 items-center gap-2 rounded-lg border border-[#e5e7eb] bg-[#f9fafb] px-3 py-2">
                        <svg class="size-4 shrink-0 text-[#9ca3af]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search categories..." class="min-w-0 flex-1 bg-transparent text-sm outline-none placeholder:text-[#9ca3af]">
                    </div>
                    <select wire:model.live="parentFilter" class="h-10 rounded-lg border border-[#e5e7eb] bg-white px-3 text-sm text-[#374151]"><option value="">All parents</option>@foreach($parents as $parent)<option wire:key="category-parent-filter-{{ $parent->id }}" value="{{ $parent->id }}">{{ $parent->name }}</option>@endforeach</select>
                    <button wire:click="resetFilters" class="rounded-lg px-3 py-2 text-sm font-semibold text-[#64748b] hover:bg-[#f9fafb]">Reset</button>
                </div>

                @if($errors->has('delete'))<div class="mb-4 rounded-lg border border-[#fecaca] bg-[#fef2f2] px-4 py-3 text-sm text-[#b91c1c]">{{ $errors->first('delete') }}</div>@endif

                <div class="overflow-hidden rounded-xl border border-[#e5e7eb] bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="bg-[#f9fafb] text-[11px] font-semibold uppercase tracking-wider text-[#9ca3af]"><tr><th class="px-5 py-3">Category</th><th class="px-4 py-3">Parent</th><th class="px-4 py-3">Products</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Sort Order</th><th class="px-4 py-3">Updated</th><th class="px-5 py-3">Actions</th></tr></thead>
                            <tbody class="divide-y divide-[#f3f4f6]">
                                @forelse($rows as $row)
                                    <tr wire:key="category-row-{{ $row->id }}" class="text-[#374151] hover:bg-[#f9fafb]">
                                        <td class="px-5 py-4"><div class="flex items-center gap-3" style="padding-left: {{ min($depths[$row->id] ?? 0, 4) * 20 }}px"><div class="grid size-8 place-items-center rounded-lg bg-[#fef3c7]"><svg class="size-4 text-[#d97706]" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"/></svg></div><span class="font-bold text-[#111827]">{{ $row->name }}</span></div></td>
                                        <td class="px-4 py-4 text-[#64748b]">{{ $row->parent?->name ?? '—' }}</td>
                                        <td class="px-4 py-4 font-semibold text-[#374151]">{{ number_format($row->products_count) }}</td>
                                        <td class="px-4 py-4"><button wire:click="toggleStatus({{ $row->id }})" class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $row->is_active ? 'bg-[#dcfce7] text-[#16a34a]' : 'bg-[#f3f4f6] text-[#6b7280]' }}">{{ $row->is_active ? 'Active' : 'Inactive' }}</button></td>
                                        <td class="px-4 py-4 text-[#64748b]">{{ $row->sort_order }}</td>
                                        <td class="px-4 py-4 text-xs text-[#94a3b8]">{{ $row->updated_at?->format('M d, Y') }}<br>{{ $row->updated_at?->format('h:i A') }}</td>
                                        <td class="px-5 py-4"><div class="flex items-center gap-1"><button wire:click="openEdit({{ $row->id }})" aria-label="Edit {{ $row->name }}" class="grid size-8 place-items-center rounded-lg text-[#64748b] hover:bg-[#f3f4f6] hover:text-[#2563eb]">✎</button><a href="{{ url('/category/'.$row->slug) }}" target="_blank" aria-label="View {{ $row->name }}" class="grid size-8 place-items-center rounded-lg text-[#64748b] hover:bg-[#f3f4f6] hover:text-[#2563eb]">◉</a><button wire:click="deleteCategory({{ $row->id }})" onclick="return confirm('Delete this category?')" aria-label="Delete {{ $row->name }}" class="grid size-8 place-items-center rounded-lg text-[#64748b] hover:bg-[#fef2f2] hover:text-[#ef4444]">⌫</button></div></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="px-5 py-12 text-center text-sm text-[#9ca3af]">No categories found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="flex items-center justify-between border-t border-[#f3f4f6] px-5 py-4"><p class="text-xs text-[#9ca3af]">Showing {{ $rows->firstItem() ?? 0 }} to {{ $rows->lastItem() ?? 0 }} of {{ $rows->total() }} results</p>{{ $rows->links() }}</div>
                </div>
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
            <div><div class="flex items-center justify-between"><label class="text-xs font-bold text-[#374151]">Category Image</label><button type="button" wire:click="$toggle('showMediaPicker')" class="text-xs font-bold text-[#2563eb]">{{ $showMediaPicker ? 'Hide Library' : 'Select from Media Library' }}</button></div>@if($selectedMedia)<div class="mt-2 flex items-center gap-3 rounded-lg border border-[#e5e7eb] p-2"><img src="{{ $selectedMedia->url() }}" class="size-14 rounded object-cover"><span class="text-xs text-[#64748b]">{{ $selectedMedia->filename }}</span></div>@endif @error('media_asset_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror @if($showMediaPicker)<div class="mt-3 grid max-h-40 grid-cols-6 gap-2 overflow-y-auto rounded-lg border border-[#e5e7eb] p-2">@forelse($mediaAssets as $asset)<button type="button" wire:click="selectMedia({{ $asset->id }})" class="rounded border border-transparent p-1 hover:border-[#2563eb]"><img src="{{ $asset->url() }}" class="aspect-square w-full rounded object-cover" alt="{{ $asset->filename }}"></button>@empty<p class="col-span-6 p-3 text-xs text-[#64748b]">No media assets available. Upload an image in Media Library first.</p>@endforelse</div>@endif</div>
            <div class="grid gap-4 sm:grid-cols-2"><label class="flex items-center gap-2 text-sm text-[#374151]"><input type="checkbox" wire:model="is_active" class="rounded border-[#cbd5e1] text-[#2563eb]"> Active category</label><div><label class="text-xs font-bold text-[#374151]">Sort Order</label><input type="number" min="0" wire:model="sort_order" class="mt-1 w-full rounded-lg border border-[#dbe2ea] px-3 py-2.5 text-sm"></div></div>
            <div class="flex justify-end gap-2 border-t border-[#eef2f7] pt-4"><button type="button" x-on:click="$data.close()" class="rounded-lg border border-[#e5e7eb] px-4 py-2.5 text-sm font-semibold text-[#374151]">Cancel</button><button type="submit" class="rounded-lg bg-[#2563eb] px-4 py-2.5 text-sm font-bold text-white">{{ $editingId ? 'Save Changes' : 'Save Category' }}</button></div>
        </form>
    </x-ui.modal>
</div>
