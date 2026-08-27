<div class="p-5 sm:p-8">
    <div class="mx-auto max-w-[1480px] space-y-6">
        <x-admin.cms.page-header
            eyebrow="StoreZ / Content"
            title="Content Management"
            description="Manage website pages, banners, menus and promotional content."
        >
            <a href="{{ url('/admin/content/pages/create') }}" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">+ Create New Page</a>
            <a href="{{ url('/admin/content/banners/create') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-blue-300 hover:text-blue-700">Upload Banner</a>
            <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700">Filters</button>
        </x-admin.cms.page-header>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            @foreach($stats as $label => $value)
                <x-admin.cms.stat-card wire:key="content-stat-{{ $label }}" :label="$label" :value="number_format($value)" :accent="$loop->iteration % 2 === 0 ? 'green' : 'blue'" trend="+12.4% vs last 7 days">
                    <x-ui.icon name="document-text" class="size-5" />
                </x-admin.cms.stat-card>
            @endforeach
        </div>

        <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_300px]">
            <div class="space-y-5">
                <x-admin.cms.panel>
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <x-admin.cms.tabs class="border-0">
                            <x-admin.cms.tab :active="true">All Content</x-admin.cms.tab>
                            <x-admin.cms.tab>Pages</x-admin.cms.tab>
                            <x-admin.cms.tab>Banners</x-admin.cms.tab>
                            <x-admin.cms.tab>Menus</x-admin.cms.tab>
                        </x-admin.cms.tabs>
                    </div>
                    <x-admin.cms.toolbar class="mt-4 border-0 bg-slate-50 shadow-none">
                        <div class="flex min-w-0 flex-1 items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2">
                            <x-ui.icon name="magnifying-glass" class="size-4 text-slate-400" />
                            <input type="search" placeholder="Search content by title, slug or keyword..." class="min-w-0 flex-1 bg-transparent text-sm outline-none placeholder:text-slate-400">
                        </div>
                        <select class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700"><option>All Types</option><option>Page</option><option>Banner</option><option>Menu</option></select>
                        <select class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700"><option>All Status</option><option>Published</option><option>Draft</option><option>Scheduled</option></select>
                        <button type="button" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700">More Filters</button>
                    </x-admin.cms.toolbar>
                </x-admin.cms.panel>

                <x-admin.cms.panel title="Recent content" description="The latest pages and content changes across your storefront.">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="border-b border-slate-200 text-[11px] uppercase tracking-wide text-slate-500"><tr><th class="px-3 py-3">Title</th><th class="px-3 py-3">Type</th><th class="px-3 py-3">Status</th><th class="px-3 py-3">Updated</th><th class="px-3 py-3 text-right">Actions</th></tr></thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($recentPages as $page)
                                    <tr wire:key="dashboard-page-{{ $page->id }}" class="hover:bg-slate-50"><td class="px-3 py-3"><p class="font-bold text-slate-900">{{ $page->title }}</p><p class="text-xs text-slate-500">/{{ $page->slug }}</p></td><td class="px-3 py-3 text-slate-600">Page</td><td class="px-3 py-3"><x-admin.cms.badge :tone="$page->status === 'published' ? 'success' : ($page->status === 'scheduled' ? 'warning' : 'neutral')">{{ ucfirst($page->status) }}</x-admin.cms.badge></td><td class="px-3 py-3 text-xs text-slate-500">{{ $page->updated_at?->format('M d, Y') }}</td><td class="px-3 py-3 text-right"><a href="{{ url('/admin/content/pages/'.$page->id.'/edit') }}" class="font-bold text-blue-600 hover:text-blue-700">Edit</a></td></tr>
                                @empty
                                    <tr><td colspan="5" class="px-3 py-10 text-center text-slate-500">No content has been created yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 text-right"><a href="{{ url('/admin/content/pages') }}" class="text-sm font-bold text-blue-600">View all content →</a></div>
                </x-admin.cms.panel>
            </div>

            <aside class="space-y-5">
                <x-admin.cms.panel title="Content categories" description="Manage the main CMS areas.">
                    <div class="space-y-3 text-sm">
                        @foreach([['Pages', url('/admin/content/pages'), $stats['Pages']], ['Banners', url('/admin/content/banners'), $stats['Banners']], ['Homepage Builder', url('/admin/content/homepage'), $stats['Homepage Sections']], ['Media Library', url('/admin/media'), ''] ] as [$label, $href, $count])
                            <a wire:key="content-category-{{ $label }}" href="{{ $href }}" class="flex items-center justify-between rounded-lg px-2 py-2 text-slate-700 hover:bg-blue-50 hover:text-blue-700"><span class="font-semibold">{{ $label }}</span><span class="text-xs font-bold text-slate-500">{{ $count }}</span></a>
                        @endforeach
                    </div>
                </x-admin.cms.panel>

                <x-admin.cms.panel title="Recent updates">
                    <div class="space-y-3 text-xs">
                        @forelse($recentBanners as $banner)
                            <div wire:key="dashboard-banner-{{ $banner->id }}" class="flex gap-2"><span class="mt-1 size-2 shrink-0 rounded-full bg-blue-600"></span><div><p class="font-bold text-slate-800">{{ $banner->name }}</p><p class="text-slate-500">Updated {{ $banner->updated_at?->diffForHumans() }}</p></div></div>
                        @empty
                            <p class="text-slate-500">No banner updates yet.</p>
                        @endforelse
                    </div>
                </x-admin.cms.panel>

                <x-admin.cms.panel title="Quick links">
                    <div class="grid gap-2 text-sm"><a href="{{ url('/admin/content/navigation') }}" class="rounded-lg bg-blue-50 px-3 py-2 font-bold text-blue-700">Navigation Manager →</a><a href="{{ url('/admin/content/redirects') }}" class="rounded-lg bg-slate-50 px-3 py-2 font-bold text-slate-700">Redirect Manager →</a><a href="{{ url('/admin/content/header') }}" class="rounded-lg bg-slate-50 px-3 py-2 font-bold text-slate-700">Header & Announcements →</a></div>
                </x-admin.cms.panel>
            </aside>
        </div>
    </div>
</div>
