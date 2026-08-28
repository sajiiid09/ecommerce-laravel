<div class="p-5 sm:p-8">
    <div class="mx-auto max-w-[1280px]">
        <x-admin.cms.page-header eyebrow="StoreZ / Content / Header" title="Header settings"
            description="Configure the storefront header and its storefront behavior.">
            <x-ui.button type="button" icon="check" wire:click="saveHeader" wire:loading.attr="disabled"
                wire:target="saveHeader" size="sm">
                <span wire:loading.remove wire:target="saveHeader">Save header</span>
                <span wire:loading wire:target="saveHeader">Saving...</span>
            </x-ui.button>
        </x-admin.cms.page-header>

        @if(session('status'))
            <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="space-y-5">
            <x-admin.cms.panel title="Branding" description="Choose a logo asset or provide a fallback URL.">
                <div class="space-y-4">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-sm font-semibold text-slate-700">Current header logo</p>
                        @if(filled($logo_url))
                            <div class="mt-3 flex min-h-24 items-center gap-4 rounded-lg border border-slate-200 bg-white p-3">
                                <img src="{{ $logo_url }}" alt="Current header logo" class="max-h-16 max-w-64 object-contain" />
                                <p class="text-xs text-slate-500">This image is used in the storefront header.</p>
                            </div>
                        @else
                            <p class="mt-2 text-sm text-slate-500">No header logo selected.</p>
                        @endif
                    </div>

                    <label class="block text-sm font-semibold text-slate-700">
                        Fallback image URL
                        <x-ui.input wire:model.live="logo_url" placeholder="https://... or /images/brand/logo.webp" class="mt-1" inputmode="url" />
                        <span class="mt-1 block text-xs font-normal text-slate-500">Use this only when a logo is not uploaded.</span>
                    </label>

                    <div class="rounded-xl border border-dashed border-blue-200 bg-blue-50/50 p-4">
                        <label class="block text-sm font-semibold text-slate-700" for="header-logo-file">Upload a new logo</label>
                        <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-center">
                            <x-ui.input id="header-logo-file" type="file" wire:model="header_logo_file" accept="image/*" class="min-w-0 flex-1" />
                            <x-ui.button type="button" variant="primary" color="blue" icon="arrow-up"
                                wire:click="uploadHeaderLogo" wire:loading.attr="disabled"
                                wire:target="uploadHeaderLogo,header_logo_file" class="w-full shrink-0 sm:w-auto">
                                <span wire:loading.remove wire:target="uploadHeaderLogo">Upload and select</span>
                                <span wire:loading wire:target="uploadHeaderLogo">Uploading...</span>
                            </x-ui.button>
                        </div>
                        <p class="mt-1 text-xs font-normal text-slate-500">Choose an image from your computer. Maximum 10 MB.</p>
                        @error('header_logo_file')<span class="mt-1 block text-xs font-normal text-red-600">{{ $message }}</span>@enderror
                    </div>

                    {{-- The shared media picker is intentionally hidden; header logos use direct upload. --}}
                    {{-- <x-admin.media-picker :assets="$mediaAssets" :selected="$logo_media_id" title="Header logo" context="header_logo" /> --}}

                    <label class="block text-sm font-semibold text-slate-700">
                        Support text
                        <x-ui.input wire:model.live="support_text" placeholder="Need help? Call us" class="mt-1" />
                    </label>
                </div>
            </x-admin.cms.panel>

            <x-admin.cms.panel title="Header behavior">
                <div class="space-y-3">
                    <p class="text-xs text-slate-500">Visibility changes are saved automatically.</p>
                    <x-ui.checkbox wire:model.live="show_search" label="Show search"
                        description="Display the storefront search field." variant="cards" size="sm" />
                    <x-ui.checkbox wire:model.live="sticky" label="Sticky header"
                        description="Keep the header visible while scrolling." variant="cards" size="sm" />
                    <x-ui.checkbox wire:model.live="show_announcement" label="Show announcements"
                        description="Display active storefront announcements." variant="cards" size="sm" />

                    <label class="block text-sm font-semibold text-slate-700">
                        Desktop menu
                        <x-ui.select wire:model.live="desktop_menu_key" class="mt-1 w-full">
                            <x-ui.select.option value="">No menu</x-ui.select.option>
                            @foreach($menus as $menu)
                                <x-ui.select.option wire:key="header-desktop-menu-{{ $menu->key }}" value="{{ $menu->key }}">{{ $menu->name }}</x-ui.select.option>
                            @endforeach
                        </x-ui.select>
                    </label>

                    <label class="block text-sm font-semibold text-slate-700">
                        Mobile menu
                        <x-ui.select wire:model.live="mobile_menu_key" class="mt-1 w-full">
                            <x-ui.select.option value="">Use category fallback</x-ui.select.option>
                            @foreach($menus as $menu)
                                <x-ui.select.option wire:key="header-mobile-menu-{{ $menu->key }}" value="{{ $menu->key }}">{{ $menu->name }}</x-ui.select.option>
                            @endforeach
                        </x-ui.select>
                    </label>
                </div>
            </x-admin.cms.panel>
        </div>
    </div>
</div>
