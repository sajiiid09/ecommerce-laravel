<div class="p-5 sm:p-8">
    <div class="mx-auto max-w-[1480px]">
        <x-admin.cms.page-header eyebrow="StoreZ / Content / Banners" title="Banners" description="Create, schedule, and place promotional banners across the storefront.">
            <x-slot:actions><a href="{{ url('/admin/content/banners/create') }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-700"><x-ui.icon name="plus" class="size-4" /> Add banner</a></x-slot:actions>
        </x-admin.cms.page-header>

        <div class="mb-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($stats as $label => $value)
                <x-admin.cms.stat-card :label="$label" :value="number_format($value)" :accent="$loop->iteration === 2 ? 'green' : ($loop->iteration === 3 ? 'orange' : 'blue')"><x-ui.icon name="photo" class="size-5" /></x-admin.cms.stat-card>
            @endforeach
        </div>

        <x-admin.cms.tabs>
            <x-admin.cms.tab wire:click="$set('statusFilter', '')" :active="$statusFilter === ''">All banners</x-admin.cms.tab>
            <x-admin.cms.tab wire:click="$set('statusFilter', 'published')" :active="$statusFilter === 'published'">Published</x-admin.cms.tab>
            <x-admin.cms.tab wire:click="$set('statusFilter', 'scheduled')" :active="$statusFilter === 'scheduled'">Scheduled</x-admin.cms.tab>
            <x-admin.cms.tab wire:click="$set('statusFilter', 'draft')" :active="$statusFilter === 'draft'">Drafts</x-admin.cms.tab>
        </x-admin.cms.tabs>

        <x-admin.cms.toolbar>
            <div class="flex flex-1 flex-col gap-3 md:flex-row">
                <div class="relative min-w-0 flex-1"><x-ui.icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" /><input wire:model.live.debounce.300ms="search" type="search" placeholder="Search banners..." class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-3 text-sm outline-none focus:border-blue-400"></div>
                <select wire:model.live="placementFilter" class="rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm"><option value="">All placements</option><option value="hero">Hero</option><option value="homepage">Homepage</option><option value="category">Category</option><option value="offers">Offers</option></select>
            </div>
            <button wire:click="$set('search', '')" wire:loading.attr="disabled" class="rounded-lg px-3 py-2 text-sm font-bold text-slate-500 hover:bg-slate-50 disabled:opacity-60">Reset</button>
        </x-admin.cms.toolbar>

        <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_300px]">
            <x-admin.cms.panel>
                <div class="overflow-x-auto"><table class="min-w-full text-left text-sm"><thead class="border-b border-slate-100 text-[11px] uppercase tracking-wide text-slate-400"><tr><th class="px-4 py-3">Banner</th><th class="px-4 py-3">Placement</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Schedule</th><th class="px-4 py-3 text-right">Actions</th></tr></thead><tbody class="divide-y divide-slate-100">
                    @forelse($banners as $banner)
                        <tr wire:key="banner-{{ $banner->id }}" class="hover:bg-slate-50"><td class="px-4 py-4"><div class="flex items-center gap-3"><div class="grid size-10 place-items-center overflow-hidden rounded-lg bg-slate-100">@if($banner->desktopMedia)<img src="{{ $banner->desktopMedia->url() }}" alt="" class="size-full object-cover">@else<x-ui.icon name="photo" class="size-5 text-slate-400" />@endif</div><div><p class="font-bold text-slate-800">{{ $banner->name }}</p><p class="text-xs text-slate-500">{{ $banner->title ?: 'No headline set' }}</p></div></div></td><td class="px-4 py-4 text-slate-600">{{ ucfirst($banner->placement) }}</td><td class="px-4 py-4"><x-admin.cms.badge :tone="$banner->status === 'published' ? 'success' : ($banner->status === 'scheduled' ? 'warning' : 'neutral')">{{ ucfirst($banner->status) }}</x-admin.cms.badge></td><td class="px-4 py-4 text-xs text-slate-500">{{ $banner->starts_at?->format('M d, Y') ?? 'Always' }}{{ $banner->ends_at ? ' - '.$banner->ends_at->format('M d, Y') : '' }}</td><td class="px-4 py-4 text-right"><a href="{{ url('/admin/content/banners/'.$banner->id.'/edit') }}" class="font-bold text-blue-600 hover:text-blue-800">Edit</a><button wire:click="deleteBanner({{ $banner->id }})" wire:confirm="Delete this banner?" wire:loading.attr="disabled" wire:target="deleteBanner({{ $banner->id }})" class="ml-3 font-bold text-red-600 disabled:opacity-60">Delete</button></td></tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-14 text-center text-sm text-slate-500">No banners match your filters.</td></tr>
                    @endforelse
                </tbody></table></div>
                <div class="mt-4">{{ $banners->links() }}</div>
            </x-admin.cms.panel>
            <div class="space-y-5"><x-admin.cms.panel title="Placement guide" description="Keep banner content consistent across storefront surfaces."><div class="space-y-3 text-sm"><div class="rounded-lg bg-blue-50 p-3"><p class="font-bold text-blue-800">Hero</p><p class="mt-1 text-xs text-blue-700">Rotating primary homepage slides.</p></div><div class="rounded-lg bg-slate-50 p-3"><p class="font-bold text-slate-800">Homepage</p><p class="mt-1 text-xs text-slate-500">Promotional campaign cards below the hero.</p></div><div class="rounded-lg bg-amber-50 p-3"><p class="font-bold text-amber-800">Offers</p><p class="mt-1 text-xs text-amber-700">Time-sensitive promotional messaging.</p></div></div></x-admin.cms.panel><x-admin.cms.panel title="Upcoming schedule"><p class="text-sm text-slate-500">Scheduled banners will appear here with their publication window.</p></x-admin.cms.panel></div>
        </div>
    </div>
</div>
