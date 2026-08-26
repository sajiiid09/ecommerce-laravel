@php($order ??= (object) ['customer_name' => 'StoreZ customer', 'order_number' => 'SZ-DEMO', 'status' => 'pending', 'payment_status' => 'unpaid', 'total_minor' => 0, 'customer_email' => 'customer@example.test'])
<main class="bg-store-soft py-12 sm:py-20">
    <x-store.ui.container size="narrow">
        <section class="rounded-card border border-store-border bg-white p-6 text-center shadow-store-soft sm:p-10">
            <div class="mx-auto grid size-16 place-items-center rounded-full bg-green-100 text-3xl text-green-600">✓</div>
            <h1 class="mt-5 text-2xl font-extrabold text-store-ink sm:text-3xl">Order confirmed</h1>
            <p class="mt-2 text-sm text-store-muted">Thank you, {{ $order->customer_name }}. We’ve received your order.</p>
            <div class="mt-6 rounded-control bg-store-soft p-4 text-left text-sm"><div class="flex justify-between gap-4"><span class="text-store-muted">Order number</span><strong class="text-store-ink">{{ $order->order_number }}</strong></div><div class="mt-2 flex justify-between gap-4"><span class="text-store-muted">Status</span><strong class="capitalize text-store-ink">{{ $order->status }}</strong></div><div class="mt-2 flex justify-between gap-4"><span class="text-store-muted">Payment</span><strong class="capitalize text-store-ink">{{ $order->payment_status }}</strong></div><div class="mt-2 flex justify-between gap-4"><span class="text-store-muted">Total</span><strong class="text-store-red">৳{{ number_format($order->total_minor / 100, 2) }}</strong></div><div class="mt-2 flex justify-between gap-4"><span class="text-store-muted">Email</span><strong class="text-store-ink">{{ $order->customer_email }}</strong></div></div>
            <div class="mt-6 flex flex-wrap justify-center gap-3"><a href="{{ auth()->check() ? route('account.order', ['order' => $order->order_number]) : route('store.category') }}" wire:navigate class="inline-flex h-11 items-center rounded-control bg-store-blue px-5 text-sm font-bold text-white">{{ auth()->check() ? 'View Order' : 'Continue Shopping' }}</a><a href="{{ route('store.category') }}" wire:navigate class="inline-flex h-11 items-center rounded-control border border-store-blue px-5 text-sm font-bold text-store-blue">Continue Shopping</a></div>
        </section>
    </x-store.ui.container>
</main>
