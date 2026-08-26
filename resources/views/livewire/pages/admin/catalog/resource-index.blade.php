<div class="p-5 sm:p-8">
    <div class="mx-auto max-w-[1480px]">
        <div class="mb-6">
            <p class="text-sm text-[#6b7280]">StoreZ / Catalog / {{ $title }}</p>
            <h1 class="mt-1 text-[28px] font-extrabold tracking-tight text-[#111827]">{{ $title }}</h1>
            <p class="mt-1 text-sm text-[#6b7280]">Manage your {{ strtolower($title) }} for the storefront.</p>
        </div>

        <div class="mb-5 flex flex-wrap items-center gap-2">
            <button wire:click="openCreate" class="rounded-lg bg-[#2563eb] px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#1d4ed8]">+ Add {{ rtrim($title, 's') }}</button>
        </div>

        <x-ui.modal id="resource-create" width="md" heading="Add {{ rtrim($title, 's') }}" description="Create a reusable catalog value.">
            <form wire:submit="createRecord" class="space-y-4">
                <label class="block text-sm font-semibold text-slate-700">
                    Name
                    <input wire:model="name" placeholder="Enter a name" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                    @error('name')
                        <p class="text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </label>
                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="$dispatch('close-modal', { id: 'resource-create' })" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600">Cancel</button>
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-bold text-white">Create</button>
                </div>
            </form>
        </x-ui.modal>

        <x-ui.modal id="resource-edit" width="lg" heading="Edit {{ rtrim($title, 's') }}" description="Update this catalog record.">
            <form wire:submit="updateRecord" class="space-y-4">
                <label class="block text-sm font-semibold text-slate-700">
                    Name
                    <input wire:model="editName" placeholder="Enter a name" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                    @error('editName')
                        <p class="text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </label>

                <label class="block text-sm font-semibold text-slate-700">
                    Slug
                    <input wire:model="editSlug" placeholder="url-friendly-slug" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                    @error('editSlug')
                        <p class="text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </label>

                @if($this->isBrandResource())
                    <label class="block text-sm font-semibold text-slate-700">
                        Description
                        <textarea wire:model="editDescription" rows="4" placeholder="Optional brand description" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></textarea>
                        @error('editDescription')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </label>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                            <input type="checkbox" wire:model="editIsActive" class="rounded border-slate-300 text-blue-600">
                            Active
                        </label>
                        <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                            <input type="checkbox" wire:model="editIsFeatured" class="rounded border-slate-300 text-blue-600">
                            Featured
                        </label>
                    </div>

                    <label class="block text-sm font-semibold text-slate-700">
                        Sort order
                        <input type="number" min="0" wire:model="editSortOrder" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                        @error('editSortOrder')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </label>
                @elseif($this->isTagResource())
                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                        <input type="checkbox" wire:model="editIsActive" class="rounded border-slate-300 text-blue-600">
                        Active
                    </label>
                @endif

                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="$dispatch('close-modal', { id: 'resource-edit' })" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600">Cancel</button>
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-bold text-white">Save changes</button>
                </div>
            </form>
        </x-ui.modal>

        <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @php
                $total = $rows->total();
                $active = $total;
            @endphp
            @php $plural = \Illuminate\Support\Str::plural($title); @endphp
            @foreach([
                ['Total '.$plural, $total, '#2563eb', 'M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z'],
                ['Active '.$plural, $active, '#10b981', 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                ['Selected', count($selectedIds), '#8b5cf6', 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                ['Last Updated', 'Now', '#f97316', 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
            ] as [$label, $value, $color, $path])
                <div class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold text-[#6b7280]">{{ $label }}</p>
                            <p class="mt-2 text-[26px] font-extrabold text-[#111827]">{{ $value }}</p>
                            <a href="#" class="mt-1 inline-block text-xs font-bold text-[#2563eb]">View all ÃƒÂ¢Ã¢â‚¬Â Ã¢â‚¬â„¢</a>
                        </div>
                        <span style="background: {{ $color }}15; color: {{ $color }}" class="grid size-11 place-items-center rounded-full">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/></svg>
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_280px]">
            <div>
                <div class="mb-4 flex flex-col gap-3 rounded-xl border border-[#e5e7eb] bg-white p-3 shadow-sm md:flex-row md:items-center">
                    <x-ui.input wire:model.live.debounce.300ms="searchQuery" type="search" placeholder="Search {{ strtolower($title) }}..." leftIcon="magnifying-glass" class="min-w-0 flex-1" controlClass="bg-[#f9fafb]" />
                    <select wire:model.live="perPage" aria-label="Rows per page" class="h-10 w-24 rounded-lg border border-[#e5e7eb] bg-white px-3 text-sm text-[#374151]">
                        <option value="15">15</option>
                        <option value="30">30</option>
                        <option value="50">50</option>
                    </select>
                </div>

                @if(count($selectedIds))
                    <div class="mb-4 flex flex-wrap items-center gap-2 rounded-xl border border-[#bfdbfe] bg-[#eff6ff] p-3 text-sm text-[#2563eb]">
                        <span class="mr-2 font-semibold">{{ count($selectedIds) }} selected</span>
                        @if(method_exists($this, 'bulk'))
                            <button wire:click="bulk('activate')" class="rounded-md border border-[#bfdbfe] bg-white px-3 py-1.5 font-semibold hover:bg-[#dbeafe]">Activate</button>
                            <button wire:click="bulk('deactivate')" class="rounded-md border border-[#bfdbfe] bg-white px-3 py-1.5 font-semibold hover:bg-[#dbeafe]">Deactivate</button>
                            <button wire:click="bulk('delete')" wire:confirm="Delete the selected records?" class="rounded-md border border-[#fecaca] bg-white px-3 py-1.5 font-semibold text-[#b91c1c] hover:bg-[#fef2f2]">Delete</button>
                        @else
                            <button wire:click="deleteSelected" wire:confirm="Delete the selected records?" class="rounded-md border border-[#bfdbfe] bg-white px-3 py-1.5 font-semibold hover:bg-[#dbeafe]">Delete</button>
                        @endif
                    </div>
                @endif

                <x-ui.table :paginator="$rows" wire:loading loadOn="pagination, search, sorting" class="overflow-hidden rounded-xl border border-[#e5e7eb] bg-white shadow-sm" table:class="text-left">
                            <x-ui.table.header class="bg-[#f9fafb] text-[11px] font-semibold uppercase tracking-wider text-[#9ca3af]"><x-ui.table.columns withCheckAll>
                                    <x-ui.table.head column="name" sortable :currentSortBy="$sortBy" :currentSortDir="$sortDir" class="px-4 py-3">{{ rtrim($title, 's') }}</x-ui.table.head>
                                    <x-ui.table.head column="slug" sortable :currentSortBy="$sortBy" :currentSortDir="$sortDir" class="px-4 py-3">Slug</x-ui.table.head>
                                    <x-ui.table.head class="px-4 py-3">Products</x-ui.table.head>
                                    <x-ui.table.head class="px-4 py-3">Status</x-ui.table.head>
                                    <x-ui.table.head column="updated_at" sortable :currentSortBy="$sortBy" :currentSortDir="$sortDir" class="px-4 py-3">Updated</x-ui.table.head>
                                    <x-ui.table.head class="px-5 py-3">Actions</x-ui.table.head>
                            </x-ui.table.columns></x-ui.table.header>
                            <x-ui.table.rows class="divide-y divide-[#f3f4f6]">
                                @forelse($rows as $row)
                                    <x-ui.table.row :checkboxId="$row->id" :key="$row->id" class="text-[#374151] hover:bg-[#f9fafb]">
                                        <td class="px-4 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="grid size-10 place-items-center rounded-lg bg-[#f3f4f6]">
                                                    <svg class="size-5 text-[#9ca3af]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/></svg>
                                                </div>
                                                <span class="font-bold text-[#111827]">{{ $row->name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-[#6b7280]">{{ $row->slug }}</td>
                                        <td class="px-4 py-4 text-sm font-medium text-[#374151]">{{ $row->products_count ?? 0 }}</td>
                                        <td class="px-4 py-4">
                                            <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ ($row->is_active ?? true) ? 'bg-[#dcfce7] text-[#16a34a]' : 'bg-[#f3f4f6] text-[#6b7280]' }}">{{ ($row->is_active ?? true) ? 'Active' : 'Inactive' }}</span>
                                        </td>
                                        <td class="px-4 py-4 text-xs text-[#9ca3af]">{{ $row->updated_at?->diffForHumans() }}</td>
                                        <td class="px-5 py-4">
                                            <div class="flex items-center gap-1">
                                                <button type="button" wire:click="openEdit({{ $row->id }})" aria-label="Edit {{ $row->name }}" class="grid size-8 place-items-center rounded-lg text-[#6b7280] hover:bg-[#f3f4f6] hover:text-[#2563eb]"><svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg></button>
                                                @php($publicUrl = $this->publicUrlFor($row))
                                                @if($publicUrl)
                                                    <a href="{{ $publicUrl }}" target="_blank" rel="noopener" aria-label="View {{ $row->name }} storefront" class="grid size-8 place-items-center rounded-lg text-[#6b7280] hover:bg-[#f3f4f6] hover:text-[#2563eb]"><svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg></a>
                                                @else
                                                    <span title="No public storefront page" aria-label="No public storefront page" class="grid size-8 place-items-center rounded-lg text-[#d1d5db]"><svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg></span>
                                                @endif
                                                <button wire:click="delete({{ $row->id }})" class="grid size-8 place-items-center rounded-lg text-[#6b7280] hover:bg-[#fef2f2] hover:text-[#ef4444]"><svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg></button>
                                            </div>
                                        </td>
                                    </x-ui.table.row>
                                @empty
                                    <x-ui.table.empty>{{ filled($searchQuery) ? 'No '.strtolower($title).' match your search.' : 'No '.strtolower($title).' found.' }}</x-ui.table.empty>
                                @endforelse
                            </x-ui.table.rows>
                </x-ui.table>
            </div>

            <div class="space-y-5">
                <div class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold text-[#111827]">Most Used {{ $title }}</h3>
                        <a href="#" class="text-xs font-bold text-[#2563eb]">View All</a>
                    </div>
                    <div class="mt-4 space-y-3">
                        @forelse($mostUsed as $i => $tag)
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="size-5 rounded-full bg-[#f3f4f6] text-center text-[10px] font-bold leading-5 text-[#6b7280]">{{ $i+1 }}</span>
                                    <span class="text-[#374151]">{{ $tag->name }}</span>
                                </div>
                                <span class="font-bold text-[#111827]">{{ $tag->products_count }}</span>
                            </div>
                        @empty
                            <p class="text-xs text-slate-500">No product usage data yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold text-[#111827]">{{ rtrim($title, 's') }} Health</h3>
                        <a href="#" class="text-xs font-bold text-[#2563eb]">View Report</a>
                    </div>
                    <div class="mt-4 space-y-3">
                        @foreach(['Unused' => $health['unused'], 'Inactive' => $health['inactive']] as $label=>$count)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-[#374151]">{{ $label }} {{ strtolower($title) }}</span>
                                <span class="rounded-full bg-[#fef3c7] px-2 py-0.5 text-[11px] font-bold text-[#d97706]">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                    <a href="#" class="mt-4 block text-center text-xs font-bold text-[#2563eb]">View Full Report ÃƒÂ¢Ã¢â‚¬Â Ã¢â‚¬â„¢</a>
                </div>
            </div>
        </div>
    </div>
</div>
