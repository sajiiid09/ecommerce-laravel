<div class="p-5 sm:p-8">
    <div class="mx-auto max-w-[1480px] space-y-6">
        <x-admin.cms.page-header eyebrow="StoreZ / Content / Pages" title="Pages" description="Create and publish storefront content pages.">
            <a href="{{ url('/admin/content/pages/create') }}" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-700">+ Create New Page</a>
            <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700">Filters</button>
        </x-admin.cms.page-header>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-admin.cms.stat-card label="Total Pages" :value="number_format($pageStats['total'])" accent="blue" trend="+12.4% vs last 7 days"><x-ui.icon name="document-text" class="size-5" /></x-admin.cms.stat-card>
            <x-admin.cms.stat-card label="Published Pages" :value="number_format($pageStats['published'])" accent="green" trend="+8.7% vs last 7 days"><x-ui.icon name="check-circle" class="size-5" /></x-admin.cms.stat-card>
            <x-admin.cms.stat-card label="Draft Pages" :value="number_format($pageStats['draft'])" accent="orange" trend="Needs review"><x-ui.icon name="document" class="size-5" /></x-admin.cms.stat-card>
            <x-admin.cms.stat-card label="Scheduled Pages" :value="number_format($pageStats['scheduled'])" accent="pink" trend="Upcoming publishing"><x-ui.icon name="clock" class="size-5" /></x-admin.cms.stat-card>
        </div>

        <x-admin.cms.panel>
            <x-admin.cms.tabs>
                <x-admin.cms.tab wire:click="$set('status', '')" :active="$status === ''">All Pages</x-admin.cms.tab>
                <x-admin.cms.tab wire:click="$set('status', 'published')" :active="$status === 'published'">Published</x-admin.cms.tab>
                <x-admin.cms.tab wire:click="$set('status', 'draft')" :active="$status === 'draft'">Draft</x-admin.cms.tab>
                <x-admin.cms.tab wire:click="$set('status', 'scheduled')" :active="$status === 'scheduled'">Scheduled</x-admin.cms.tab>
                <x-admin.cms.tab wire:click="$set('status', 'archived')" :active="$status === 'archived'">Archived</x-admin.cms.tab>
            </x-admin.cms.tabs>
            <x-admin.cms.toolbar class="mt-4 border-0 bg-slate-50 shadow-none">
                <div class="flex min-w-0 flex-1 items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2"><x-ui.icon name="magnifying-glass" class="size-4 text-slate-400" /><input wire:model.live.debounce.300ms="search" placeholder="Search content by title, slug or keyword..." class="min-w-0 flex-1 bg-transparent text-sm outline-none placeholder:text-slate-400"></div>
                <select class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700"><option>All Types</option><option>Standard</option><option>Landing</option><option>Legal</option></select>
                <button type="button" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700">More Filters</button>
                <button type="button" wire:click="$set('search', '')" class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-500 hover:bg-white hover:text-slate-900">Reset</button>
            </x-admin.cms.toolbar>
        </x-admin.cms.panel>

        <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_300px]">
            <x-admin.cms.panel>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="border-b border-slate-200 text-[11px] uppercase tracking-wide text-slate-500"><tr><th class="w-10 px-3 py-3"><input type="checkbox" class="rounded border-slate-300 text-blue-600"></th><th class="px-3 py-3">Title</th><th class="px-3 py-3">Type</th><th class="px-3 py-3">Location</th><th class="px-3 py-3">Status</th><th class="px-3 py-3">Last Updated</th><th class="px-3 py-3 text-right">Actions</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($pages as $page)
                                <tr wire:key="page-{{ $page->id }}" class="hover:bg-slate-50"><td class="px-3 py-4"><input type="checkbox" class="rounded border-slate-300 text-blue-600"></td><td class="px-3 py-4"><p class="font-bold text-slate-900">{{ $page->title }}</p><p class="text-xs text-slate-500">/{{ $page->slug }}</p></td><td class="px-3 py-4 text-slate-600">{{ ucfirst($page->page_type) }}</td><td class="px-3 py-4 text-slate-500">{{ $page->show_in_navigation ? 'Navigation' : 'Unlisted' }}</td><td class="px-3 py-4"><x-admin.cms.badge :tone="$page->status === 'published' ? 'success' : ($page->status === 'scheduled' ? 'warning' : 'neutral')">{{ ucfirst($page->status) }}</x-admin.cms.badge></td><td class="px-3 py-4 text-xs text-slate-500">{{ $page->updated_at?->format('M d, Y') }}<br>{{ $page->updated_at?->format('h:i A') }}</td><td class="px-3 py-4 text-right"><div class="flex justify-end gap-3"><a href="{{ URL::signedRoute('admin.content.pages.preview', ['page' => $page->id]) }}" target="_blank" class="text-slate-500 hover:text-blue-600" aria-label="Preview {{ $page->title }}"><x-ui.icon name="eye" class="size-4" /></a><a href="{{ url('/admin/content/pages/'.$page->id.'/edit') }}" class="font-bold text-blue-600 hover:text-blue-700">Edit</a><button wire:click="deletePage({{ $page->id }})" wire:confirm="Delete this page?" wire:loading.attr="disabled" class="font-bold text-red-600 disabled:opacity-60">Delete</button></div></td></tr>
                            @empty
                                <tr><td colspan="7" class="px-3 py-12 text-center text-slate-500">No pages found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-500"><span>Showing {{ $pages->firstItem() ?? 0 }} to {{ $pages->lastItem() ?? 0 }} of {{ $pages->total() }} pages</span>{{ $pages->links() }}</div>
            </x-admin.cms.panel>

            <aside class="space-y-5">
                <x-admin.cms.panel title="Content categories" description="Pages and promotional content by type."><div class="space-y-3 text-sm"><div class="flex justify-between"><span>Pages</span><strong>{{ number_format($pageStats['total']) }}</strong></div><div class="flex justify-between"><span>Published</span><strong>{{ number_format($pageStats['published']) }}</strong></div><div class="flex justify-between"><span>Drafts</span><strong>{{ number_format($pageStats['draft']) }}</strong></div><div class="flex justify-between"><span>Scheduled</span><strong>{{ number_format($pageStats['scheduled']) }}</strong></div></div></x-admin.cms.panel>
                <x-admin.cms.panel title="Quick links"><div class="space-y-2 text-sm"><a href="{{ url('/admin/content/homepage') }}" class="block rounded-lg bg-blue-50 px-3 py-2 font-bold text-blue-700">Homepage Builder →</a><a href="{{ url('/admin/content/banners') }}" class="block rounded-lg bg-slate-50 px-3 py-2 font-bold text-slate-700">Manage Banners →</a><a href="{{ url('/admin/media') }}" class="block rounded-lg bg-slate-50 px-3 py-2 font-bold text-slate-700">Open Media Library →</a></div></x-admin.cms.panel>
            </aside>
        </div>
    </div>
</div>
