<div class="p-5 sm:p-8">
    <div class="mx-auto max-w-[1480px]">
        <div class="mb-6">
            <x-admin.cms.page-header eyebrow="StoreZ / Content / Navigation" title="Navigation manager" description="Organize storefront menus, nested links, and internal destinations from one place.">
                <x-slot:actions>
                    <div class="flex flex-wrap gap-2">
                        <x-ui.button type="button" variant="outline" color="blue" icon="plus" wire:click="openAddItem">Add menu item</x-ui.button>
                        <x-ui.button type="button" icon="check" wire:click="saveMenu" wire:loading.attr="disabled" wire:target="saveMenu">
                            <span wire:loading.remove wire:target="saveMenu">Save menu</span>
                            <span wire:loading wire:target="saveMenu">Saving...</span>
                        </x-ui.button>
                    </div>
                </x-slot:actions>
            </x-admin.cms.page-header>
        </div>
        @if(session('status'))<div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('status') }}</div>@endif
        @if($errors->any())<div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif

        <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_360px]">
            <x-admin.cms.panel title="{{ $menu?->name ?: 'Primary navigation' }}" description="Use Edit to change an existing item, or use the order controls to maintain a clear hierarchy.">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-lg bg-slate-50 px-3 py-2 text-xs"><span class="font-bold text-slate-700">{{ $menu?->items?->count() ?? 0 }} top-level items</span><span class="text-slate-500">Location: {{ str_replace('_', ' ', ucfirst($location)) }}</span></div>
                <div class="space-y-2">
                    @forelse($menu?->items ?? [] as $item)
                        <div wire:key="menu-item-{{ $item->id }}" class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm">
                            <div class="flex items-center gap-3">
                                <span class="cursor-grab text-slate-400" aria-label="Drag to reorder">⠿</span>
                                <span class="grid size-8 place-items-center rounded-md bg-blue-50 text-xs font-bold text-blue-700">{{ $loop->iteration }}</span>
                                <div class="min-w-0 flex-1"><p class="truncate text-sm font-bold text-slate-800">{{ $item->label }}</p><p class="truncate text-xs text-slate-500">{{ $item->type }} · {{ $item->url ?: ($item->targets->first()?->target?->slug ?? '⚠ Broken target') }}</p></div>
                                <span class="size-2 rounded-full {{ $item->enabled ? 'bg-emerald-500' : 'bg-slate-300' }}" title="{{ $item->enabled ? 'Enabled' : 'Disabled' }}"></span>
                                <x-ui.button type="button" variant="outline" color="blue" size="xs" wire:click="editItem({{ $item->id }})">Edit</x-ui.button>
                                <button wire:click="moveItem({{ $item->id }}, -1)" wire:loading.attr="disabled" wire:target="moveItem({{ $item->id }}, -1)" class="rounded px-2 py-1 text-xs font-bold text-slate-500 disabled:opacity-60" aria-label="Move {{ $item->label }} up">↑</button>
                                <button wire:click="moveItem({{ $item->id }}, 1)" wire:loading.attr="disabled" wire:target="moveItem({{ $item->id }}, 1)" class="rounded px-2 py-1 text-xs font-bold text-slate-500 disabled:opacity-60" aria-label="Move {{ $item->label }} down">↓</button>
                                <button wire:click="deleteItem({{ $item->id }})" wire:confirm="Delete this menu item?" wire:loading.attr="disabled" wire:target="deleteItem({{ $item->id }})" class="text-xs font-bold text-red-600 disabled:opacity-60">Delete</button>
                            </div>
                            @if($item->children->isNotEmpty())<div class="mt-3 space-y-2 border-l-2 border-slate-100 pl-9">@foreach($item->children as $child)<div wire:key="menu-child-{{ $child->id }}" class="flex items-center gap-2 rounded-lg bg-slate-50 px-3 py-2 text-sm"><span class="text-slate-400">↳</span><span class="min-w-0 flex-1 truncate font-semibold text-slate-700">{{ $child->label }}</span><span class="text-[11px] text-slate-400">{{ $child->type }}</span><x-ui.button type="button" variant="outline" color="blue" size="xs" wire:click="editItem({{ $child->id }})">Edit</x-ui.button><button wire:click="deleteItem({{ $child->id }})" wire:confirm="Delete this menu item?" wire:loading.attr="disabled" class="text-xs font-bold text-red-600 disabled:opacity-60">Remove</button></div>@endforeach</div>@endif
                        </div>
                    @empty
                        <div class="rounded-lg border border-dashed border-slate-300 px-4 py-12 text-center text-sm text-slate-500">No navigation items yet. Add your first item from the editor.</div>
                    @endforelse
                </div>
            </x-admin.cms.panel>

            <div class="space-y-5">
                <x-admin.cms.panel title="Menu settings" description="Identify this menu and choose where its links appear on the storefront.">
                    <div class="space-y-4">
                        <label class="block text-sm font-semibold text-slate-700">Name<input wire:model.live="name" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm"></label>
                        <label class="block text-sm font-semibold text-slate-700">Key<input wire:model.live="key" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm"></label>
                        <label class="block text-sm font-semibold text-slate-700">Location<select wire:model.live="location" class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm"><option value="header_primary">Header primary</option><option value="mobile">Mobile</option><option value="footer_shop">Footer shop</option></select></label>
                        <div class="rounded-lg border border-blue-100 bg-blue-50/60 p-3 text-xs text-slate-600">
                            <p class="font-bold text-slate-800">What these settings control</p>
                            <dl class="mt-2 space-y-2">
                                <div><dt class="font-semibold text-slate-700">Name</dt><dd>Admin-facing label used to identify this menu.</dd></div>
                                <div><dt class="font-semibold text-slate-700">Key</dt><dd>Stable identifier used by Header and Footer settings to select this menu. Change it only when you also update those assignments.</dd></div>
                                <div><dt class="font-semibold text-slate-700">Location</dt><dd>Controls where the menu is rendered: desktop header, mobile navigation, or the footer shop column.</dd></div>
                            </dl>
                        </div>
                        <x-ui.button type="button" class="w-full" wire:click="saveMenu" wire:loading.attr="disabled" wire:target="saveMenu">
                            <span wire:loading.remove wire:target="saveMenu">Save settings</span>
                            <span wire:loading wire:target="saveMenu">Saving...</span>
                        </x-ui.button>
                    </div>
                </x-admin.cms.panel>

                <x-admin.cms.panel title="Add menu item" description="Create a link that customers can use to navigate your storefront.">
                    <p class="mb-4 text-sm leading-6 text-slate-600">Add a label and destination, then optionally choose a parent item to place this link inside a nested menu. After saving, it appears in the selected menu location.</p>
                    <x-ui.button type="button" variant="outline" color="blue" icon="plus" wire:click="openAddItem">Open item editor</x-ui.button>
                </x-admin.cms.panel>
            </div>
        </div>

        <x-ui.modal id="menu-item-editor" width="lg" :heading="$editingItemId ? 'Edit menu item' : 'Add menu item'" description="Create a storefront link and optionally place it beneath another menu item.">
            <form wire:submit="saveItem" class="space-y-4">
                <label class="block text-sm font-semibold text-slate-700">Label<x-ui.input wire:model.live="label" placeholder="Shop" class="mt-1" /></label>
                @error('label')<p class="text-xs text-red-600">{{ $message }}</p>@enderror

                <label class="block text-sm font-semibold text-slate-700">Link type<select wire:model.live="type" class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm"><option value="custom_url">Custom URL</option><option value="route">Named route</option><option value="page">Page</option><option value="category">Category</option><option value="brand">Brand</option><option value="product">Product</option></select></label>

                @if($type === 'custom_url')
                    <label class="block text-sm font-semibold text-slate-700">URL<x-ui.input wire:model.live="url" type="url" placeholder="https://example.com/offers" class="mt-1" /></label>
                    @error('url')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                @elseif($type === 'route')
                    <label class="block text-sm font-semibold text-slate-700">Route name<x-ui.input wire:model.live="route_name" placeholder="home" class="mt-1" /></label>
                    @error('route_name')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                @else
                    <label class="block text-sm font-semibold text-slate-700">Target ID<x-ui.input wire:model.live="target_id" type="number" min="1" placeholder="Numeric ID" class="mt-1" /></label>
                    <p class="-mt-2 text-xs text-slate-500">Enter the ID of the selected page, category, brand, or product.</p>
                    @error('target_id')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                @endif

                <label class="block text-sm font-semibold text-slate-700">Parent item ID<x-ui.input wire:model.live="parent_id" type="number" min="1" placeholder="Optional parent item ID" class="mt-1" /></label>
                @error('parent_id')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                <x-ui.checkbox wire:model.live="enabled" label="Enabled" description="Show this item on the storefront." size="sm" />

                <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                    <x-ui.button type="button" variant="outline" color="slate" wire:click="$dispatch('close-modal', { id: 'menu-item-editor' })">Cancel</x-ui.button>
                    <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="saveItem">
                        <span wire:loading.remove wire:target="saveItem">{{ $editingItemId ? 'Save changes' : 'Add item' }}</span>
                        <span wire:loading wire:target="saveItem">Saving...</span>
                    </x-ui.button>
                </div>
            </form>
        </x-ui.modal>
    </div>
</div>
