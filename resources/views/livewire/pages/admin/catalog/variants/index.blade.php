<div class="p-5 sm:p-8">
    <div class="mx-auto max-w-[1480px]">
        <div class="mb-6">
            <p class="text-sm text-[#6b7280]">StoreZ / Catalog / Variants</p>
            <h1 class="mt-1 text-[28px] font-extrabold tracking-tight text-[#111827]">Variants</h1>
            <p class="mt-1 text-sm text-[#6b7280]">Manage sellable product combinations, SKU pricing and status across the catalog.</p>
        </div>

        <div class="mb-5 flex flex-wrap items-center gap-2">
            <button class="flex items-center gap-2 rounded-lg border border-[#e5e7eb] bg-white px-4 py-2.5 text-sm font-semibold text-[#374151] hover:bg-[#f9fafb]">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Export Variants
            </button>
            <button class="flex items-center gap-2 rounded-lg border border-[#e5e7eb] bg-white px-4 py-2.5 text-sm font-semibold text-[#374151] hover:bg-[#f9fafb]">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75"/></svg>
                Filters
            </button>
        </div>

        <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach([
                ['Total Variants', number_format($rows->total()), '#2563eb', 'M21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9'],
                ['Active Variants', $rows->where('is_active', true)->count(), '#10b981', 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                ['Out of Stock', '0', '#ef4444', 'M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636'],
                ['SKU Conflicts', '0', '#f97316', 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126Z'],
            ] as [$label, $value, $color, $path])
                <div class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold text-[#6b7280]">{{ $label }}</p>
                            <p class="mt-2 text-[26px] font-extrabold text-[#111827]">{{ $value }}</p>
                            <a href="#" class="mt-1 inline-block text-xs font-bold text-[#2563eb]">View all →</a>
                        </div>
                        <span style="background: {{ $color }}15; color: {{ $color }}" class="grid size-11 place-items-center rounded-full">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/></svg>
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mb-4 flex flex-wrap items-center gap-2 border-b border-[#e5e7eb]">
            @foreach(['All','Active','Inactive','Low Stock','Out of Stock'] as $tab)
                <button class="border-b-2 {{ $loop->first ? 'border-[#2563eb] text-[#2563eb]' : 'border-transparent text-[#9ca3af] hover:text-[#374151]' }} px-3 pb-3 text-sm font-semibold">{{ $tab }}</button>
            @endforeach
        </div>

        <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_280px]">
            <div>
                <div class="mb-4 flex flex-col gap-3 rounded-xl border border-[#e5e7eb] bg-white p-3 shadow-sm md:flex-row md:items-center">
                    <div class="flex min-w-0 flex-1 items-center gap-2 rounded-lg border border-[#e5e7eb] bg-[#f9fafb] px-3 py-2">
                        <svg class="size-4 shrink-0 text-[#9ca3af]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search product or SKU..." class="min-w-0 flex-1 bg-transparent text-sm outline-none placeholder:text-[#9ca3af]">
                    </div>
                    <x.ui.select wire:model.live="perPage" placeholder="10" class="h-10 w-24"><x.ui.select.option value="10">10</x.ui.select.option><x.ui.select.option value="25">25</x.ui.select.option></x.ui.select>
                </div>

                <div class="overflow-hidden rounded-xl border border-[#e5e7eb] bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="bg-[#f9fafb] text-[11px] font-semibold uppercase tracking-wider text-[#9ca3af]">
                                <tr>
                                    <th class="w-12 px-5 py-3"><input type="checkbox" class="rounded border-[#d1d5db] text-[#2563eb]"></th>
                                    <th class="px-4 py-3">Variant</th>
                                    <th class="px-4 py-3">Parent Product</th>
                                    <th class="px-4 py-3">SKU</th>
                                    <th class="px-4 py-3">Options</th>
                                    <th class="px-4 py-3">Price</th>
                                    <th class="px-4 py-3">Stock</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Default</th>
                                    <th class="px-5 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#f3f4f6]">
                                @forelse($rows as $row)
                                    <tr class="text-[#374151] hover:bg-[#f9fafb]">
                                        <td class="px-5 py-4"><input type="checkbox" class="rounded border-[#d1d5db] text-[#2563eb]"></td>
                                        <td class="px-4 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="grid size-10 shrink-0 place-items-center rounded-lg bg-[#f3f4f6]">
                                                    <svg class="size-5 text-[#9ca3af]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
                                                </div>
                                                <span class="font-bold text-[#111827]">{{ $row->product?->name ?? '—' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-[#6b7280]">{{ $row->product?->name ?? '—' }}</td>
                                        <td class="px-4 py-4 text-xs font-medium text-[#6b7280]">{{ $row->sku }}</td>
                                        <td class="px-4 py-4 text-sm text-[#374151]">{{ $row->optionValues ?? '—' }}</td>
                                        <td class="px-4 py-4 font-semibold text-[#111827]">৳{{ number_format(($row->currentPriceMinor() ?? 0)/100,2) }}</td>
                                        <td class="px-4 py-4"><span class="font-semibold {{ $row->availableQuantity() < 5 ? 'text-[#ef4444]' : 'text-[#374151]' }}">{{ $row->availableQuantity() }}</span></td>
                                        <td class="px-4 py-4">
                                            @php $isActive = $row->is_active ?? true; @endphp
                                            <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $isActive ? 'bg-[#dcfce7] text-[#16a34a]' : 'bg-[#f3f4f6] text-[#6b7280]' }}">{{ $isActive ? 'Active' : 'Inactive' }}</span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <svg class="size-5 {{ ($row->is_default ?? false) ? 'text-[#f59e0b]' : 'text-[#d1d5db]' }}" fill="currentColor" viewBox="0 0 24 24"><path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/></svg>
                                        </td>
                                        <td class="px-5 py-4">
                                            <div class="flex items-center gap-1">
                                                <button class="grid size-8 place-items-center rounded-lg text-[#6b7280] hover:bg-[#f3f4f6] hover:text-[#2563eb]"><svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg></button>
                                                <button class="grid size-8 place-items-center rounded-lg text-[#6b7280] hover:bg-[#f3f4f6] hover:text-[#2563eb]"><svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg></button>
                                                <button class="grid size-8 place-items-center rounded-lg text-[#6b7280] hover:bg-[#fef2f2] hover:text-[#ef4444]"><svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg></button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="10" class="px-5 py-12 text-center text-sm text-[#9ca3af]">No variants found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-[#f3f4f6] px-5 py-4">{{ $rows->links() }}</div>
                </div>
            </div>

            <div class="space-y-5">
                <div class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-[#111827]">Variant Summary</h3>
                    <p class="mt-1 text-xs text-[#9ca3af]">Variants by Product (Top 5)</p>
                    <div class="mt-4 space-y-3">
                        @foreach(['Wireless Headphones'=>9,'Redmi Note 13'=>6,'Orix Detergent'=>4,'Anker PowerCore'=>3,'Nivea Soft Cream'=>3] as $name=>$count)
                            <div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-[#374151]">{{ $name }}</span>
                                    <span class="font-bold text-[#111827]">{{ $count }}</span>
                                </div>
                                <div class="mt-1 h-1.5 rounded-full bg-[#f3f4f6]">
                                    <div class="h-1.5 rounded-full bg-[#2563eb]" style="width: {{ $count * 10 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold text-[#111827]">Low Stock Variants</h3>
                        <a href="#" class="text-xs font-bold text-[#2563eb]">View all</a>
                    </div>
                    <div class="mt-4 space-y-3">
                        @foreach(['Beige / Small'=>5,'128 GB / Blue'=>14,'256 GB / Green'=>7] as $variant=>$count)
                            <div class="flex items-center gap-3">
                                <div class="size-8 shrink-0 rounded bg-[#f3f4f6]"></div>
                                <div class="flex-1">
                                    <p class="text-xs font-bold text-[#111827]">{{ $variant }}</p>
                                </div>
                                <span class="rounded-full bg-[#fef3c7] px-2 py-0.5 text-[10px] font-bold text-[#d97706]">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
