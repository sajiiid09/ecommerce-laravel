<div class="p-5 sm:p-8">
    <div class="mx-auto max-w-[1480px]">
        <x-admin.cms.page-header eyebrow="StoreZ / Catalog / Attributes" title="Attributes" description="Define type-safe product specifications and reusable option values."><x-slot:actions><button wire:click="openCreate" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-bold text-white"><x-ui.icon name="plus" class="size-4" /> Add attribute</button></x-slot:actions></x-admin.cms.page-header>
        @if($errors->any())<div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif
        <div class="mb-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">@foreach($stats as $label => $value)<x-admin.cms.stat-card :label="$label" :value="number_format($value)" accent="blue" />@endforeach</div>
        <x-ui.modal id="attribute-editor" width="lg" :heading="$editingId ? 'Edit attribute' : 'Add attribute'" description="Use one value per line for select attributes.">
            <div class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2"><label class="block text-sm font-semibold text-slate-700">Name<input wire:model.live="name" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></label><label class="block text-sm font-semibold text-slate-700">Slug<input wire:model.live="slug" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></label></div>
                <div class="grid gap-4 sm:grid-cols-3"><label class="block text-sm font-semibold text-slate-700">Type<select wire:model.live="type" class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"><option value="text">Text</option><option value="number">Number</option><option value="boolean">Boolean</option><option value="select">Select</option><option value="multi_select">Multi-select</option></select></label><label class="block text-sm font-semibold text-slate-700">Unit<input wire:model.live="unit" placeholder="kg, cm..." class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></label><label class="block text-sm font-semibold text-slate-700">Sort order<input type="number" min="0" wire:model.live="sort_order" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></label></div>
                @if(in_array($type, ['select', 'multi_select'], true))<label class="block text-sm font-semibold text-slate-700">Option values<textarea wire:model.live="values_text" rows="5" placeholder="Small&#10;Medium&#10;Large" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></textarea><span class="mt-1 block text-xs font-normal text-slate-500">One value per line. Assigned values cannot be removed.</span></label>@endif
                <div class="grid gap-3 sm:grid-cols-3"><x-ui.checkbox wire:model.live="is_filterable" label="Filterable" size="sm" /><x-ui.checkbox wire:model.live="is_required" label="Required" size="sm" /><x-ui.checkbox wire:model.live="is_active" label="Active" size="sm" /></div>
                <div class="flex justify-end gap-2"><button type="button" wire:click="$dispatch('close-modal', { id: 'attribute-editor' })" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600">Cancel</button><button wire:click="saveAttribute" wire:loading.attr="disabled" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-bold text-white disabled:opacity-60">Save attribute</button></div>
            </div>
        </x-ui.modal>
        <x-admin.cms.panel title="Attribute definitions" description="Type, filter, and product assignment status for catalog specifications.">
            <x-ui.table.container>
                <div class="mb-4 flex items-center gap-3">
                    <x-ui.input wire:model.live.debounce.300ms="searchQuery" placeholder="Search attributes..." leftIcon="magnifying-glass" class="flex-1" />
                    <select wire:model.live="perPage" aria-label="Rows per page" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm"><option value="15">15</option><option value="30">30</option><option value="50">50</option></select>
                </div>
                <x-ui.table :paginator="$rows" wire:loading loadOn="pagination, search, sorting">
                    <x-ui.table.header><x-ui.table.columns withCheckAll>
                        <x-ui.table.head column="name" sortable :currentSortBy="$sortBy" :currentSortDir="$sortDir">Attribute</x-ui.table.head>
                        <x-ui.table.head>Type</x-ui.table.head><x-ui.table.head>Values</x-ui.table.head><x-ui.table.head>Products</x-ui.table.head><x-ui.table.head>Status</x-ui.table.head><x-ui.table.head>Actions</x-ui.table.head>
                    </x-ui.table.columns></x-ui.table.header>
                    <x-ui.table.rows>
                        @forelse($rows as $row)
                            <x-ui.table.row :checkboxId="$row->id" :key="$row->id" class="hover:bg-slate-50"><x-ui.table.cell><p class="font-bold text-slate-800">{{ $row->name }}</p><p class="text-xs text-slate-500">{{ $row->slug }}{{ $row->unit ? ' · '.$row->unit : '' }}</p></x-ui.table.cell><x-ui.table.cell><x-admin.cms.badge tone="info">{{ str_replace('_', ' ', ucfirst($row->type)) }}</x-admin.cms.badge></x-ui.table.cell><x-ui.table.cell>{{ $row->values_count }}</x-ui.table.cell><x-ui.table.cell>{{ $row->products_count }}</x-ui.table.cell><x-ui.table.cell><x-admin.cms.badge :tone="$row->is_active ? 'success' : 'neutral'">{{ $row->is_active ? 'Active' : 'Inactive' }}</x-admin.cms.badge></x-ui.table.cell><x-ui.table.cell><button wire:click="openEdit({{ $row->id }})" class="font-bold text-blue-600">Edit</button><button wire:click="toggleActive({{ $row->id }})" class="ml-3 font-bold text-slate-600">{{ $row->is_active ? 'Deactivate' : 'Activate' }}</button><button wire:click="deleteAttribute({{ $row->id }})" wire:confirm="Delete this attribute?" class="ml-3 font-bold text-red-600">Delete</button></x-ui.table.cell></x-ui.table.row>
                        @empty
                            <x-ui.table.empty><div class="space-y-1 py-6 text-center"><h3 class="text-sm font-semibold">{{ filled($searchQuery) ? 'No attributes match your search.' : 'No attributes found.' }}</h3><p class="text-sm text-slate-500">{{ filled($searchQuery) ? 'Try a different search term.' : 'Create an attribute to get started.' }}</p></div></x-ui.table.empty>
                        @endforelse
                    </x-ui.table.rows>
                </x-ui.table>
            </x-ui.table.container>
        </x-admin.cms.panel>
    </div>
</div>
