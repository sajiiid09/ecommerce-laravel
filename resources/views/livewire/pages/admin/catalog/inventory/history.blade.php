<div class="p-5 sm:p-8">
    <div class="mx-auto max-w-[1480px]">
        <div class="mb-6">
            <p class="text-sm text-[#6b7280]">StoreZ / Catalog / Inventory / History</p>
            <h1 class="mt-1 text-[28px] font-extrabold tracking-tight text-[#111827]">Inventory History</h1>
            <p class="mt-1 text-sm text-[#6b7280]">Review stock changes for this product variant.</p>
        </div>

        <div class="mb-5 flex flex-wrap items-center gap-2">
            <button class="rounded-lg bg-[#2563eb] px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#1d4ed8]">Adjust Stock</button>
            <button class="flex items-center gap-2 rounded-lg border border-[#e5e7eb] bg-white px-4 py-2.5 text-sm font-semibold text-[#374151] hover:bg-[#f9fafb]">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Export History
            </button>
        </div>

        <div class="mb-6 rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="grid size-14 place-items-center rounded-xl bg-[#f3f4f6]">
                    <svg class="size-7 text-[#9ca3af]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
                </div>
                <div>
                    <p class="text-lg font-bold text-[#111827]">{{ $variant->product?->name ?? 'Product' }}</p>
                    <p class="text-xs text-[#9ca3af]">{{ $variant->sku }}</p>
                </div>
                <div class="ml-auto grid grid-cols-3 gap-6 text-center">
                    <div>
                        <p class="text-xs text-[#6b7280]">Current On Hand</p>
                        <p class="text-xl font-extrabold text-[#111827]">{{ $variant->inventory?->quantity_on_hand ?? 0 }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-[#6b7280]">Reserved</p>
                        <p class="text-xl font-extrabold text-[#f97316]">{{ $variant->inventory?->quantity_reserved ?? 0 }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-[#6b7280]">Available</p>
                        <p class="text-xl font-extrabold text-[#10b981]">{{ $variant->inventory?->availableQuantity() ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4 flex flex-col gap-3 rounded-xl border border-[#e5e7eb] bg-white p-3 shadow-sm md:flex-row md:items-center">
            <div class="flex min-w-0 flex-1 items-center gap-2 rounded-lg border border-[#e5e7eb] bg-[#f9fafb] px-3 py-2">
                <svg class="size-4 shrink-0 text-[#9ca3af]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                <input type="text" placeholder="Search reference..." class="min-w-0 flex-1 bg-transparent text-sm outline-none placeholder:text-[#9ca3af]">
            </div>
            <button class="text-sm font-semibold text-[#6b7280] hover:text-[#374151]">Reset</button>
        </div>

        <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_280px]">
            <div class="overflow-hidden rounded-xl border border-[#e5e7eb] bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-[#f9fafb] text-[11px] font-semibold uppercase tracking-wider text-[#9ca3af]">
                            <tr>
                                <th class="px-4 py-3">Date & Time</th>
                                <th class="px-4 py-3">Type</th>
                                <th class="px-4 py-3">Change</th>
                                <th class="px-4 py-3">Before</th>
                                <th class="px-4 py-3">After</th>
                                <th class="px-4 py-3">Reference</th>
                                <th class="px-4 py-3">Created By</th>
                                <th class="px-4 py-3">Note</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#f3f4f6]">
                            @forelse($rows ?? [] as $row)
                                @php
                                    $typeColors = ['restock'=>'bg-[#dcfce7] text-[#16a34a]','sale'=>'bg-[#fef2f2] text-[#ef4444]','return'=>'bg-[#dbeafe] text-[#2563eb]','adjustment'=>'bg-[#fef3c7] text-[#d97706]','reservation'=>'bg-[#f3e8ff] text-[#8b5cf6]','release'=>'bg-[#e0f2fe] text-[#0284c7]','correction'=>'bg-[#f3f4f6] text-[#6b7280]'];
                                    $typeIcons = ['restock'=>'M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z','sale'=>'M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75-9.75a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z'];
                                @endphp
                                <tr class="text-[#374151] hover:bg-[#f9fafb]">
                                    <td class="px-4 py-3 text-xs">{{ $row->created_at->format('d M Y') }}<br>{{ $row->created_at->format('h:i A') }}</td>
                                    <td class="px-4 py-3"><span class="rounded-full {{ $typeColors[$row->type->value ?? $row->type] ?? 'bg-[#f3f4f6] text-[#6b7280]' }} px-2.5 py-1 text-[11px] font-bold">{{ ucfirst($row->type->value ?? $row->type) }}</span></td>
                                    <td class="px-4 py-3 font-semibold {{ $row->quantity_delta > 0 ? 'text-[#16a34a]' : 'text-[#ef4444]' }}">{{ $row->quantity_delta > 0 ? '+' : '' }}{{ $row->quantity_delta }}</td>
                                    <td class="px-4 py-3 text-[#6b7280]">{{ $row->quantity_before }}</td>
                                    <td class="px-4 py-3 font-semibold text-[#111827]">{{ $row->quantity_after }}</td>
                                    <td class="px-4 py-3 text-xs text-[#2563eb]">{{ $row->reference ?? 'Manual' }}</td>
                                    <td class="px-4 py-3 text-xs">{{ $row->creator?->name ?? 'System' }}</td>
                                    <td class="px-4 py-3 text-xs text-[#6b7280]">{{ $row->note ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="px-5 py-12 text-center text-sm text-[#9ca3af]">No history records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-[#f3f4f6] px-5 py-4">{{ $rows?->links() }}</div>
            </div>

            <div class="space-y-5">
                <div class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-[#111827]">Inventory Summary</h3>
                    <div class="mt-4 space-y-2">
                        @foreach(['Opening Stock'=>0,'Stock In'=>'356','Stock Out'=>'228','Net Change'=>'+128'] as $label=>$val)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-[#6b7280]">{{ $label }}</span>
                                <span class="font-bold {{ str_starts_with($val, '+') ? 'text-[#16a34a]' : ($val === '0' ? 'text-[#111827]' : 'text-[#ef4444]') }}">{{ $val }}</span>
                            </div>
                        @endforeach
                        <div class="border-t border-[#f3f4f6] pt-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-bold text-[#111827]">Current Stock (On Hand)</span>
                                <span class="font-extrabold text-[#111827]">{{ $variant->inventory?->quantity_on_hand ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-[#111827]">Movement Types</h3>
                    <div class="mt-4 space-y-3">
                        @foreach(['Restock'=>'Added stock to inventory','Sale'=>'Stock sold or used in order','Return'=>'Returned by customer','Adjustment'=>'Manual stock adjustment'] as $type=>$desc)
                            <div class="flex items-start gap-2">
                                <span class="mt-0.5 size-2 rounded-full bg-[#9ca3af]"></span>
                                <div>
                                    <p class="text-xs font-bold text-[#111827]">{{ $type }}</p>
                                    <p class="text-[10px] text-[#9ca3af]">{{ $desc }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
