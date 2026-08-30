<div class="p-5 sm:p-8">
    <div class="mx-auto max-w-[1480px]">
        <div class="mb-6 flex items-end justify-between gap-4">
            <div><p class="text-sm text-slate-500">StoreZ / Admin</p><h1 class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900">Admin Dashboard</h1><p class="mt-1 text-sm text-slate-500">A live overview of your commerce showcase.</p></div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach([['Total Sales', '৳'.number_format($stats['sales'] / 100, 2), 'text-blue-600'], ['Total Orders', number_format($stats['orders']), 'text-orange-600'], ['Published Products', number_format($stats['published']), 'text-pink-600'], ['Customers', number_format($stats['customers']), 'text-emerald-600']] as [$label, $value, $tone])
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-xs font-semibold text-slate-500">{{ $label }}</p><p class="mt-2 text-2xl font-extrabold {{ $tone }}">{{ $value }}</p></div>
            @endforeach
        </div>

        <div class="mt-5 grid gap-5 xl:grid-cols-[minmax(0,1.5fr)_minmax(300px,.9fr)]">
            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between"><div><h2 class="text-base font-bold text-slate-900">Recent Orders</h2><p class="mt-1 text-xs text-slate-500">Latest customer activity.</p></div><a href="{{ route('admin.orders') }}" class="text-xs font-bold text-blue-600">View all orders →</a></div>
                <div class="mt-4 overflow-x-auto"><table class="w-full text-left text-sm"><thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-2 py-3">Order</th><th class="px-2 py-3">Customer</th><th class="px-2 py-3">Total</th><th class="px-2 py-3">Status</th><th class="px-2 py-3"></th></tr></thead><tbody class="divide-y divide-slate-100">
                    @forelse($recentOrders as $order)
                        <tr><td class="px-2 py-3 font-bold text-blue-600">{{ $order->order_number }}</td><td class="px-2 py-3">{{ $order->customer_name }}</td><td class="px-2 py-3 font-semibold">৳{{ number_format($order->total_minor / 100, 2) }}</td><td class="px-2 py-3"><x-store.ui.status-badge :status="$order->status" /></td><td class="px-2 py-3 text-right"><a href="{{ route('admin.order', ['order' => $order->order_number]) }}" class="text-xs font-bold text-blue-600">View</a></td></tr>
                    @empty
                        <tr><td colspan="5" class="px-2 py-8 text-center text-sm text-slate-500">No orders yet.</td></tr>
                    @endforelse
                </tbody></table></div>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between"><div><h2 class="text-base font-bold text-slate-900">Low Stock</h2><p class="mt-1 text-xs text-slate-500">Products needing attention.</p></div><a href="{{ route('admin.catalog.inventory') }}" class="text-xs font-bold text-blue-600">View inventory →</a></div>
                <div class="mt-4 space-y-3">
                    @forelse($lowStock as $inventory)
                        <div class="flex items-center justify-between gap-3 rounded-lg bg-slate-50 p-3"><div class="min-w-0"><p class="truncate text-sm font-semibold text-slate-900">{{ $inventory->variant?->product?->name ?? 'Unnamed product' }}</p><p class="mt-1 text-xs text-slate-500">{{ $inventory->variant?->sku }}</p></div><span class="shrink-0 text-sm font-bold text-orange-600">{{ $inventory->availableQuantity() }} left</span></div>
                    @empty
                        <p class="rounded-lg bg-emerald-50 p-4 text-sm text-emerald-700">No low-stock products.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <section class="mt-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><h2 class="text-base font-bold text-slate-900">Quick Actions</h2><div class="mt-4 flex flex-wrap gap-3"><a href="{{ route('admin.catalog.products.create') }}" class="rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-700 hover:border-blue-500">New product</a><a href="{{ route('admin.orders') }}" class="rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-700 hover:border-blue-500">Manage orders</a><a href="{{ route('admin.content.homepage') }}" class="rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-700 hover:border-blue-500">Homepage content</a><a href="{{ route('admin.catalog.inventory') }}" class="rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-700 hover:border-blue-500">Inventory</a></div></section>
    </div>
</div>
