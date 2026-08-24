<div class="min-h-[calc(100vh-72px)] bg-[#f7f9fc] px-5 py-7 text-[#17233d] sm:px-8">
    <div class="mx-auto max-w-[1480px]">
        <div class="mb-7 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="mb-2 text-xs font-semibold uppercase tracking-[.16em] text-[#9aa8bd]">Catalog / Inventory</p>
                <h1 class="text-[30px] font-extrabold tracking-[-.7px]">Products</h1>
                <p class="mt-1 text-sm text-[#718099]">Manage your catalog, pricing, and product visibility.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ url('/admin/catalog/products/import') }}" class="rounded-lg border border-[#dce4ef] bg-white px-4 py-2.5 text-sm font-semibold text-[#53627b] shadow-sm hover:border-[#1769e8] hover:text-[#1769e8]">Import</a>
                <a href="{{ url('/admin/catalog/products/export') }}" class="rounded-lg border border-[#dce4ef] bg-white px-4 py-2.5 text-sm font-semibold text-[#53627b] shadow-sm hover:border-[#1769e8] hover:text-[#1769e8]">Export</a>
                <a href="{{ url('/admin/catalog/products/create') }}" class="rounded-lg bg-[#1769e8] px-4 py-2.5 text-sm font-bold text-white shadow-[0_5px_12px_rgba(23,105,232,.2)]">Add product</a>
            </div>
        </div>

        <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach([['Products','All products',$rows->total(),'#e7f0ff','#1769e8'],['Published','Live in store',App\Models\Product::where('status','published')->count(),'#e7f8ef','#17a673'],['Draft','Needs attention',App\Models\Product::where('status','draft')->count(),'#fff6e5','#c27a00'],['Archived','Hidden from store',App\Models\Product::where('status','archived')->count(),'#f1f3f6','#718099']] as [$label,$caption,$value,$bg,$color])
                <div class="rounded-xl border border-[#e3e9f2] bg-white p-5 shadow-[0_3px_12px_rgba(30,55,90,.04)]"><div class="flex items-start justify-between"><div><p class="text-xs font-semibold text-[#718099]">{{ $label }}</p><p class="mt-2 text-[28px] font-extrabold tracking-[-.7px]">{{ $value }}</p><p class="mt-1 text-xs text-[#9aa8bd]">{{ $caption }}</p></div><span class="grid size-10 place-items-center rounded-full" style="background:{{ $bg }};color:{{ $color }}"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 4h12v16H6z"/><path d="M9 8h6M9 12h6M9 16h4"/></svg></span></div></div>
            @endforeach
        </div>

        <div class="mb-4 flex flex-wrap items-center gap-2 border-b border-[#e3e9f2]">
            @foreach([''=>'All products','published'=>'Published','draft'=>'Draft','archived'=>'Archived'] as $value=>$label)
                <button wire:click="$set('status', '{{ $value }}')" class="border-b-2 px-3 pb-3 text-sm font-semibold {{ $status === $value ? 'border-[#1769e8] text-[#1769e8]' : 'border-transparent text-[#8491a5] hover:text-[#53627b]' }}">{{ $label }}</button>
            @endforeach
        </div>

        <div class="mb-4 flex flex-col gap-3 rounded-xl border border-[#e3e9f2] bg-white p-3 shadow-[0_3px_12px_rgba(30,55,90,.04)] md:flex-row md:items-center">
            <x-ui.input wire:model.live.debounce.300ms="search" leftIcon="magnifying-glass" placeholder="Search products, SKU, or category..." class="min-w-0 flex-1 !rounded-lg !border-[#e0e6ef]" />
            <x-ui.select wire:model.live="perPage" placeholder="15" class="h-10 sm:w-24"><x-ui.select.option value="15">15</x-ui.select.option><x-ui.select.option value="30">30</x-ui.select.option><x-ui.select.option value="50">50</x-ui.select.option></x-ui.select>
            <button class="flex h-10 items-center justify-center gap-2 rounded-lg border border-[#e0e6ef] px-4 text-sm font-semibold text-[#53627b]"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h16M7 12h10M10 18h4"/></svg>Filters</button>
        </div>

        @if(count($selected))
            <div class="mb-4 flex flex-wrap items-center gap-2 rounded-xl border border-[#cfe0ff] bg-[#f1f6ff] p-3 text-sm text-[#1769e8]"><span class="mr-2 font-semibold">{{ count($selected) }} selected</span>@foreach(['publish'=>'Publish','draft'=>'Draft','archive'=>'Archive','feature'=>'Feature','unfeature'=>'Unfeature','delete'=>'Delete'] as $action=>$label)<button wire:click="bulk('{{ $action }}')" wire:confirm="Apply {{ strtolower($label) }} to selected products?" class="rounded-md border border-[#cfe0ff] bg-white px-3 py-1.5 font-semibold hover:bg-[#e7f0ff]">{{ $label }}</button>@endforeach</div>
        @endif

        <div class="overflow-hidden rounded-xl border border-[#e3e9f2] bg-white shadow-[0_3px_12px_rgba(30,55,90,.04)]">
            <div class="flex items-center justify-between border-b border-[#edf1f6] px-5 py-4"><div><h2 class="font-bold">Product list</h2><p class="mt-1 text-xs text-[#9aa8bd]">{{ $rows->total() }} products in your catalog</p></div><span class="rounded-full bg-[#e7f8ef] px-3 py-1 text-xs font-bold text-[#159669]">Catalog synced</span></div>
            <div class="overflow-x-auto"><table class="min-w-full text-left text-sm"><thead class="bg-[#fbfcfe] text-[11px] font-bold uppercase tracking-[.08em] text-[#9aa8bd]"><tr><th class="w-12 px-5 py-3"><input type="checkbox" wire:click="togglePageSelection" class="rounded border-[#cbd5e1] text-[#1769e8]"></th><th class="px-4 py-3">Product</th><th class="px-4 py-3">SKU</th><th class="px-4 py-3">Category</th><th class="px-4 py-3">Price</th><th class="px-4 py-3">Stock</th><th class="px-4 py-3">Status</th><th class="px-5 py-3"></th></tr></thead><tbody class="divide-y divide-[#edf1f6]">@forelse($rows as $row)<tr class="group hover:bg-[#fbfdff]"><td class="px-5 py-4"><input type="checkbox" wire:model="selected" value="{{ $row->id }}" class="rounded border-[#cbd5e1] text-[#1769e8]"></td><td class="px-4 py-4"><div class="flex items-center gap-3"><div class="grid size-10 shrink-0 place-items-center rounded-lg bg-[#f1f6ff] text-[#1769e8]"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="m4 7 8-4 8 4v10l-8 4-8-4V7Z"/><path d="m4 7 8 4 8-4M12 11v10"/></svg></div><div><p class="font-bold text-[#263550]">{{ $row->name }}</p><p class="mt-0.5 text-xs text-[#9aa8bd]">{{ $row->brand?->name ?? 'StoreZ catalog' }}</p></div></div></td><td class="px-4 py-4 text-xs font-medium text-[#718099]">{{ $row->defaultVariant?->sku ?? '—' }}</td><td class="px-4 py-4 text-[#53627b]">{{ $row->primaryCategory?->name ?? 'Uncategorized' }}</td><td class="px-4 py-4 font-semibold text-[#263550]">৳{{ number_format(($row->defaultVariant?->currentPriceMinor() ?? 0)/100,2) }}</td><td class="px-4 py-4"><span class="font-semibold {{ ($row->defaultVariant?->availableQuantity() ?? 0) < 5 ? 'text-[#e05252]' : 'text-[#53627b]' }}">{{ $row->defaultVariant?->availableQuantity() ?? 0 }}</span></td><td class="px-4 py-4"><span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ ($row->status?->value ?? $row->status) === 'published' ? 'bg-[#e7f8ef] text-[#159669]' : (($row->status?->value ?? $row->status) === 'draft' ? 'bg-[#fff6e5] text-[#b77700]' : 'bg-[#f1f3f6] text-[#718099]') }}">{{ ucfirst($row->status?->value ?? $row->status) }}</span></td><td class="px-5 py-4 text-right"><a class="font-semibold text-[#1769e8] opacity-0 transition group-hover:opacity-100" href="{{ url('/admin/catalog/products/'.$row->id.'/edit') }}">Edit</a><button wire:click="delete({{ $row->id }})" wire:confirm="Delete this product?" class="ml-3 font-semibold text-[#e05252] opacity-0 transition group-hover:opacity-100">Delete</button></td></tr>@empty<tr><td colspan="8" class="px-5 py-16 text-center"><div class="mx-auto grid size-12 place-items-center rounded-full bg-[#f1f6ff] text-[#1769e8]"><svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="m4 7 8-4 8 4v10l-8 4-8-4V7Z"/><path d="m4 7 8 4 8-4M12 11v10"/></svg></div><p class="mt-3 font-bold text-[#53627b]">No products found</p><p class="mt-1 text-sm text-[#9aa8bd]">Create your first product to start building the catalog.</p></td></tr>@endforelse</tbody></table></div>
            <div class="border-t border-[#edf1f6] px-5 py-4">{{ $rows->links() }}</div>
        </div>
    </div>
</div>
