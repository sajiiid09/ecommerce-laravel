<div class="p-5 sm:p-8">
    <div class="mx-auto max-w-[1480px]">
        <div class="mb-6">
            <p class="text-sm text-[#6b7280]">StoreZ / Catalog / Products / Export</p>
            <h1 class="mt-1 text-[28px] font-extrabold tracking-tight text-[#111827]">Export Products</h1>
            <p class="mt-1 text-sm text-[#6b7280]">Choose which catalog data to export.</p>
        </div>

        <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_280px]">
            <div class="space-y-5">
                {{-- 1. Export Scope --}}
                <div class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-[#111827]">1. Export Scope</h3>
                    <div class="mt-4 space-y-3">
                        <label class="flex items-center gap-3 rounded-lg border border-[#e5e7eb] p-3 hover:bg-[#f9fafb]"><input type="radio" checked name="scope" class="text-[#2563eb]"><div><p class="text-sm font-bold text-[#111827]">All Products</p><p class="text-xs text-[#6b7280]">Export all products from the catalog</p></div></label>
                        <label class="flex items-center gap-3 rounded-lg border border-[#e5e7eb] p-3 hover:bg-[#f9fafb]"><input type="radio" name="scope" class="text-[#2563eb]"><div class="flex-1"><p class="text-sm font-bold text-[#111827]">Current Filtered Results</p><p class="text-xs text-[#6b7280]">Export products based on the current filters</p></div><span class="rounded-full bg-[#f3f4f6] px-2 py-0.5 text-[10px] font-bold text-[#6b7280]">2,458 products</span></label>
                        <label class="flex items-center gap-3 rounded-lg border border-[#e5e7eb] p-3 hover:bg-[#f9fafb]"><input type="radio" name="scope" class="text-[#2563eb]"><div class="flex-1"><p class="text-sm font-bold text-[#111827]">Selected Products</p><p class="text-xs text-[#6b7280]">Export only the selected products</p></div><span class="rounded-full bg-[#f3f4f6] px-2 py-0.5 text-[10px] font-bold text-[#6b7280]">0 selected</span></label>
                    </div>
                </div>

                {{-- 2. Product Status --}}
                <div class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-[#111827]">2. Product Status</h3>
                    <div class="mt-4 flex gap-6">
                        @foreach(['All','Published','Draft','Archived'] as $opt)
                            <label class="flex items-center gap-2 text-sm text-[#374151]"><input type="radio" {{ $opt==='All'?'checked':'' }} name="status" class="text-[#2563eb]"> {{ $opt }}</label>
                        @endforeach
                    </div>
                </div>

                {{-- 3. Fields to Include --}}
                <div class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold text-[#111827]">3. Fields to Include</h3>
                        <div class="flex gap-2"><button class="text-xs font-bold text-[#2563eb]">Select All</button><span class="text-[#d1d5db]">|</span><button class="text-xs font-bold text-[#6b7280]">Clear All</button></div>
                    </div>
                    <div class="mt-4 grid grid-cols-4 gap-3">
                        @foreach(['Product ID','Name','Slug','Product Type','Status','Visibility','Brand','Primary Category','Tags','SKU','Regular Price','Sale Price','Cost Price','Stock','Featured','Created At','Updated At'] as $field)
                            <label class="flex items-center gap-2 text-sm text-[#374151]"><input type="checkbox" checked class="rounded border-[#d1d5db] text-[#2563eb]"> {{ $field }}</label>
                        @endforeach
                    </div>
                </div>

                {{-- 4. Variant Data --}}
                <div class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold text-[#111827]">4. Variant Data</h3>
                        <button class="relative inline-flex h-6 w-11 items-center rounded-full bg-[#2563eb]"><span class="inline-block size-4 translate-x-6 rounded-full bg-white transition"></span></button>
                    </div>
                    <p class="mt-1 text-xs text-[#6b7280]">Include Variant Details</p>
                    <div class="mt-4 grid grid-cols-5 gap-3">
                        @foreach(['Variant SKU','Options','Variant Price','Variant Stock','Barcode'] as $field)
                            <label class="flex items-center gap-2 text-sm text-[#374151]"><input type="checkbox" checked class="rounded border-[#d1d5db] text-[#2563eb]"> {{ $field }}</label>
                        @endforeach
                    </div>
                </div>

                {{-- 5. File Format --}}
                <div class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-[#111827]">5. File Format</h3>
                    <div class="mt-4 flex items-center gap-4">
                        <div class="flex-1 rounded-lg border border-[#e5e7eb] p-4">
                            <p class="text-sm font-bold text-[#111827]">CSV (Comma Separated Values)</p>
                            <p class="text-xs text-[#6b7280]">Best for spreadsheets and bulk data operations</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs text-[#6b7280]">Estimated records</p>
                            <p class="text-2xl font-extrabold text-[#111827]">2,458</p>
                        </div>
                    </div>
                    <div class="mt-4 flex gap-3">
                        <a href="{{ url('/admin/catalog/products') }}" class="rounded-lg border border-[#e5e7eb] px-4 py-2.5 text-sm font-semibold text-[#374151] hover:bg-[#f9fafb]">Cancel</a>
                        <button wire:click="export" class="flex items-center gap-2 rounded-lg bg-[#2563eb] px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#1d4ed8]">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            Export CSV
                        </button>
                    </div>
                    @if(session('status'))
                        <div class="mt-4 rounded-lg bg-[#dcfce7] p-3 text-sm font-semibold text-[#16a34a]">{{ session('status') }}</div>
                    @endif
                </div>
            </div>

            <div class="space-y-5">
                <div class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-[#111827]">
                        <svg class="size-5 text-[#2563eb]" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                        Export Notes
                    </h3>
                    <div class="mt-3 space-y-3">
                        @foreach([['Prices exported as formatted BDT values','All prices include currency formatting and thousands separators.'],['Product media is not included as files','Images and files are referenced by URL, not exported as attachments.'],['Payment/customer data is never included','Export contains only catalog-related data for your products.']] as [$title,$desc])
                            <div class="rounded-lg bg-[#f9fafb] p-3">
                                <p class="text-xs font-bold text-[#111827]">{{ $title }}</p>
                                <p class="text-[10px] text-[#6b7280]">{{ $desc }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold text-[#111827]">Recent Exports</h3>
                        <a href="#" class="text-xs font-bold text-[#2563eb]">View All</a>
                    </div>
                    <div class="mt-4 space-y-3">
                        @foreach([['Products Export','CSV','May 24, 2026 · 2,458 rows'],['Inventory Export','CSV','May 23, 2026 · 3,108 rows'],['Low Stock Report','CSV','May 21, 2026 · 256 rows']] as [$name,$fmt,$detail])
                            <div class="flex items-center gap-3">
                                <div class="grid size-8 place-items-center rounded-lg bg-[#f3f4f6]"><svg class="size-4 text-[#6b7280]" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg></div>
                                <div class="flex-1">
                                    <p class="text-xs font-bold text-[#111827]">{{ $name }} <span class="rounded bg-[#dcfce7] px-1.5 py-0.5 text-[9px] font-bold text-[#16a34a]">{{ $fmt }}</span></p>
                                    <p class="text-[10px] text-[#9ca3af]">{{ $detail }}</p>
                                </div>
                                <button class="grid size-7 place-items-center rounded text-[#6b7280] hover:bg-[#f3f4f6]"><svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg></button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
