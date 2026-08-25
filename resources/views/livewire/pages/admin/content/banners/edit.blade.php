<div class="p-5 sm:p-8">
    <div class="mx-auto max-w-[1280px]">
        <x-admin.cms.page-header eyebrow="StoreZ / Content / Banners / {{ $bannerId ? 'Edit' : 'Create' }}" :title="$bannerId ? 'Edit banner' : 'Create banner'" description="Build a responsive campaign banner with a clear destination and publication window.">
            <x-slot:actions>
                <a href="{{ url('/admin/content/banners') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700">Cancel</a>
                <button wire:click="saveBanner" wire:loading.attr="disabled" wire:target="saveBanner" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-700 disabled:opacity-60"><x-ui.icon name="check" class="size-4" /><span wire:loading.remove wire:target="saveBanner">Save banner</span><span wire:loading wire:target="saveBanner">Saving...</span></button>
            </x-slot:actions>
        </x-admin.cms.page-header>
        @if(session('status'))<div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('status') }}</div>@endif
        @if($errors->any())<div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif

        <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_360px]">
            <div class="space-y-5">
                <x-admin.cms.panel title="Banner information" description="The internal name helps your team identify this campaign. The title and description are customer-facing.">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="text-sm font-semibold text-slate-700">Internal name<input wire:model.live="name" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm"></label>
                        <label class="text-sm font-semibold text-slate-700">Placement<select wire:model.live="placement" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm"><option value="homepage">Homepage</option><option value="category">Category</option><option value="offers">Offers</option></select></label>
                        <label class="text-sm font-semibold text-slate-700 sm:col-span-2">Headline<input wire:model.live="title" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm" placeholder="A compelling campaign headline"></label>
                        <label class="text-sm font-semibold text-slate-700 sm:col-span-2">Description<textarea wire:model.live="description" rows="4" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm" placeholder="Supporting campaign message"></textarea></label>
                        <label class="text-sm font-semibold text-slate-700">CTA label<input wire:model.live="cta_label" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm" placeholder="Shop now"></label>
                        <label class="text-sm font-semibold text-slate-700">Destination type<select wire:model.live="destination_type" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm"><option value="url">Custom URL</option><option value="page">Page</option><option value="category">Category</option><option value="product">Product</option></select></label>
                        <label class="text-sm font-semibold text-slate-700 sm:col-span-2">Destination<input wire:model.live="destination_value" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm" placeholder="https://... or an internal target"></label>
                    </div>
                </x-admin.cms.panel>
                <x-admin.cms.panel title="Responsive media" description="Use the shared Media Library so campaign assets remain reusable and governed.">
                    <div class="grid gap-4 md:grid-cols-2"><x-admin.media-picker :assets="$mediaAssets" :selected="$desktop_media_id" title="Desktop image" context="desktop" /><x-admin.media-picker :assets="$mediaAssets" :selected="$mobile_media_id" title="Mobile image" context="mobile" /></div>
                </x-admin.cms.panel>
                <x-admin.cms.panel title="Schedule and ordering"><div class="grid gap-4 sm:grid-cols-3"><label class="text-sm font-semibold text-slate-700">Starts at<input type="datetime-local" wire:model.live="starts_at" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm"></label><label class="text-sm font-semibold text-slate-700">Ends at<input type="datetime-local" wire:model.live="ends_at" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm"></label><label class="text-sm font-semibold text-slate-700">Sort order<input type="number" min="0" wire:model.live="sort_order" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm"></label></div></x-admin.cms.panel>
            </div>
            <div class="space-y-5">
                <x-admin.cms.panel title="Publishing" description="Control when this banner can be shown publicly."><label class="text-sm font-semibold text-slate-700">Status<select wire:model.live="status" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm"><option value="draft">Draft</option><option value="scheduled">Scheduled</option><option value="published">Published</option><option value="archived">Archived</option></select></label><div class="mt-4 rounded-lg bg-slate-50 p-3 text-xs text-slate-500">Published banners respect their start and end timestamps and placement rules.</div></x-admin.cms.panel>
                <x-admin.cms.panel title="Live preview"><div class="overflow-hidden rounded-xl bg-slate-900 text-white"><div class="aspect-[16/9] bg-gradient-to-br from-blue-700 to-indigo-950 p-5">@if($title)<p class="max-w-[220px] text-xl font-extrabold">{{ $title }}</p>@else<p class="text-xl font-extrabold text-white/60">Banner headline</p>@endif@if($description)<p class="mt-2 max-w-[240px] text-xs text-white/75">{{ $description }}</p>@endif@if($cta_label)<span class="mt-4 inline-flex rounded-md bg-white px-3 py-2 text-xs font-bold text-slate-900">{{ $cta_label }}</span>@endif</div></div><p class="mt-3 text-xs text-slate-500">Preview is representative; final output uses the storefront placement component.</p></x-admin.cms.panel>
            </div>
        </div>
    </div>
</div>
