<div class="p-5 sm:p-8">
    <div class="mx-auto max-w-[1280px]">
        <x-admin.cms.page-header eyebrow="StoreZ / Content / Header" title="Header settings" description="Configure the storefront header while keeping the real customer experience in view.">
            <x-slot:actions>
                <button wire:click="saveHeader" wire:loading.attr="disabled" wire:target="saveHeader" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm disabled:opacity-60">
                    <x-ui.icon name="check" class="size-4" /><span wire:loading.remove wire:target="saveHeader">Save header</span><span wire:loading wire:target="saveHeader">Saving...</span>
                </button>
            </x-slot:actions>
        </x-admin.cms.page-header>
        @if (session('status'))<div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif

        <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_380px]">
            <div class="space-y-5">
                <x-admin.cms.panel title="Branding" description="Choose a shared logo asset or provide a fallback URL.">
                    <div class="space-y-4">
                        <label class="block text-sm font-semibold text-slate-700">Logo URL<input wire:model.live="logo_url" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm" placeholder="https://..."></label>
                        <x-admin.media-picker :assets="$mediaAssets" :selected="$logo_media_id" title="Header logo" context="header_logo" />
                        <label class="block text-sm font-semibold text-slate-700">Support text<input wire:model.live="support_text" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm" placeholder="Need help? Call us"></label>
                    </div>
                </x-admin.cms.panel>
                <x-admin.cms.panel title="Header behavior">
                    <div class="space-y-3">
                        <label class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 p-3 text-sm font-semibold"><span>Show search</span><input type="checkbox" wire:model.live="show_search" class="rounded border-slate-300 text-blue-600"></label>
                        <label class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 p-3 text-sm font-semibold"><span>Sticky header</span><input type="checkbox" wire:model.live="sticky" class="rounded border-slate-300 text-blue-600"></label>
                        <label class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 p-3 text-sm font-semibold"><span>Show announcements</span><input type="checkbox" wire:model.live="show_announcement" class="rounded border-slate-300 text-blue-600"></label>
                        <label class="block text-sm font-semibold text-slate-700">Desktop menu<select wire:model.live="desktop_menu_key" class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm"><option value="">No menu</option>@foreach($menus as $menu)<option wire:key="header-desktop-menu-{{ $menu->key }}" value="{{ $menu->key }}">{{ $menu->name }}</option>@endforeach</select></label>
                        <label class="block text-sm font-semibold text-slate-700">Mobile menu<select wire:model.live="mobile_menu_key" class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm"><option value="">Use category fallback</option>@foreach($menus as $menu)<option wire:key="header-mobile-menu-{{ $menu->key }}" value="{{ $menu->key }}">{{ $menu->name }}</option>@endforeach</select></label>
                    </div>
                </x-admin.cms.panel>
            </div>
            <x-admin.cms.panel title="Header preview" description="The saved settings rendered by the storefront header component.">
                <iframe src="{{ route('admin.content.header.preview') }}" title="Storefront header preview" class="h-64 w-full rounded-xl border border-slate-200 bg-white"></iframe>
                <p class="mt-3 text-xs text-slate-500">Save changes, then refresh this preview to verify responsive behavior.</p>
            </x-admin.cms.panel>
        </div>
    </div>
</div>
