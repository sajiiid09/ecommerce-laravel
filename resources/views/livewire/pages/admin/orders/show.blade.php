<div class="p-5 sm:p-8">
    <div class="mx-auto max-w-[1200px]">
        <a href="{{ route('admin.orders') }}" class="text-sm font-semibold text-blue-600">← Orders</a>
        <div class="mt-4 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm text-slate-500">StoreZ / Orders / {{ $orderModel->order_number }}</p>
                <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900">Order {{ $orderModel->order_number }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ optional($orderModel->placed_at)->format('F j, Y g:i A') }}</p>
            </div>
            <x-store.ui.status-badge :status="$orderModel->status" class="text-sm" />
        </div>
        @if(session('status')) <p class="mt-4 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('status') }}</p> @endif
        <div class="mt-6 grid gap-5 lg:grid-cols-[minmax(0,1fr)_320px]">
            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-bold text-slate-900">Items</h2>
                @if($orderModel->discount_minor > 0)<p class="mt-3 rounded-lg bg-emerald-50 p-3 text-sm font-semibold text-emerald-700">Coupon{{ $orderModel->coupon_code ? ' '.$orderModel->coupon_code : '' }} saved ৳{{ number_format($orderModel->discount_minor / 100, 2) }}.</p>@endif
                <div class="mt-4 divide-y divide-slate-100">
                    @foreach($orderModel->items as $item)
                        <div class="flex justify-between gap-4 py-4 first:pt-0">
                            <div><p class="font-semibold text-slate-900">{{ $item->product_name }}</p><p class="mt-1 text-xs text-slate-500">{{ $item->variant_name ?: $item->sku }} · Qty {{ $item->quantity }}</p></div>
                            <span class="font-semibold">৳{{ number_format($item->line_total_minor / 100, 2) }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="mt-5 border-t border-slate-200 pt-4 text-sm"><div class="flex justify-between"><span class="text-slate-500">Subtotal</span><span>৳{{ number_format($orderModel->subtotal_minor / 100, 2) }}</span></div><div class="mt-2 flex justify-between"><span class="text-slate-500">Delivery</span><span>৳{{ number_format($orderModel->shipping_minor / 100, 2) }}</span></div><div class="mt-3 flex justify-between text-base font-bold"><span>Total</span><span class="text-red-600">৳{{ number_format($orderModel->total_minor / 100, 2) }}</span></div></div>
            </section>
            <aside class="space-y-5">
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><h2 class="text-base font-bold text-slate-900">Customer</h2><p class="mt-3 text-sm font-semibold">{{ $orderModel->customer_name }}</p><p class="mt-1 text-sm text-slate-500">{{ $orderModel->customer_email }}<br>{{ $orderModel->customer_phone }}</p><p class="mt-4 text-sm text-slate-500">{{ $orderModel->shippingAddress?->address_line }}<br>{{ $orderModel->shippingAddress?->city }}, {{ $orderModel->shippingAddress?->district }}</p></section>
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><h2 class="text-base font-bold text-slate-900">Payment</h2><p class="mt-3 text-sm capitalize">{{ $orderModel->payment?->provider }} · {{ $orderModel->payment_status }}</p><p class="mt-1 text-xs text-slate-500">Reference: {{ $orderModel->payment?->provider_reference ?: '—' }}</p></section>
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><h2 class="text-base font-bold text-slate-900">Actions</h2><div class="mt-3 grid gap-2">@if($orderModel->status === 'pending')<button wire:click="changeStatus('processing')" class="h-10 rounded-lg bg-blue-600 text-sm font-bold text-white">Mark Processing</button>@endif @if(in_array($orderModel->status, ['pending', 'processing'], true))<button wire:click="changeStatus('completed')" class="h-10 rounded-lg bg-emerald-600 text-sm font-bold text-white">Mark Completed</button><button wire:click="changeStatus('cancelled')" class="h-10 rounded-lg border border-red-200 text-sm font-bold text-red-600">Cancel</button>@endif</div></section>
            </aside>
        </div>
    </div>
</div>
