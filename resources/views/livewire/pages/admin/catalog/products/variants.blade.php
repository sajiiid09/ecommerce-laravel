<div class="p-5 sm:p-8">
    <div class="mx-auto max-w-[1480px]">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold text-blue-600">StoreZ / Catalog / Products / {{ $product->name }} / Variants</p>
                <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-slate-950">Options &amp; Variants</h1>
                <p class="mt-1 text-sm text-slate-500">Configure customer-selectable options and sellable SKU combinations.</p>
            </div>
            <a href="{{ url('/admin/catalog/products/'.$product->id.'/edit') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700">Back to product</a>
        </div>

        @if(session('status'))
            <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('status') }}</div>
        @endif

        @if($errors->any())
            <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif

        <div class="space-y-5">
            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="font-bold text-slate-950">Product options</h2>
                        <p class="mt-1 text-xs text-slate-500">Add options such as Color, Size, or Storage before generating combinations.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <input wire:model="optionName" placeholder="Option name" class="w-36 rounded-lg border border-slate-200 px-3 py-2 text-sm">
                        <button type="button" wire:click="addOption" wire:loading.attr="disabled" wire:target="addOption" class="rounded-lg border border-dashed border-blue-500 px-3 py-2 text-xs font-bold text-blue-600 disabled:opacity-60">+ Add option</button>
                    </div>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse($options as $option)
                        <div wire:key="product-option-{{ $option->id }}" class="rounded-lg border border-slate-200 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm font-bold text-slate-800">{{ $option->name }}</span>
                                <button type="button" wire:click="removeOption({{ $option->id }})" wire:confirm="Remove this option and regenerate variants?" class="text-xs font-bold text-red-600">Delete option</button>
                            </div>
                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                @foreach($option->values as $value)
                                    <span wire:key="option-value-{{ $value->id }}" class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700">
                                        {{ $value->value }}
                                        <button type="button" wire:click="removeValue({{ $option->id }}, {{ $value->id }})" wire:confirm="Remove this option value?" class="text-slate-400 hover:text-red-600" aria-label="Remove {{ $value->value }}">&times;</button>
                                    </span>
                                @endforeach
                                <input wire:model="optionValues.{{ $option->id }}" placeholder="New value" class="w-28 rounded-full border border-dashed border-blue-400 px-3 py-1.5 text-xs">
                                <button type="button" wire:click="addValue({{ $option->id }})" wire:loading.attr="disabled" class="rounded-full border border-dashed border-blue-400 px-3 py-1.5 text-xs font-bold text-blue-600 disabled:opacity-60">+ Add value</button>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-lg border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500">No options yet.</div>
                    @endforelse
                </div>

                <div class="mt-5 flex justify-end">
                    <button type="button" wire:click="generate" wire:loading.attr="disabled" wire:target="generate" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-bold text-white disabled:opacity-60">
                        <span wire:loading.remove wire:target="generate">Generate variants</span>
                        <span wire:loading wire:target="generate">Generating...</span>
                    </button>
                </div>
            </section>

            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 p-5">
                    <h2 class="font-bold text-slate-950">Generated variants</h2>
                    <p class="mt-1 text-xs text-slate-500">Use the Action menu to edit, change availability, or delete a variant.</p>
                </div>

                <x-ui.table
                    :paginator="$variants"
                    pagination:variant="full"
                    :pagination:options="[15, 30, 50]"
                    wire:loading
                    loadOn="pagination"
                    class="w-full overflow-hidden"
                    table:class="w-full min-w-[900px] table-fixed text-left"
                >
                    <x-ui.table.header class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-400">
                        <x-ui.table.columns>
                            <x-ui.table.head class="px-4 py-3">Variant</x-ui.table.head>
                            <x-ui.table.head class="px-4 py-3">SKU</x-ui.table.head>
                            <x-ui.table.head class="px-4 py-3">Price</x-ui.table.head>
                            <x-ui.table.head class="px-4 py-3">Stock</x-ui.table.head>
                            <x-ui.table.head class="px-4 py-3">Status</x-ui.table.head>
                            <x-ui.table.head class="px-4 py-3 text-right">Actions</x-ui.table.head>
                        </x-ui.table.columns>
                    </x-ui.table.header>
                    <x-ui.table.rows>
                        @forelse($variants as $variant)
                            <x-ui.table.row :key="'product-variant-'.$variant->id" class="hover:bg-slate-50">
                                <x-ui.table.cell class="px-4 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($variant->optionValues as $value)
                                            <span class="rounded-full bg-slate-100 px-2 py-1 text-[11px] font-bold text-slate-700">{{ $value->value }}</span>
                                        @empty
                                            <span class="text-slate-400">Default</span>
                                        @endforelse
                                    </div>
                                </x-ui.table.cell>
                                <x-ui.table.cell class="px-4 py-4 text-xs font-semibold text-slate-600">{{ $variant->sku }}</x-ui.table.cell>
                                <x-ui.table.cell class="px-4 py-4">{{ number_format(($variant->currentPriceMinor() ?? 0) / 100, 2) }}</x-ui.table.cell>
                                <x-ui.table.cell class="px-4 py-4 font-semibold">{{ $variant->availableQuantity() }}</x-ui.table.cell>
                                <x-ui.table.cell class="px-4 py-4">
                                    <x-admin.cms.badge :tone="$variant->is_active ? 'success' : 'neutral'">{{ $variant->is_active ? 'Active' : 'Inactive' }}</x-admin.cms.badge>
                                </x-ui.table.cell>
                                <x-ui.table.cell class="px-4 py-4 text-right">
                                    <x-ui.dropdown position="bottom-end">
                                        <x-slot:button>
                                            <x-ui.button type="button" size="sm" variant="outline" color="slate" icon-after="chevron-down" aria-label="Actions for {{ $variant->sku }}">Action</x-ui.button>
                                        </x-slot:button>
                                        <x-slot:menu>
                                            <x-ui.dropdown.item wire:click="openVariantEditor({{ $variant->id }})" icon="pencil">Edit</x-ui.dropdown.item>
                                            <x-ui.dropdown.item wire:click="toggle({{ $variant->id }})" icon="check">{{ $variant->is_active ? 'Deactivate' : 'Activate' }}</x-ui.dropdown.item>
                                            <x-ui.dropdown.separator />
                                            <x-ui.dropdown.item wire:click="deleteVariant({{ $variant->id }})" wire:confirm="Delete this variant?" variant="danger" icon="trash">Delete</x-ui.dropdown.item>
                                        </x-slot:menu>
                                    </x-ui.dropdown>
                                </x-ui.table.cell>
                            </x-ui.table.row>
                        @empty
                            <x-ui.table.empty>Generate variants from the options above.</x-ui.table.empty>
                        @endforelse
                    </x-ui.table.rows>
                </x-ui.table>
            </section>
        </div>

        <x-ui.modal
            id="variant-editor"
            width="3xl"
            heading="Edit variant"
            description="Update pricing, inventory, availability, and variant-specific media."
            stickyFooter
        >
            @if($selectedVariantId)
                <form wire:submit="saveVariant" class="space-y-5">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block text-sm font-semibold text-slate-700">
                            SKU
                            <input wire:model="variantSku" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                        </label>
                        <label class="block text-sm font-semibold text-slate-700">
                            Barcode
                            <input wire:model="variantBarcode" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                        </label>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block text-sm font-semibold text-slate-700">
                            Regular price (BDT)
                            <input type="number" min="0" step="0.01" wire:model="variantRegularPrice" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                        </label>
                        <label class="block text-sm font-semibold text-slate-700">
                            Sale price (BDT)
                            <input type="number" min="0" step="0.01" wire:model="variantSalePrice" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                        </label>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block text-sm font-semibold text-slate-700">
                            Cost price (BDT)
                            <input type="number" min="0" step="0.01" wire:model="variantCostPrice" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                        </label>
                        <label class="block text-sm font-semibold text-slate-700">
                            Weight (grams)
                            <input type="number" min="0" wire:model="variantWeightGrams" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                        </label>
                    </div>

                    <div class="border-t border-slate-100 pt-4">
                        <h3 class="text-sm font-bold text-slate-800">Inventory</h3>
                        <div class="mt-3 grid gap-4 sm:grid-cols-2">
                            <label class="text-sm font-semibold text-slate-700">
                                On hand
                                <input type="number" min="0" wire:model="variantQuantity" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                            </label>
                            <label class="text-sm font-semibold text-slate-700">
                                Low stock
                                <input type="number" min="0" wire:model="variantLowStockThreshold" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                            </label>
                        </div>
                        <x-ui.checkbox class="mt-3" wire:model="variantTrackQuantity" label="Track quantity" size="sm" />
                        <x-ui.checkbox class="mt-2" wire:model="variantAllowBackorders" label="Allow backorders" size="sm" />
                    </div>

                    <div class="border-t border-slate-100 pt-4">
                        <x-ui.checkbox wire:model="variantIsActive" label="Active" size="sm" />
                        <x-ui.checkbox class="mt-3" wire:model="variantIsDefault" label="Default variant" size="sm" />
                    </div>

                    <div class="border-t border-slate-100 pt-4">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="text-sm font-bold text-slate-800">Variant media</h3>
                            <button type="button" x-data x-on:click="$dispatch('open-media-picker', { context: 'variant-gallery' })" class="text-xs font-bold text-blue-600">Add media</button>
                        </div>
                        <label class="mt-3 block rounded-lg border border-dashed border-blue-300 bg-blue-50/50 p-3 text-sm font-semibold text-slate-700">
                            Upload a new variant image
                            <input type="file" wire:model="variantImage" wire:loading.attr="disabled" wire:target="variantImage" accept="image/*" class="mt-2 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-normal disabled:cursor-wait disabled:opacity-60">
                            <span class="mt-1 block text-xs font-normal text-slate-500">The uploaded image will be added when you press Save variant.</span>
                        </label>
                        <div wire:loading wire:target="variantImage" class="mt-2 flex items-center gap-2 rounded-lg bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700">
                            <svg class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4Z"/></svg>
                            Uploading image&hellip; please wait.
                        </div>
                        @error('variantImage')<p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                        @if($variantImage)
                            <p class="mt-2 rounded-lg bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700">Ready to add: {{ $variantImage->getClientOriginalName() }}</p>
                        @endif
                        <div class="mt-3 grid grid-cols-3 gap-2 sm:grid-cols-4">
                            @if($variantImage)
                                <div wire:key="variant-image-upload-preview" class="group relative aspect-square overflow-hidden rounded-lg border-2 border-dashed border-blue-400 bg-blue-50">
                                    <img src="{{ $variantImage->temporaryUrl() }}" alt="Preview of {{ $variantImage->getClientOriginalName() }}" class="size-full object-cover">
                                    <span class="absolute left-1 top-1 rounded bg-blue-600 px-1.5 py-0.5 text-[10px] font-bold text-white">New</span>
                                    <button type="button" wire:click="removeVariantImageUpload" class="absolute right-1 top-1 grid size-7 place-items-center rounded-full bg-white/95 text-base font-bold leading-none text-red-600 shadow" aria-label="Remove uploaded image">&times;</button>
                                </div>
                            @endif
                            @forelse($selectedVariantMedia as $asset)
                                <div wire:key="variant-media-{{ $asset->id }}" class="group relative aspect-square overflow-hidden rounded-lg border border-slate-200">
                                    <img src="{{ $asset->url() }}" alt="{{ $asset->alt_text ?: $asset->filename }}" class="size-full object-cover">
                                    <button type="button" wire:click="removeMedia({{ $asset->id }})" class="absolute right-1 top-1 grid size-7 place-items-center rounded-full bg-white/95 text-base font-bold leading-none text-red-600 shadow" aria-label="Remove {{ $asset->filename }}">&times;</button>
                                    <div class="absolute inset-x-1 bottom-1 flex justify-center gap-1 opacity-0 transition group-hover:opacity-100 group-focus-within:opacity-100">
                                        <button type="button" wire:click="moveMedia({{ array_search($asset->id, $selectedVariantMediaIds, true) }}, -1)" class="rounded bg-white px-1 text-xs" aria-label="Move media left">&larr;</button>
                                        <button type="button" wire:click="moveMedia({{ array_search($asset->id, $selectedVariantMediaIds, true) }}, 1)" class="rounded bg-white px-1 text-xs" aria-label="Move media right">&rarr;</button>
                                    </div>
                                </div>
                            @empty
                                @unless($variantImage)
                                    <p class="col-span-full text-xs text-slate-500">No variant-specific media selected.</p>
                                @endunless
                            @endforelse
                        </div>
                        <p class="mt-2 text-xs text-slate-500">You can add multiple images to this variant. The first image is used as its main image.</p>
                        <x-admin.media-picker :assets="$mediaAssets" :selected="$selectedVariantMediaIds[0] ?? null" title="Select variant media" context="variant-gallery" modal />
                    </div>

                    <div class="flex justify-end gap-3 border-t border-slate-200 pt-4">
                        <button type="button" wire:click="$dispatch('close-modal', { id: 'variant-editor' })" class="rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600">Cancel</button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="saveVariant" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-bold text-white disabled:opacity-60">
                            <span wire:loading.remove wire:target="saveVariant">Save variant</span>
                            <span wire:loading wire:target="saveVariant">Saving...</span>
                        </button>
                    </div>
                </form>
            @else
                <p class="text-sm text-slate-500">Select a generated variant to edit its details.</p>
            @endif
        </x-ui.modal>
    </div>
</div>
