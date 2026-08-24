<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $adminPageTitle = $title ?? match (true) {
            request()->is('admin/catalog/products/create') => 'Add Product',
            request()->is('admin/catalog/products/*/edit') => 'Edit Product',
            request()->is('admin/catalog/products/*/variants') => 'Options & Variants',
            request()->is('admin/catalog/products/import') => 'Import Products',
            request()->is('admin/catalog/products/export') => 'Export Products',
            request()->is('admin/catalog/products') => 'Products',
            request()->is('admin/catalog/categories') => 'Categories',
            request()->is('admin/catalog/brands') => 'Brands',
            request()->is('admin/catalog/tags') => 'Tags',
            request()->is('admin/catalog/attributes') => 'Attributes / Options',
            request()->is('admin/catalog/variants') => 'Variants',
            request()->is('admin/catalog/inventory/*/history') => 'Inventory History',
            request()->is('admin/catalog/inventory') => 'Inventory',
            request()->is('admin/media') => 'Media Library',
            default => 'Admin Dashboard',
        };
    @endphp
    <title>{{ $adminPageTitle }} · StoreZ Admin</title>
    @vite(['resources/css/app.css','resources/js/app.js']) @livewireStyles
</head>
<body class="min-h-screen bg-[#f7f9fc] text-[#14213d] antialiased">
<div x-data="{ sidebarOpen: false, catalogOpen: @js(request()->is('admin/catalog/categories*') || request()->is('admin/catalog/brands*') || request()->is('admin/catalog/tags*') || request()->is('admin/catalog/attributes*')), operationsOpen: @js(request()->is('admin/catalog/variants*') || request()->is('admin/catalog/inventory*') || request()->is('admin/media*')), storeOpen: true }" class="flex min-h-screen">
    <aside x-bind:style="sidebarOpen ? 'transform: translateX(0)' : ''" class="fixed inset-y-0 left-0 z-40 flex w-[248px] -translate-x-full flex-col border-r border-[#e8edf5] bg-white transition-transform duration-200 lg:translate-x-0">
        <div class="flex h-[72px] items-center border-b border-[#edf1f6] px-6"><a href="{{ url('/admin') }}" class="text-[27px] font-extrabold italic tracking-[-1.5px]"><span class="text-[#1769e8]">Store</span><span class="text-[#ef2c35]">Z</span></a><span class="ml-2 rounded bg-[#eef4ff] px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider text-[#1769e8]">Admin</span></div>
        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-5 text-[13px] font-medium">
            <a href="{{ url('/admin') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 {{ request()->is('admin')?'bg-[#1769e8] text-white shadow-[0_5px_12px_rgba(23,105,232,.22)]':'text-[#41516d] hover:bg-[#f3f7ff]' }}"><span class="grid size-6 place-items-center rounded-md bg-current/10"><x-ui.icon name="squares-2x2" class="size-4 !text-current" /></span><span>Dashboard</span></a>
            <a href="{{ url('/admin/catalog/products') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 {{ request()->is('admin/catalog/products*')?'bg-[#1769e8] text-white shadow-[0_5px_12px_rgba(23,105,232,.22)]':'text-[#41516d] hover:bg-[#f3f7ff]' }}"><span class="grid size-6 place-items-center rounded-md bg-current/10"><x-ui.icon name="cube" class="size-4 !text-current" /></span><span>Products</span></a>

            <div class="pt-4">
                <button type="button" x-on:click="catalogOpen = !catalogOpen" x-bind:aria-expanded="catalogOpen.toString()" class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-[10px] font-bold uppercase tracking-[.16em] text-[#9aa8bd] hover:bg-[#f8fafc] hover:text-[#53627b]"><span>Catalog</span><svg x-bind:class="catalogOpen ? 'rotate-180' : ''" class="size-4 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg></button>
                <div x-cloak x-show="catalogOpen" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="ml-3 mt-1 space-y-1 border-l border-[#e5eaf2] pl-2">
                    @foreach([['Categories','/admin/catalog/categories','folder'],['Brands','/admin/catalog/brands','building-storefront'],['Tags','/admin/catalog/tags','tag'],['Attributes / Options','/admin/catalog/attributes','adjustments-horizontal']] as [$label,$href,$icon])
                        <a href="{{ url($href) }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 {{ request()->is(ltrim($href,'/').'*')?'bg-[#eef4ff] text-[#1769e8]':'text-[#41516d] hover:bg-[#f3f7ff]' }}"><span class="grid size-6 place-items-center rounded-md bg-current/10"><x-ui.icon name="{{ $icon }}" class="size-4 !text-current" /></span><span>{{ $label }}</span></a>
                    @endforeach
                </div>
            </div>

            <div class="pt-3">
                <button type="button" x-on:click="operationsOpen = !operationsOpen" x-bind:aria-expanded="operationsOpen.toString()" class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-[10px] font-bold uppercase tracking-[.16em] text-[#9aa8bd] hover:bg-[#f8fafc] hover:text-[#53627b]"><span>Operations</span><svg x-bind:class="operationsOpen ? 'rotate-180' : ''" class="size-4 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg></button>
                <div x-cloak x-show="operationsOpen" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="ml-3 mt-1 space-y-1 border-l border-[#e5eaf2] pl-2">
                    @foreach([['Variants','/admin/catalog/variants','view-columns'],['Inventory','/admin/catalog/inventory','clipboard-document-list'],['Media Library','/admin/media','photo']] as [$label,$href,$icon])
                        <a href="{{ url($href) }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 {{ request()->is(ltrim($href,'/').'*')?'bg-[#eef4ff] text-[#1769e8]':'text-[#41516d] hover:bg-[#f3f7ff]' }}"><span class="grid size-6 place-items-center rounded-md bg-current/10"><x-ui.icon name="{{ $icon }}" class="size-4 !text-current" /></span><span>{{ $label }}</span></a>
                    @endforeach
                </div>
            </div>

            <div class="pt-3">
                <button type="button" x-on:click="storeOpen = !storeOpen" x-bind:aria-expanded="storeOpen.toString()" class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-[10px] font-bold uppercase tracking-[.16em] text-[#9aa8bd] hover:bg-[#f8fafc] hover:text-[#53627b]"><span>Store</span><svg x-bind:class="storeOpen ? 'rotate-180' : ''" class="size-4 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg></button>
                <div x-cloak x-show="storeOpen" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="ml-3 mt-1 border-l border-[#e5eaf2] pl-2"><a href="{{ url('/') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-[#41516d] hover:bg-[#f3f7ff]"><span class="grid size-6 place-items-center rounded-md bg-[#f1f4f8]"><x-ui.icon name="building-storefront" class="size-4 !text-[#53627b]" /></span><span>View storefront</span><x-ui.icon name="arrow-top-right-on-square" class="ml-auto size-3.5 !text-[#9aa8bd]" /></a></div>
            </div>
        </nav>
        <div class="m-3 rounded-xl border border-[#dce8ff] bg-[#f3f7ff] p-4"><p class="text-xs font-bold text-[#1769e8]">Need help?</p><p class="mt-1 text-[11px] leading-4 text-[#66758d]">Manage your store from one place.</p><a href="#" class="mt-3 inline-block text-[11px] font-bold text-[#1769e8]">Visit support &rarr;</a></div>
        <div class="border-t border-[#edf1f6] p-3"><form method="POST" action="{{ route('logout') }}">@csrf<button class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-[#65738a] hover:bg-[#fff5f5] hover:text-[#ef2c35]"><span class="grid size-6 place-items-center rounded-md bg-[#f1f4f8]"><x-ui.icon name="arrow-right-start-on-rectangle" class="size-4 !text-current" /></span><span>Sign out</span></button></form></div>
    </aside>
    <div x-cloak x-show="sidebarOpen" x-transition.opacity x-on:click="sidebarOpen = false" class="fixed inset-0 z-30 bg-[#14213d]/30 lg:hidden"></div>
    <main class="min-w-0 flex-1 lg:pl-[248px]">
        <header class="sticky top-0 z-20 flex h-[72px] items-center justify-between border-b border-[#e8edf5] bg-white/95 px-5 backdrop-blur sm:px-8"><div class="flex items-center gap-4"><button x-on:click="sidebarOpen = true" class="text-xl text-[#53627b] lg:hidden" aria-label="Open menu">Menu</button><h1 class="hidden text-xl font-bold text-[#17233d] sm:block">{{ $adminPageTitle }}</h1></div><div class="flex items-center gap-3"><div class="hidden h-10 w-[280px] items-center gap-2 rounded-lg border border-[#e0e6ef] px-3 text-xs text-[#9aa8bd] md:flex"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg><span>Search orders, customers, products...</span><kbd class="ml-auto rounded bg-[#f1f4f8] px-1.5 py-1 text-[10px]">Ctrl K</kbd></div><button aria-label="Notifications" class="relative grid size-10 place-items-center rounded-lg border border-[#e6ebf2] text-[#53627b]"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg><span class="absolute right-1 top-1 size-2 rounded-full bg-[#ef2c35]"></span></button><a href="{{ url('/admin/catalog/products/create') }}" class="hidden rounded-lg bg-[#1769e8] px-4 py-2.5 text-sm font-bold text-white shadow-[0_5px_12px_rgba(23,105,232,.2)] sm:block">Quick Add</a><div class="flex items-center gap-2 border-l border-[#edf1f6] pl-3"><div class="grid size-9 place-items-center rounded-full bg-[#dce8ff] text-sm font-bold text-[#1769e8]">{{ strtoupper(substr(auth()->user()->name ?? 'A',0,1)) }}</div><div class="hidden leading-tight sm:block"><p class="text-xs font-bold text-[#17233d]">{{ auth()->user()->name ?? 'Admin User' }}</p><p class="text-[10px] text-[#8b98ab]">Administrator</p></div></div></div></header>
        {{ $slot }}
    </main>
</div>
@livewireScriptConfig
</body>
</html>
