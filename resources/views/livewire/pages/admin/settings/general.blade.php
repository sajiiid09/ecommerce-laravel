<div class="p-5 sm:p-8">
    <div class="mx-auto max-w-[1100px]">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm text-slate-500">StoreZ / Settings / General</p>
                <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900">General settings</h1>
                <p class="mt-1 max-w-2xl text-sm text-slate-500">Manage the store identity and contact details used across the customer-facing storefront.</p>
            </div>
        </div>

        @if (session('status'))
            <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif

        <form wire:submit="save" class="mt-6 space-y-5">
            <x-admin.cms.panel title="Store identity" description="These values provide the default storefront branding and document title suffix.">
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="text-sm font-semibold text-slate-700">
                        Store name
                        <x-ui.input wire:model.live="storeName" class="mt-1" />
                        @error('storeName')<span class="mt-1 block text-xs font-normal text-red-600">{{ $message }}</span>@enderror
                    </label>
                    <label class="text-sm font-semibold text-slate-700">
                        Tagline
                        <x-ui.input wire:model.live="tagline" class="mt-1" />
                        @error('tagline')<span class="mt-1 block text-xs font-normal text-red-600">{{ $message }}</span>@enderror
                    </label>
                </div>
            </x-admin.cms.panel>

            <x-admin.cms.panel title="Brand media" description="Upload the logo and favicon used across the customer-facing storefront.">
                <div class="grid gap-5 lg:grid-cols-2">
                    <div>
                        {{-- Shared Media Library selection is temporarily hidden; direct upload remains available. --}}
                        {{-- <x-admin.media-picker :assets="$mediaAssets" :selected="$logoMediaId" title="Default store logo" context="general_logo" /> --}}
                        <div class="rounded-xl border border-dashed border-blue-200 bg-blue-50/50 p-4">
                            @if($logoAsset)
                                <div class="mb-4 flex items-center gap-3 rounded-lg border border-slate-200 bg-white p-3">
                                    <img src="{{ $logoAsset->url() }}" alt="Current store logo" class="h-12 max-w-40 object-contain">
                                    <div><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Current logo</p><p class="mt-1 truncate text-xs text-slate-500">{{ $logoAsset->filename }}</p></div>
                                </div>
                            @endif
                            <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-start">
                                <div class="min-w-0 flex-1">
                                    <label class="block text-sm font-semibold text-slate-700" for="general-logo-file">Upload a new logo</label>
                                    <x-ui.input id="general-logo-file" type="file" wire:model="logoFile" accept="image/*" class="mt-1" />
                                    <p class="mt-1 text-xs font-normal text-slate-500">Choose an image from your computer. Maximum 10 MB.</p>
                                    @error('logoFile')<span class="mt-1 block text-xs font-normal text-red-600">{{ $message }}</span>@enderror
                                </div>
                                <div class="sm:pt-6">
                                    <x-ui.button type="button" variant="primary" color="blue" icon="arrow-up" class="w-full sm:w-auto"
                                        wire:click="uploadStoreLogo" wire:loading.attr="disabled"
                                        wire:target="uploadStoreLogo,logoFile">
                                        <span wire:loading.remove wire:target="uploadStoreLogo">Upload</span>
                                    <span wire:loading wire:target="uploadStoreLogo">Uploading...</span>
                                    </x-ui.button>
                                </div>
                            </div>
                        </div>
                        <button type="button" wire:click="$set('logoMediaId', null)" class="mt-2 text-xs font-semibold text-slate-500 hover:text-red-600">Use packaged logo fallback</button>
                        @error('logoMediaId')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        {{-- Shared Media Library selection is temporarily hidden; direct upload remains available. --}}
                        {{-- <x-admin.media-picker :assets="$mediaAssets" :selected="$faviconMediaId" title="Store favicon" context="general_favicon" /> --}}
                        <div class="rounded-xl border border-dashed border-blue-200 bg-blue-50/50 p-4">
                            @if($faviconAsset)
                                <div class="mb-4 flex items-center gap-3 rounded-lg border border-slate-200 bg-white p-3">
                                    <img src="{{ $faviconAsset->url() }}" alt="Current store favicon" class="size-12 object-contain">
                                    <div><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Current favicon</p><p class="mt-1 truncate text-xs text-slate-500">{{ $faviconAsset->filename }}</p></div>
                                </div>
                            @endif
                            <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-start">
                                <div class="min-w-0 flex-1">
                                    <label class="block text-sm font-semibold text-slate-700" for="general-favicon-file">Upload a new favicon</label>
                                    <x-ui.input id="general-favicon-file" type="file" wire:model="faviconFile" accept="image/*" class="mt-1" />
                                    <p class="mt-1 text-xs font-normal text-slate-500">Choose an image from your computer. Maximum 10 MB.</p>
                                    @error('faviconFile')<span class="mt-1 block text-xs font-normal text-red-600">{{ $message }}</span>@enderror
                                </div>
                                <div class="sm:pt-6">
                                    <x-ui.button type="button" variant="primary" color="blue" icon="arrow-up" class="w-full sm:w-auto"
                                        wire:click="uploadStoreFavicon" wire:loading.attr="disabled"
                                        wire:target="uploadStoreFavicon,faviconFile">
                                        <span wire:loading.remove wire:target="uploadStoreFavicon">Upload</span>
                                    <span wire:loading wire:target="uploadStoreFavicon">Uploading...</span>
                                    </x-ui.button>
                                </div>
                            </div>
                        </div>
                        <button type="button" wire:click="$set('faviconMediaId', null)" class="mt-2 text-xs font-semibold text-slate-500 hover:text-red-600">Use packaged favicon fallback</button>
                        @error('faviconMediaId')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </div>
                </div>
            </x-admin.cms.panel>

            <x-admin.cms.panel title="Contact information" description="Shown in storefront contact areas and available to future customer-facing pages.">
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="text-sm font-semibold text-slate-700">
                        Support email
                        <x-ui.input type="email" wire:model.live="supportEmail" class="mt-1" autocomplete="email" />
                        @error('supportEmail')<span class="mt-1 block text-xs font-normal text-red-600">{{ $message }}</span>@enderror
                    </label>
                    <label class="text-sm font-semibold text-slate-700">
                        Support phone
                        <x-ui.input type="tel" wire:model.live="supportPhone" class="mt-1" autocomplete="tel" />
                        @error('supportPhone')<span class="mt-1 block text-xs font-normal text-red-600">{{ $message }}</span>@enderror
                    </label>
                </div>
                <label class="mt-4 block text-sm font-semibold text-slate-700">
                    Business address
                    <x-ui.textarea wire:model.live="address" rows="3" class="mt-1" />
                    @error('address')<span class="mt-1 block text-xs font-normal text-red-600">{{ $message }}</span>@enderror
                </label>
            </x-admin.cms.panel>

            <x-admin.cms.panel title="Regional settings" description="Choose the timezone used when displaying store-local dates and times.">
                <label class="block max-w-md text-sm font-semibold text-slate-700">
                    Timezone
                    <x-ui.select
                        wire:model.live="timezone"
                        placeholder="Select timezone"
                        searchable
                        :invalid="$errors->has('timezone')"
                        class="mt-1 w-full"
                    >
                        @foreach($timezones as $timezoneOption)
                            <x-ui.select.option wire:key="timezone-{{ $timezoneOption }}" value="{{ $timezoneOption }}">
                                {{ $timezoneOption }}
                            </x-ui.select.option>
                        @endforeach
                    </x-ui.select>
                    @error('timezone')<span class="mt-1 block text-xs font-normal text-red-600">{{ $message }}</span>@enderror
                </label>
            </x-admin.cms.panel>

            <x-admin.cms.panel title="Delivery districts" description="Manage the active districts and delivery fees available during checkout.">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm text-slate-500">Fees are entered in BDT and stored as minor currency units.</p>
                    <x-ui.button type="button" wire:click="openCreateDistrict" variant="primary" color="blue">Add district</x-ui.button>
                </div>
                @error('districtDelete')<p class="mt-3 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
                <x-ui.table :paginator="$districts" pagination:variant="full" :pagination:options="[10, 25, 50]" wire:loading loadOn="pagination" class="mt-4 w-full overflow-hidden rounded-lg border border-slate-200 bg-white p-0" table:class="w-full min-w-[640px] table-fixed text-left">
                    <x-ui.table.header class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                            <x-ui.table.columns><x-ui.table.head class="px-4 py-3">District</x-ui.table.head><x-ui.table.head class="px-4 py-3">Delivery fee</x-ui.table.head><x-ui.table.head class="px-4 py-3">Status</x-ui.table.head><x-ui.table.head class="px-4 py-3 text-right">Actions</x-ui.table.head></x-ui.table.columns>
                    </x-ui.table.header>
                    <x-ui.table.rows class="divide-y divide-slate-100">
                            @forelse($districts as $district)
                                <x-ui.table.row :key="'settings-district-'.$district->id" class="text-sm text-slate-700 hover:bg-slate-50">
                                    <x-ui.table.cell class="px-4 py-4 font-bold text-slate-800">{{ $district->name }}</x-ui.table.cell>
                                    <x-ui.table.cell class="px-4 py-4 text-slate-600">৳{{ number_format($district->delivery_fee_minor / 100, 2) }}</x-ui.table.cell>
                                    <x-ui.table.cell class="px-4 py-4"><x-ui.badge variant="solid" :color="$district->is_active ? 'emerald' : 'slate'" pill size="sm">{{ $district->is_active ? 'Active' : 'Inactive' }}</x-ui.badge></x-ui.table.cell>
                                    <x-ui.table.cell class="whitespace-nowrap px-4 py-4 text-right">
                                        <x-ui.dropdown position="bottom-end" portal>
                                            <x-slot:button><x-ui.button type="button" size="sm" variant="outline" color="slate" icon-after="chevron-down" aria-label="Actions for {{ $district->name }}">Actions</x-ui.button></x-slot:button>
                                            <x-slot:menu>
                                                <x-ui.dropdown.item as="button" type="button" wire:click="openEditDistrict({{ $district->id }})" icon="pencil">Edit</x-ui.dropdown.item>
                                                <x-ui.dropdown.item as="button" type="button" wire:click="toggleDistrict({{ $district->id }})" icon="power">{{ $district->is_active ? 'Deactivate' : 'Activate' }}</x-ui.dropdown.item>
                                                <x-ui.dropdown.separator />
                                                <x-ui.dropdown.item as="button" type="button" wire:click="deleteDistrict({{ $district->id }})" icon="trash" variant="danger">Delete</x-ui.dropdown.item>
                                            </x-slot:menu>
                                        </x-ui.dropdown>
                                    </x-ui.table.cell>
                                </x-ui.table.row>
                            @empty
                                <x-ui.table.empty>No districts configured yet.</x-ui.table.empty>
                            @endforelse
                    </x-ui.table.rows>
                </x-ui.table>
            </x-admin.cms.panel>

            <div class="flex justify-end">
                <button type="submit" wire:loading.attr="disabled" wire:target="save" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-700 disabled:cursor-wait disabled:opacity-60">
                    <span wire:loading.remove wire:target="save">Save settings</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
            </div>
        </form>

        <x-ui.modal id="district-editor" width="xl" :heading="$editingDistrictId ? 'Edit district' : 'Add district'" description="Configure the district name and checkout delivery pricing.">
            <form wire:submit="saveDistrict" class="space-y-5">
                <label class="block text-sm font-semibold text-slate-700">District name<x-ui.input wire:model="districtName" class="mt-2" />@error('districtName')<span class="mt-1 block text-xs font-normal text-red-600">{{ $message }}</span>@enderror</label>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm font-semibold text-slate-700">Delivery fee<x-ui.input wire:model="districtFee" type="number" min="0" step="0.01" prefix="BDT" class="mt-2" />@error('districtFee')<span class="mt-1 block text-xs font-normal text-red-600">{{ $message }}</span>@enderror</label>
                    <label class="block text-sm font-semibold text-slate-700">Sort order<x-ui.input wire:model="districtSortOrder" type="number" min="0" step="1" class="mt-2" />@error('districtSortOrder')<span class="mt-1 block text-xs font-normal text-red-600">{{ $message }}</span>@enderror</label>
                </div>
                <x-ui.checkbox wire:model="districtIsActive" label="District is active" />
                <div class="flex justify-end gap-3 border-t border-slate-200 pt-4"><x-ui.button type="button" variant="outline" wire:click="$dispatch('close-modal', { id: 'district-editor' })">Cancel</x-ui.button><x-ui.button type="submit" variant="primary" color="blue">Save district</x-ui.button></div>
            </form>
        </x-ui.modal>
    </div>
</div>
