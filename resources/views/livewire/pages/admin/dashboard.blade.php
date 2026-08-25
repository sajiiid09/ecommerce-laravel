<div class="p-5 sm:p-8">
    <div class="mx-auto max-w-[1480px]">
        <div class="mb-6 flex items-end justify-between">
            <div>
                <p class="text-sm text-[#6b7280]">StoreZ / Admin</p>
                <h1 class="mt-1 text-[28px] font-extrabold tracking-tight text-[#111827]">Admin Dashboard</h1>
                <p class="mt-1 text-sm text-[#6b7280]">Welcome back. Here is what is happening in your store today.</p>
            </div>
            <a href="{{ url('/admin/catalog/products/create') }}" class="rounded-lg bg-[#2563eb] px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#1d4ed8]">+ Quick Add</a>
        </div>

        {{-- Stats Cards --}}
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach([
                ['Total Sales', '৳'.number_format($stats['sales']), '#2563eb', 'M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                ['Total Orders', number_format($stats['orders']), '#f97316', 'M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z'],
                ['Products', number_format($stats['products']), '#ec4899', 'm21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9'],
                ['Customers', number_format($stats['customers']), '#10b981', 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z']
            ] as [$label, $value, $color, $path])
                <div class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold text-[#6b7280]">{{ $label }}</p>
                            <p class="mt-2 text-[26px] font-extrabold text-[#111827]">{{ $value }}</p>
                        </div>
                        <span style="background: {{ $color }}15; color: {{ $color }}" class="grid size-11 place-items-center rounded-full">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/></svg>
                        </span>
                    </div>
                    <div class="mt-4 flex items-center gap-2 text-xs">
                        <span class="font-bold text-[#10b981]">+0.0% ↑</span>
                        <span class="text-[#9ca3af]">vs last 7 days</span>
                        <span class="ml-auto">
                            <svg class="h-6 w-16" viewBox="0 0 64 24" fill="none">
                                <polyline points="0,20 8,16 16,18 24,10 32,12 40,6 48,8 56,2 64,4" stroke="{{ $color }}" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Sales Overview + Order Status --}}
        <div class="mt-5 grid gap-5 xl:grid-cols-[minmax(0,1.65fr)_minmax(300px,.9fr)]">
            <section class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-[#111827]">Sales Overview</h2>
                        <p class="mt-3 text-2xl font-extrabold text-[#111827]">৳{{ number_format($stats['sales']) }}</p>
                        <p class="mt-1 text-xs text-[#10b981]">+0.0% <span class="text-[#9ca3af]">vs previous 7 days</span></p>
                    </div>
                    <x-ui.select placeholder="Last 7 Days" class="w-32">
                        <x-ui.select.option value="7">Last 7 Days</x-ui.select.option>
                        <x-ui.select.option value="30">Last 30 Days</x-ui.select.option>
                    </x-ui.select>
                </div>
                <div class="mt-6 flex h-52 items-end gap-3 border-b border-dashed border-[#e5e7eb] px-3">
                    @foreach([18,42,30,58,94,65,79,100] as $height)
                        <div class="relative flex-1">
                            <div style="height:{{ $height }}%" class="rounded-t-lg bg-gradient-to-t from-[#dbeafe] to-[#2563eb]"></div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-2 flex justify-between text-[10px] text-[#9ca3af]">
                    <span>18 May</span><span>19 May</span><span>20 May</span><span>21 May</span><span>22 May</span><span>23 May</span><span>24 May</span>
                </div>
            </section>

            <section class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-bold text-[#111827]">Order Status</h2>
                    <x-ui.select placeholder="Last 7 Days" class="w-32">
                        <x-ui.select.option value="7">Last 7 Days</x-ui.select.option>
                        <x-ui.select.option value="30">Last 30 Days</x-ui.select.option>
                    </x-ui.select>
                </div>
                <div class="flex items-center gap-6 py-8">
                    <div class="grid size-36 place-items-center rounded-full" style="background: conic-gradient(#2563eb 0 59%, #f97316 59% 82%, #8b5cf6 82% 94%, #ef4444 94% 100%)">
                        <div class="grid size-24 place-items-center rounded-full bg-white text-center">
                            <b class="text-xl">{{ $stats['orders'] }}</b>
                            <span class="text-[10px] text-[#9ca3af]">Total Orders</span>
                        </div>
                    </div>
                    <div class="space-y-2 text-xs text-[#374151]">
                        <p><span class="mr-2 inline-block size-2 rounded-full bg-[#2563eb]"></span>Delivered <b class="float-right ml-5">59%</b></p>
                        <p><span class="mr-2 inline-block size-2 rounded-full bg-[#f97316]"></span>Processing <b class="float-right ml-5">22%</b></p>
                        <p><span class="mr-2 inline-block size-2 rounded-full bg-[#8b5cf6]"></span>Shipped <b class="float-right ml-5">11%</b></p>
                        <p><span class="mr-2 inline-block size-2 rounded-full bg-[#ef4444]"></span>Cancelled <b class="float-right ml-5">7%</b></p>
                    </div>
                </div>
                <div class="border-t border-[#e5e7eb] pt-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-[#6b7280]">Total Revenue</p>
                            <p class="text-lg font-extrabold text-[#111827]">৳{{ number_format($stats['sales']) }} <span class="text-xs font-bold text-[#10b981]">+12.5%</span></p>
                        </div>
                        <a href="#" class="rounded-lg border border-[#e5e7eb] px-3 py-1.5 text-xs font-bold text-[#374151] hover:bg-[#f9fafb]">View Report</a>
                    </div>
                </div>
            </section>
        </div>

        {{-- Recent Orders + Low Stock Alert --}}
        <div class="mt-5 grid gap-5 xl:grid-cols-[minmax(0,1.5fr)_minmax(300px,.9fr)]">
            <section class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-bold text-[#111827]">Recent Orders</h2>
                    <a href="#" class="text-xs font-bold text-[#2563eb]">View All Orders →</a>
                </div>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-[#e5e7eb] text-[10px] font-semibold uppercase tracking-wider text-[#9ca3af]">
                                <th class="pb-3 pr-4">Order ID</th>
                                <th class="pb-3 pr-4">Customer</th>
                                <th class="pb-3 pr-4">Amount</th>
                                <th class="pb-3 pr-4">Status</th>
                                <th class="pb-3 pr-4">Payment</th>
                                <th class="pb-3">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#f3f4f6]">
                            @foreach([
                                ['#SZ123545678', 'Sakib Hossain', '৳2,680', 'Delivered', 'bKash', 'bg-[#10b981]'],
                                ['#SZ123534567', 'Nusrat Jahan', '৳1,890', 'Shipped', 'Nagad', 'bg-[#8b5cf6]'],
                                ['#SZ123527890', 'Tanvir Ahmed', '৳3,450', 'Processing', 'VISA', 'bg-[#f97316]'],
                                ['#SZ123512345', 'Faria Islam', '৳1,250', 'Delivered', 'Mastercard', 'bg-[#10b981]'],
                                ['#SZ123505678', 'Mehedi Hasan', '৳980', 'Cancelled', 'bKash', 'bg-[#ef4444]'],
                            ] as [$id, $customer, $amount, $status, $payment, $statusColor])
                                <tr class="text-[#374151]">
                                    <td class="py-3 pr-4 font-medium text-[#2563eb]">{{ $id }}</td>
                                    <td class="py-3 pr-4">{{ $customer }}</td>
                                    <td class="py-3 pr-4">{{ $amount }}</td>
                                    <td class="py-3 pr-4"><span class="rounded-full {{ $statusColor }} px-2 py-0.5 text-[10px] font-bold text-white">{{ $status }}</span></td>
                                    <td class="py-3 pr-4 text-[#6b7280]">{{ $payment }}</td>
                                    <td class="py-3"><a href="#" class="rounded border border-[#e5e7eb] px-2 py-1 text-[10px] font-bold text-[#374151] hover:bg-[#f9fafb]">View</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 text-center">
                    <a href="#" class="text-xs font-bold text-[#2563eb]">View All Orders →</a>
                </div>
            </section>

            <section class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-bold text-[#111827]">Low Stock Alert</h2>
                    <a href="#" class="text-xs font-bold text-[#2563eb]">View All Products →</a>
                </div>
                <div class="mt-4 space-y-4">
                    @foreach([
                        ['Samsung Galaxy A15', '৳17,499', 'Stock: 8 pieces'],
                        ['Orix Detergent Powder 2kg', '৳320', 'Stock: 12 pieces'],
                        ['Walton Non-Stick Cookware Set', '৳3,450', 'Stock: 6 sets'],
                        ['Apex Casual Shoes', '৳1,799', 'Stock: 7 pairs'],
                    ] as [$name, $price, $stock])
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-lg bg-[#f3f4f6]"></div>
                            <div class="flex-1">
                                <p class="text-xs font-bold text-[#111827]">{{ $name }}</p>
                                <p class="text-[10px] text-[#6b7280]">{{ $stock }}</p>
                            </div>
                            <span class="text-xs font-bold text-[#111827]">{{ $price }}</span>
                            <span class="rounded-full bg-[#fef2f2] px-2 py-0.5 text-[10px] font-bold text-[#ef4444]">Low Stock</span>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        {{-- Top Selling Products + Quick Actions + Store Performance --}}
        <div class="mt-5 grid gap-5 xl:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)_minmax(0,1fr)]">
            <section class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-bold text-[#111827]">Top Selling Products</h2>
                    <a href="#" class="text-xs font-bold text-[#2563eb]">View All Products →</a>
                </div>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-[#e5e7eb] text-[10px] font-semibold uppercase tracking-wider text-[#9ca3af]">
                                <th class="pb-3 pr-4">Product</th>
                                <th class="pb-3 pr-4">Price</th>
                                <th class="pb-3 pr-4">Sold</th>
                                <th class="pb-3 pr-4">Stock</th>
                                <th class="pb-3">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#f3f4f6]">
                            @foreach([
                                ['Fresh Soyabean Oil 2L', '৳620', '532', '156'],
                                ['Teer Premium Basmati Rice 5kg', '৳950', '412', '78'],
                                ['Redmi Note 13 (8/128GB)', '৳18,999', '389', '45'],
                                ['Orix Crystal Detergent 2kg', '৳320', '365', '112'],
                            ] as [$name, $price, $sold, $stock])
                                <tr class="text-[#374151]">
                                    <td class="py-3 pr-4 font-medium">{{ $name }}</td>
                                    <td class="py-3 pr-4">{{ $price }}</td>
                                    <td class="py-3 pr-4">{{ $sold }}</td>
                                    <td class="py-3 pr-4">{{ $stock }}</td>
                                    <td class="py-3"><a href="#" class="rounded border border-[#e5e7eb] px-2 py-1 text-[10px] font-bold text-[#374151] hover:bg-[#f9fafb]">View</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                <h2 class="text-base font-bold text-[#111827]">Quick Actions</h2>
                <div class="mt-4 grid grid-cols-2 gap-3">
                    @foreach([
                        ['+ New Product', '/admin/catalog/products/create', 'M12 4.5v15m7.5-7.5h-15'],
                        ['Create Coupon', '#', 'M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z'],
                        ['Manage Orders', '#', 'M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z'],
                        ['Homepage Banners', '#', 'M2.25 15.75l5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z'],
                        ['Payment Settings', '#', 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z'],
                        ['View Reports', '#', 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z'],
                    ] as [$label, $href, $icon])
                        <a href="{{ url($href) }}" class="flex flex-col items-center gap-2 rounded-lg border border-[#e5e7eb] p-4 text-center hover:border-[#2563eb] hover:bg-[#f0f7ff]">
                            <svg class="size-6 text-[#6b7280]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                            <span class="text-[11px] font-bold text-[#374151]">{{ $label }}</span>
                        </a>
                    @endforeach
                </div>
            </section>

            <section class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-bold text-[#111827]">Store Performance</h2>
                    <x-ui.select placeholder="Last 7 Days" class="w-32">
                            <x-ui.select.option value="7">Last 7 Days</x-ui.select.option>
                        <x-ui.select.option value="30">Last 30 Days</x-ui.select.option>
                    </x-ui.select>
                </div>
                <div class="mt-4 space-y-4">
                    @foreach([
                        ['Average Order Value', '৳681', '+10.3%'],
                        ['Conversion Rate', '3.24%', '+0.8%'],
                        ['Total Visitors', '28,450', '+18.6%'],
                        ['Return Customers', '2,847', '+11.2%'],
                    ] as [$label, $value, $change])
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="grid size-9 place-items-center rounded-lg bg-[#f3f4f6]">
                                    <svg class="size-4 text-[#6b7280]" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs text-[#6b7280]">{{ $label }}</p>
                                    <p class="text-lg font-extrabold text-[#111827]">{{ $value }}</p>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-[#10b981]">{{ $change }} ↑</span>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</div>
