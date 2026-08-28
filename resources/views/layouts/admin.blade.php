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
            request()->is('admin/catalog/categories/import') => 'Import Categories',
            request()->is('admin/catalog/categories/export') => 'Export Categories',
            request()->is('admin/catalog/products') => 'Products',
            request()->is('admin/catalog/categories') => 'Categories',
            request()->is('admin/catalog/brands') => 'Brands',
            request()->is('admin/catalog/tags') => 'Tags',
            request()->is('admin/catalog/attributes') => 'Attributes',
            request()->is('admin/catalog/variants') => 'Variants',
            request()->is('admin/catalog/inventory/*/history') => 'Inventory History',
            request()->is('admin/catalog/inventory') => 'Inventory',
            request()->is('admin/media') => 'Media Library',
            request()->is('admin/content/announcements') => 'Announcements',
            request()->is('admin/customers') => 'Customers',
            request()->is('admin/coupons') => 'Coupons',
            request()->is('admin/profile') => 'Admin Profile',
            request()->is('admin/settings/general') => 'General Settings',
            request()->is('admin/settings/payments') => 'Payment Settings',
            default => 'Admin Dashboard',
        };
        $breadcrumbs = $breadcrumbs ?? [];
    @endphp
    <title>{{ $adminPageTitle }} · StoreZ Admin</title>
    @vite(['resources/css/app.css','resources/js/app.js']) @livewireStyles
    <style>
        [x-cloak] { display: none !important; }
        .admin-sidebar nav {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .admin-sidebar nav::-webkit-scrollbar {
            display: none;
            width: 0;
            height: 0;
        }
    </style>
</head>
<body class="min-h-screen bg-[#f5f7fa] text-[#1a1f36] antialiased">
<div x-data="{
    sidebarOpen: false,
    sidebarCollapsed: localStorage.getItem('admin-sidebar-collapsed') === 'true',
    toggleSidebar() {
        this.sidebarCollapsed = !this.sidebarCollapsed;
        localStorage.setItem('admin-sidebar-collapsed', this.sidebarCollapsed);
    },
}" class="flex min-h-screen">
    <aside x-bind:style="sidebarOpen ? 'transform: translateX(0)' : ''" x-bind:class="sidebarCollapsed ? 'lg:w-[76px]' : 'lg:w-[252px]'" class="admin-sidebar fixed inset-y-0 left-0 z-40 flex w-[252px] -translate-x-full flex-col bg-[#0f172a] transition-[transform,width] duration-200 lg:translate-x-0">
        <div class="admin-brand flex h-[64px] items-center justify-between gap-2 border-b border-white/10 px-5">
            <a href="{{ url('/admin') }}" class="flex min-w-0 items-center gap-1" title="StoreZ Admin">
                <span x-cloak x-show="!sidebarCollapsed" class="admin-brand-name text-[22px] font-extrabold italic tracking-tight text-white">Store<span class="text-[#ef4444]">Z</span></span>
                <span x-cloak x-show="sidebarCollapsed" class="admin-brand-name text-[22px] font-extrabold italic tracking-tight text-white">S</span>
            </a>
            <span x-bind:class="sidebarCollapsed ? 'lg:hidden' : ''" class="rounded bg-[#1e40af]/30 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider text-[#60a5fa]">Admin</span>
        </div>
        <nav class="flex-1 space-y-0.5 overflow-y-auto px-3 py-4 text-[13px] font-medium">
            @php
                $navItems = [
                    ['label' => 'Dashboard', 'href' => '/admin', 'icon' => 'home', 'match' => 'admin'],
                ];
                if (Route::has('admin.orders')) {
                    $navItems[] = ['label' => 'Orders', 'href' => route('admin.orders'), 'icon' => 'shopping-bag', 'match' => 'admin/orders*'];
                }
                if (Route::has('admin.customers')) {
                    $navItems[] = ['label' => 'Customers', 'href' => route('admin.customers'), 'icon' => 'users', 'match' => 'admin/customers*'];
                }
                if (Route::has('admin.coupons')) {
                    $navItems[] = ['label' => 'Coupons', 'href' => route('admin.coupons'), 'icon' => 'tag', 'match' => 'admin/coupons*'];
                }
                if (config('features.reviews') && Route::has('admin.reviews')) {
                    $navItems[] = ['label' => 'Reviews', 'href' => route('admin.reviews'), 'icon' => 'chat-bubble-left-right', 'match' => 'admin/reviews*'];
                }
                if (Route::has('admin.settings.general')) {
                    $navItems[] = ['label' => 'General', 'href' => route('admin.settings.general'), 'icon' => 'cog-6-tooth', 'match' => 'admin/settings/general*'];
                }
                if (Route::has('admin.settings.payments')) {
                    $navItems[] = ['label' => 'Payments', 'href' => route('admin.settings.payments'), 'icon' => 'credit-card', 'match' => 'admin/settings/payments*'];
                }
            @endphp
            @foreach($navItems as $item)
                @php
                    $isActive = request()->is($item['match']);
                    $href = str_starts_with($item['href'], 'http') ? $item['href'] : url($item['href']);
                @endphp
                <a href="{{ $href }}" title="{{ $item['label'] }}" x-bind:class="sidebarCollapsed ? 'lg:justify-center lg:gap-0 lg:px-2' : ''" class="admin-nav-link {{ $isActive ? 'is-active bg-[#1e40af] text-white' : 'text-[#94a3b8] hover:bg-white/5 hover:text-white' }} flex items-center gap-3 rounded-lg px-3 py-2.5">
                    <span class="grid size-5 place-items-center">
                        @if($item['icon'] === 'home')
                            <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                        @elseif($item['icon'] === 'shopping-bag')
                            <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                        @elseif($item['icon'] === 'cube')
                            <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
                        @elseif($item['icon'] === 'folder')
                            <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"/></svg>
                        @elseif($item['icon'] === 'users')
                            <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                        @elseif($item['icon'] === 'tag')
                            <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"/></svg>
                        @elseif($item['icon'] === 'credit-card')
                            <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/></svg>
                        @elseif($item['icon'] === 'document-text')
                            <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                        @elseif($item['icon'] === 'chat-bubble-left-right')
                            <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/></svg>
                        @elseif($item['icon'] === 'chart-bar')
                            <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                        @elseif($item['icon'] === 'cog-6-tooth')
                            <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                        @endif
                    </span>
                    <span x-bind:class="sidebarCollapsed ? 'lg:hidden' : ''">{{ $item['label'] }}</span>
                    @if(isset($item['badge']))
                        <span x-bind:class="sidebarCollapsed ? 'lg:hidden' : ''" class="ml-auto grid size-5 place-items-center rounded-full bg-[#ef4444] text-[10px] font-bold text-white">{{ $item['badge'] }}</span>
                    @endif
                </a>
                @if($loop->first)
                    @php $catalogActive = request()->is('admin/catalog*'); @endphp
                    <div x-data="{ open: {{ $catalogActive ? 'true' : 'false' }} }" class="rounded-lg">
                        <button type="button" x-on:click="open = !open" title="Catalog" x-bind:class="sidebarCollapsed ? 'lg:justify-center lg:gap-0 lg:px-2' : ''" class="admin-nav-link {{ $catalogActive ? 'bg-[#1e40af] text-white' : 'text-[#94a3b8] hover:bg-white/5 hover:text-white' }} flex w-full items-center gap-3 rounded-lg px-3 py-2.5">
                            <span class="grid size-5 place-items-center"><svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg></span>
                            <span x-bind:class="sidebarCollapsed ? 'lg:hidden' : ''">Catalog</span>
                            <svg x-bind:class="[open ? 'rotate-180' : '', sidebarCollapsed ? 'lg:hidden' : '']" class="ml-auto size-4 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <div x-cloak x-show="open" x-transition x-bind:class="sidebarCollapsed ? 'lg:hidden' : ''" class="mt-0.5 space-y-0.5 pl-4">
                            @foreach([
                                ['label' => 'Products', 'href' => '/admin/catalog/products', 'match' => 'admin/catalog/products*'],
                                ['label' => 'Categories', 'href' => '/admin/catalog/categories', 'match' => 'admin/catalog/categories*'],
                                ['label' => 'Brands', 'href' => '/admin/catalog/brands', 'match' => 'admin/catalog/brands*'],
                                ['label' => 'Tags', 'href' => '/admin/catalog/tags', 'match' => 'admin/catalog/tags*'],
                                ['label' => 'Attributes / Options', 'href' => '/admin/catalog/attributes', 'match' => 'admin/catalog/attributes*'],
                                ['label' => 'Variants', 'href' => '/admin/catalog/variants', 'match' => 'admin/catalog/variants*'],
                                ['label' => 'Inventory', 'href' => '/admin/catalog/inventory', 'match' => 'admin/catalog/inventory*'],
                            ] as $catalogItem)
                                <a href="{{ url($catalogItem['href']) }}" class="flex items-center rounded-lg px-3 py-2 text-[12px] {{ request()->is($catalogItem['match']) ? 'bg-[#1e40af] font-semibold text-white' : 'text-[#94a3b8] hover:bg-white/5 hover:text-white' }}">{{ $catalogItem['label'] }}</a>
                            @endforeach
                        </div>
                    </div>
                    @php $contentActive = request()->is('admin/content*'); @endphp
                    <div x-data="{ open: {{ $contentActive ? 'true' : 'false' }} }" class="mt-1 rounded-lg">
                        <button type="button" x-on:click="open = !open" title="Content" x-bind:class="sidebarCollapsed ? 'lg:justify-center lg:gap-0 lg:px-2' : ''" class="admin-nav-link {{ $contentActive ? 'bg-[#1e40af] text-white' : 'text-[#94a3b8] hover:bg-white/5 hover:text-white' }} flex w-full items-center gap-3 rounded-lg px-3 py-2.5">
                            <span class="grid size-5 place-items-center"><svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3.75h7.5L18.75 8.25v12A2.25 2.25 0 0 1 16.5 22.5h-9A2.25 2.25 0 0 1 5.25 20.25v-15A1.5 1.5 0 0 1 6.75 3.75Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M14.25 3.75v4.5h4.5M8.25 12h7.5m-7.5 3h7.5m-7.5 3h4.5"/></svg></span>
                            <span x-bind:class="sidebarCollapsed ? 'lg:hidden' : ''">Content</span><svg x-bind:class="[open ? 'rotate-180' : '', sidebarCollapsed ? 'lg:hidden' : '']" class="ml-auto size-4 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <div x-cloak x-show="open" x-transition x-bind:class="sidebarCollapsed ? 'lg:hidden' : ''" class="mt-0.5 space-y-0.5 pl-4">
                            @foreach([
                                ['label'=>'Pages','href'=>'/admin/content/pages','match'=>'admin/content/pages*'],
                                ['label'=>'Homepage Builder','href'=>'/admin/content/homepage','match'=>'admin/content/homepage*'],
                                ['label'=>'Banners','href'=>'/admin/content/banners','match'=>'admin/content/banners*'],
                                ['label'=>'Navigation','href'=>'/admin/content/navigation','match'=>'admin/content/navigation*'],
                                ['label'=>'Header','href'=>'/admin/content/header','match'=>'admin/content/header*'],
                                ['label'=>'Announcements','href'=>'/admin/content/announcements','match'=>'admin/content/announcements*'],
                                ['label'=>'Footer','href'=>'/admin/content/footer','match'=>'admin/content/footer*'],
                                ['label'=>'Media Library','href'=>'/admin/media','match'=>'admin/media*'],
                                ['label'=>'Redirects','href'=>'/admin/content/redirects','match'=>'admin/content/redirects*'],
                            ] as $contentItem)
                                <a href="{{ url($contentItem['href']) }}" class="flex items-center rounded-lg px-3 py-2 text-[12px] {{ request()->is($contentItem['match']) ? 'bg-[#1e40af] font-semibold text-white' : 'text-[#94a3b8] hover:bg-white/5 hover:text-white' }}">{{ $contentItem['label'] }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </nav>
        <div class="admin-footer border-t border-white/10 p-3">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button title="Sign out" x-bind:class="sidebarCollapsed ? 'lg:justify-center lg:gap-0 lg:px-2' : ''" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-[13px] font-medium text-[#94a3b8] hover:bg-white/5 hover:text-white">
                    <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15"/></svg>
                    <span x-bind:class="sidebarCollapsed ? 'lg:hidden' : ''">Sign out</span>
                </button>
            </form>
        </div>
    </aside>
    <div x-cloak x-show="sidebarOpen" x-transition.opacity x-on:click="sidebarOpen = false" class="fixed inset-0 z-30 bg-black/50 lg:hidden"></div>
    <main x-bind:class="sidebarCollapsed ? 'lg:pl-[76px]' : 'lg:pl-[252px]'" class="min-w-0 flex-1">
        <header class="sticky top-0 z-20 flex h-[64px] items-center justify-between border-b border-[#e5e7eb] bg-white px-5 sm:px-6">
            <div class="flex items-center gap-4">
                <button type="button" x-on:click="toggleSidebar()" x-bind:aria-label="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'" x-bind:title="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'" class="hidden size-8 items-center justify-center rounded-lg text-[#374151] hover:bg-[#f3f4f6] lg:inline-flex">
                    <x-ui.icon name="ps:sidebar-simple" variant="regular" data-icon="sidebar-simple" x-bind:class="sidebarCollapsed ? 'rotate-180' : ''" class="size-5 transition-transform" aria-hidden="true" />
                </button>
                <button x-on:click="sidebarOpen = true" class="text-xl text-[#374151] lg:hidden" aria-label="Open menu">
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                </button>
            </div>
            <div class="flex shrink-0 items-center gap-3 border-l border-[#e5e7eb] pl-3">
                <x-ui.dropdown position="bottom-end">
                    <x-slot:button>
                        <button
                            type="button"
                            class="!inline-flex !flex-row !items-center !justify-center !gap-2.5 !whitespace-nowrap rounded-lg px-2 py-1.5 text-left hover:bg-[#f3f4f6]"
                            aria-label="Open administrator menu"
                        >
                            <span class="grid size-9 place-items-center rounded-full bg-[#dbeafe] text-sm font-bold text-[#2563eb]">
                                {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                            </span>
                            <span class="hidden min-w-0 leading-tight sm:block">
                                <span class="block text-xs font-bold text-[#111827]">{{ auth()->user()->name ?? 'Admin User' }}</span>
                                <span class="block text-[10px] text-[#9ca3af]">Administrator</span>
                            </span>
                            <x-ui.icon name="chevron-down" class="size-4 text-[#9ca3af]" aria-hidden="true" />
                        </button>
                    </x-slot:button>
                    <x-slot:menu class="w-64">
                        <div class="col-span-full border-b border-neutral-200 px-3 py-2 dark:border-neutral-800">
                            <p class="truncate text-xs font-bold text-neutral-900 dark:text-white">{{ auth()->user()->email }}</p>
                            <p class="mt-0.5 text-[11px] text-neutral-500">Administrator account</p>
                        </div>
                        <x-ui.dropdown.item as="a" href="{{ route('admin.profile') }}" icon="user-circle">
                            Profile
                        </x-ui.dropdown.item>
                        <x-ui.dropdown.separator />
                        <form method="POST" action="{{ route('logout') }}" class="col-span-full">
                            @csrf
                            <x-ui.dropdown.item as="button" type="submit" icon="arrow-right-on-rectangle">
                                Sign out
                            </x-ui.dropdown.item>
                        </form>
                    </x-slot:menu>
                </x-ui.dropdown>
            </div>
        </header>
        {{ $slot }}
    </main>
</div>
<x-ui.toast position="top-right" />
@livewireScriptConfig
</body>
</html>
