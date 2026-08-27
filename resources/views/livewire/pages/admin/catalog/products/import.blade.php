<div class="p-5 sm:p-8">
    <div class="mx-auto max-w-[1480px]">
        <div class="mb-6">
            <p class="text-sm text-[#6b7280]">StoreZ / Catalog / Products / Import</p>
            <h1 class="mt-1 text-[28px] font-extrabold tracking-tight text-[#111827]">Import Products</h1>
            <p class="mt-1 text-sm text-[#6b7280]">Import product data from CSV with validation before saving.</p>
        </div>

        <div class="mb-5 flex flex-wrap items-center gap-2">
            <a href="{{ url('/admin/catalog/products') }}" class="flex items-center gap-2 rounded-lg border border-[#e5e7eb] bg-white px-4 py-2.5 text-sm font-semibold text-[#374151] hover:bg-[#f9fafb]">Cancel</a>
            <a href="{{ url('/admin/catalog/products') }}" class="flex items-center gap-2 rounded-lg border border-[#e5e7eb] bg-white px-4 py-2.5 text-sm font-semibold text-[#374151] hover:bg-[#f9fafb]">Back</a>
            <button class="rounded-lg bg-[#2563eb] px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#1d4ed8]">Start Import</button>
        </div>

        <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_280px]">
            <div class="space-y-5">
                {{-- Import Wizard Progress --}}
                <div class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-[#111827]">Import Wizard Progress</h3>
                    <div class="mt-4 flex items-center justify-between">
                        @foreach(['Upload File','Map Fields','Validate','Import'] as $i=>$step)
                            <div class="flex flex-1 items-center">
                                <div class="flex flex-col items-center">
                                    <span class="grid size-8 place-items-center rounded-full {{ $step === 'Upload File' ? 'bg-[#2563eb] text-white' : 'bg-[#f3f4f6] text-[#9ca3af]' }} text-sm font-bold">{{ $i+1 }}</span>
                                    <span class="mt-1 text-[10px] text-[#9ca3af]">{{ $step }}</span>
                                </div>
                                @if(!$loop->last)<div class="mx-2 h-0.5 flex-1 bg-[#e5e7eb]"></div>@endif
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Step 1: Upload --}}
                <div class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-[#111827]">STEP 1 — Upload Products CSV</h3>
                    <div class="mt-4 flex gap-4">
                        <label class="flex min-h-[120px] flex-1 cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-[#d1d5db] bg-[#f9fafb] text-center hover:border-[#2563eb] hover:bg-[#eff6ff]">
                            <svg class="size-8 text-[#2563eb]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/></svg>
                            <span class="mt-2 text-sm font-semibold text-[#374151]">Drag & drop CSV here</span>
                            <span class="mt-1 text-xs text-[#6b7280]">or <span class="text-[#2563eb]">Browse Files</span></span>
                            <input type="file" wire:model="file" accept=".csv" class="sr-only">
                        </label>
                        <div class="flex-1 space-y-3">
                            <div class="rounded-lg bg-[#f9fafb] p-3">
                                <p class="text-sm font-bold text-[#111827]">products_import_may_2026.csv</p>
                                <p class="text-xs text-[#9ca3af]">4.8 MB · 187 rows</p>
                            </div>
                            <div class="space-y-2 text-xs text-[#6b7280]">
                                <p class="flex items-center gap-2"><svg class="size-4 text-[#10b981]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg> CSV only</p>
                                <p class="flex items-center gap-2"><svg class="size-4 text-[#10b981]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg> Max 10 MB</p>
                                <p class="flex items-center gap-2"><svg class="size-4 text-[#10b981]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg> Simple products supported</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="text-xs font-bold text-[#111827]">Import Mode</p>
                        <div class="mt-2 flex gap-4">
                            <label class="flex items-center gap-2 text-sm text-[#374151]"><input type="radio" checked class="text-[#2563eb]"> Create New Products</label>
                            <label class="flex items-center gap-2 text-sm text-[#374151]"><input type="radio" class="text-[#2563eb]"> Update Existing Products by SKU</label>
                        </div>
                    </div>
                </div>

                {{-- Step 2: Field Mapping --}}
                <div class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-[#111827]">STEP 2 — Field Mapping Preview</h3>
                    <p class="mt-1 text-xs text-[#9ca3af]">Match StoreZ product fields to CSV columns</p>
                    <div class="mt-4 space-y-3">
                        @foreach([['Product Name','product_name'],['SKU','sku'],['Brand','brand'],['Category','category'],['Regular Price','regular_price'],['Sale Price','sale_price'],['Quantity','quantity'],['Status','status']] as [$field,$col])
                            <div class="flex items-center gap-4">
                                <span class="w-40 text-sm text-[#374151]">{{ $field }}</span>
                                <svg class="size-4 text-[#9ca3af]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                                <select class="flex-1 rounded-lg border border-[#e5e7eb] bg-white px-3 py-2 text-sm text-[#374151]"><option>{{ $col }}</option></select>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Step 3: Validation Preview --}}
                <div class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-[#111827]">STEP 3 — Validation Preview</h3>
                    <div class="mt-4 grid grid-cols-3 gap-4">
                        <div class="rounded-lg bg-[#dcfce7] p-4 text-center">
                            <p class="text-2xl font-extrabold text-[#16a34a]">184</p>
                            <p class="text-xs text-[#16a34a]">Valid Rows</p>
                        </div>
                        <div class="rounded-lg bg-[#fef3c7] p-4 text-center">
                            <p class="text-2xl font-extrabold text-[#d97706]">8</p>
                            <p class="text-xs text-[#d97706]">Warnings</p>
                        </div>
                        <div class="rounded-lg bg-[#fef2f2] p-4 text-center">
                            <p class="text-2xl font-extrabold text-[#ef4444]">3</p>
                            <p class="text-xs text-[#ef4444]">Errors</p>
                        </div>
                    </div>
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-[#f9fafb] text-[10px] font-semibold uppercase text-[#9ca3af]">
                                <tr><th class="px-3 py-2">Row</th><th class="px-3 py-2">Product</th><th class="px-3 py-2">SKU</th><th class="px-3 py-2">Status</th><th class="px-3 py-2">Message</th></tr>
                            </thead>
                            <tbody class="divide-y divide-[#f3f4f6]">
                                <tr><td class="px-3 py-2">17</td><td class="px-3 py-2">Wireless Bluetooth Headphones</td><td class="px-3 py-2">WBH-BLK-M</td><td class="px-3 py-2"><span class="rounded-full bg-[#fef2f2] px-2 py-0.5 text-[10px] font-bold text-[#ef4444]">Error</span></td><td class="px-3 py-2 text-[#6b7280]">Duplicate SKU</td></tr>
                                <tr><td class="px-3 py-2">44</td><td class="px-3 py-2">Fresh Soyabean Oil 2L</td><td class="px-3 py-2">FSO-2L</td><td class="px-3 py-2"><span class="rounded-full bg-[#fef3c7] px-2 py-0.5 text-[10px] font-bold text-[#d97706]">Warning</span></td><td class="px-3 py-2 text-[#6b7280]">Unknown Brand</td></tr>
                                <tr><td class="px-3 py-2">81</td><td class="px-3 py-2">Redmi Note 13</td><td class="px-3 py-2">RN13-128-BLK</td><td class="px-3 py-2"><span class="rounded-full bg-[#fef2f2] px-2 py-0.5 text-[10px] font-bold text-[#ef4444]">Error</span></td><td class="px-3 py-2 text-[#6b7280]">Invalid price</td></tr>
                                <tr><td class="px-3 py-2">103</td><td class="px-3 py-2">Teer Premium Basmati Rice 5kg</td><td class="px-3 py-2">TPR-5KG</td><td class="px-3 py-2"><span class="rounded-full bg-[#dcfce7] px-2 py-0.5 text-[10px] font-bold text-[#16a34a]">Valid</span></td><td class="px-3 py-2 text-[#6b7280]">Ready to import</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Step 4: Import Summary --}}
                <div class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-[#111827]">STEP 4 — Import Summary</h3>
                    <div class="mt-4 grid grid-cols-4 gap-3">
                        @foreach([['156','Products Created','#16a34a'],['28','Products Updated','#2563eb'],['3','Rows Skipped','#d97706'],['3','Errors','#ef4444']] as [$num,$label,$color])
                            <div class="rounded-lg p-3 text-center" style="background: {{ $color }}10">
                                <p class="text-lg font-extrabold" style="color: {{ $color }}">{{ $num }}</p>
                                <p class="text-[10px] text-[#6b7280]">{{ $label }}</p>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4">
                        <div class="flex items-center justify-between text-xs text-[#6b7280]">
                            <span>Import Progress</span>
                            <span class="font-bold text-[#111827]">82%</span>
                        </div>
                        <div class="mt-1 h-2 rounded-full bg-[#f3f4f6]">
                            <div class="h-2 rounded-full bg-[#2563eb]" style="width: 82%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-5">
                <div class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-[#111827]">
                        <svg class="size-5 text-[#f59e0b]" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                        Import Tips
                    </h3>
                    <div class="mt-3 space-y-2 text-xs text-[#6b7280]">
                        @foreach(['Use SKU to update existing products','Unknown brands can be created during import','Invalid rows can be fixed and re-uploaded','Use the template for the best results'] as $tip)
                            <p class="flex items-center gap-2"><svg class="size-4 shrink-0 text-[#10b981]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg> {{ $tip }}</p>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold text-[#111827]">Recent Imports</h3>
                    </div>
                    <div class="mt-4 space-y-3">
                        @foreach([['Products Import','Success','156 created','24 May 2026'],['Stock Update','Partial','3 warnings','22 May 2026'],['Price Sync','Failed','2 errors','20 May 2026']] as [$name,$status,$detail,$date])
                            <div class="flex items-center gap-3">
                                <div class="size-8 shrink-0 rounded-lg bg-[#f3f4f6]"></div>
                                <div class="flex-1">
                                    <p class="text-xs font-bold text-[#111827]">{{ $name }}</p>
                                    <p class="text-[10px] text-[#9ca3af]">{{ $date }} · {{ $detail }}</p>
                                </div>
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $status==='Success' ? 'bg-[#dcfce7] text-[#16a34a]' : ($status==='Partial' ? 'bg-[#fef3c7] text-[#d97706]' : 'bg-[#fef2f2] text-[#ef4444]') }}">{{ $status }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-[#111827]">Import Summary Snapshot</h3>
                    <div class="mt-4 space-y-2 text-xs">
                        @foreach(['File Name'=>'products_import_may_2026.csv','Uploaded By'=>'Rafiq Ahmed','Imported By'=>'Admin User','Date'=>'24 May 2026 · 10:24 AM','Mode'=>'Update Existing Products by SKU','Last Validation'=>'Passed with warnings'] as $key=>$val)
                            <div class="flex items-center justify-between">
                                <span class="text-[#6b7280]">{{ $key }}</span>
                                <span class="font-semibold text-[#111827]">{{ $val }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
