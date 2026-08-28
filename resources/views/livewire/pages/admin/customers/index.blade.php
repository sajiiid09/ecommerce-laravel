<div class="p-5 sm:p-8">
    <div class="mx-auto max-w-[1480px]">
        <div class="mb-6">
            <p class="text-sm text-slate-500">StoreZ / Customers</p>
            <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900">Customers</h1>
            <p class="mt-1 text-sm text-slate-500">View registered customer accounts, orders, and reviews.</p>
        </div>

        <div class="mb-4 flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center">
            <x-ui.input
                wire:model.live.debounce.300ms="searchQuery"
                type="search"
                placeholder="Search customers by name, email, or phone..."
                leftIcon="magnifying-glass"
                class="min-w-0 flex-1"
            />
        </div>

        <x-ui.table
            :paginator="$customers"
            pagination:variant="full"
            :pagination:options="[10, 15, 25, 50]"
            wire:loading
            loadOn="pagination, search"
            class="w-full overflow-hidden rounded-xl border border-slate-200 bg-white p-0 shadow-sm"
            table:class="w-full table-fixed text-left"
        >
            <colgroup>
                <col class="w-[27%]">
                <col class="w-[37%]">
                <col class="w-[12%]">
                <col class="w-[14%]">
                <col class="w-[10%]">
            </colgroup>
            <x-ui.table.header class="bg-slate-50 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                <x-ui.table.columns>
                    <x-ui.table.head class="px-4 py-3">Customer</x-ui.table.head>
                    <x-ui.table.head class="px-4 py-3">Contact</x-ui.table.head>
                    <x-ui.table.head class="px-4 py-3">Orders</x-ui.table.head>
                    <x-ui.table.head class="px-4 py-3">Joined</x-ui.table.head>
                    <x-ui.table.head class="px-4 py-3">Action</x-ui.table.head>
                </x-ui.table.columns>
            </x-ui.table.header>
            <x-ui.table.rows class="divide-y divide-slate-100">
                @forelse($customers as $customer)
                    <x-ui.table.row :key="'admin-customer-'.$customer->id" class="text-slate-700 hover:bg-slate-50">
                        <x-ui.table.cell class="px-4 py-4">
                            <span class="block truncate text-sm font-bold text-slate-900">{{ $customer->name }}</span>
                        </x-ui.table.cell>
                        <x-ui.table.cell class="px-4 py-4 text-sm">
                            <span class="block truncate text-slate-700">{{ $customer->email }}</span>
                            <span class="mt-1 block truncate text-xs text-slate-400">{{ $customer->phone ?: 'No phone number' }}</span>
                        </x-ui.table.cell>
                        <x-ui.table.cell class="px-4 py-4 text-sm font-semibold text-slate-700">{{ number_format($customer->orders_count) }}</x-ui.table.cell>
                        <x-ui.table.cell class="px-4 py-4 text-sm text-slate-500">{{ optional($customer->created_at)->format('M j, Y') }}</x-ui.table.cell>
                        <x-ui.table.cell class="px-4 py-4">
                            <x-ui.button type="button" size="sm" variant="outline" color="slate" wire:click="viewCustomer({{ $customer->id }})" aria-label="View {{ $customer->name }}">View</x-ui.button>
                        </x-ui.table.cell>
                    </x-ui.table.row>
                @empty
                    <x-ui.table.empty>{{ filled($searchQuery) ? 'No customers match your search.' : 'No registered customers found.' }}</x-ui.table.empty>
                @endforelse
            </x-ui.table.rows>
        </x-ui.table>
    </div>

    <x-ui.modal
        id="customer-details"
        width="5xl"
        :heading="$viewingCustomer?->name ?? 'Customer details'"
        description="Customer profile, order history, and product reviews."
        stickyHeader
    >
        @if($viewingCustomer && $customerOrders && $customerReviews)
            <div wire:key="customer-details-{{ $viewingCustomer->id }}" class="space-y-6">
                <section class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Orders</p>
                        <p class="mt-1 text-2xl font-extrabold text-slate-900">{{ number_format($viewingCustomer->orders_count) }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Reviews</p>
                        <p class="mt-1 text-2xl font-extrabold text-slate-900">{{ number_format($viewingCustomer->reviews_count) }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Order total</p>
                        <p class="mt-1 text-2xl font-extrabold text-slate-900">৳{{ number_format(($viewingCustomer->orders_sum_total_minor ?? 0) / 100, 2) }}</p>
                    </div>
                </section>

                <section class="rounded-lg border border-slate-200 p-4">
                    <h3 class="text-base font-bold text-slate-900">Profile information</h3>
                    <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Name</dt><dd class="mt-1 font-semibold text-slate-800">{{ $viewingCustomer->name }}</dd></div>
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Email</dt><dd class="mt-1 text-slate-700">{{ $viewingCustomer->email }}</dd></div>
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Phone</dt><dd class="mt-1 text-slate-700">{{ $viewingCustomer->phone ?: 'Not provided' }}</dd></div>
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Joined</dt><dd class="mt-1 text-slate-700">{{ optional($viewingCustomer->created_at)->format('F j, Y') }}</dd></div>
                    </dl>
                </section>

                <section>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div><h3 class="text-base font-bold text-slate-900">Order history</h3><p class="mt-1 text-xs text-slate-500">Orders placed by this registered customer.</p></div>
                        <span class="text-xs font-semibold text-slate-400">{{ number_format($viewingCustomer->orders_count) }} total</span>
                    </div>
                    <x-ui.table class="mt-3 w-full overflow-hidden rounded-lg border border-slate-200" table:class="w-full min-w-[640px] table-fixed text-left">
                        <colgroup>
                            <col class="w-[30%]">
                            <col class="w-[25%]">
                            <col class="w-[20%]">
                            <col class="w-[25%]">
                        </colgroup>
                        <x-ui.table.header class="bg-slate-50 text-xs font-semibold text-slate-500"><x-ui.table.columns>
                            <x-ui.table.head class="px-3 py-3">Order</x-ui.table.head>
                            <x-ui.table.head class="px-3 py-3">Date</x-ui.table.head>
                            <x-ui.table.head class="px-3 py-3">Total</x-ui.table.head>
                            <x-ui.table.head class="px-3 py-3">Status</x-ui.table.head>
                        </x-ui.table.columns></x-ui.table.header>
                        <x-ui.table.rows class="divide-y divide-slate-100">
                            @forelse($customerOrders as $order)
                                <x-ui.table.row :key="'customer-order-'.$order->id" class="text-sm text-slate-700">
                                    <x-ui.table.cell class="px-3 py-3"><a href="{{ route('admin.order', ['order' => $order->order_number]) }}" class="font-bold text-blue-600 hover:underline">{{ $order->order_number }}</a></x-ui.table.cell>
                                    <x-ui.table.cell class="px-3 py-3 text-slate-500">{{ optional($order->placed_at)->format('M j, Y') ?: 'Not placed' }}</x-ui.table.cell>
                                    <x-ui.table.cell class="px-3 py-3 font-semibold">৳{{ number_format($order->total_minor / 100, 2) }}</x-ui.table.cell>
                                    <x-ui.table.cell class="px-3 py-3"><span class="rounded-full bg-blue-50 px-2 py-1 text-xs font-bold capitalize text-blue-700">{{ $order->status }}</span></x-ui.table.cell>
                                </x-ui.table.row>
                            @empty
                                <x-ui.table.empty>No orders found for this customer.</x-ui.table.empty>
                            @endforelse
                        </x-ui.table.rows>
                    </x-ui.table>
                    @if($customerOrders->hasPages())
                        <div class="mt-3 flex items-center justify-between gap-3 text-xs text-slate-500">
                            <span>Page {{ $customerOrders->currentPage() }} of {{ $customerOrders->lastPage() }}</span>
                            <div class="flex gap-2">
                                <x-ui.button type="button" size="sm" variant="outline" color="slate" wire:click="previousCustomerOrdersPage" :disabled="$customerOrders->onFirstPage()">Previous</x-ui.button>
                                <x-ui.button type="button" size="sm" variant="outline" color="slate" wire:click="nextCustomerOrdersPage" :disabled="! $customerOrders->hasMorePages()">Next</x-ui.button>
                            </div>
                        </div>
                    @endif
                </section>

                <section>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div><h3 class="text-base font-bold text-slate-900">Review history</h3><p class="mt-1 text-xs text-slate-500">Product reviews submitted by this customer.</p></div>
                        <span class="text-xs font-semibold text-slate-400">{{ number_format($viewingCustomer->reviews_count) }} total</span>
                    </div>
                    <x-ui.table class="mt-3 w-full overflow-hidden rounded-lg border border-slate-200" table:class="w-full table-fixed text-left">
                        <x-ui.table.header class="bg-slate-50 text-xs font-semibold text-slate-500"><x-ui.table.columns>
                            <x-ui.table.head class="px-3 py-3">Product</x-ui.table.head>
                            <x-ui.table.head class="px-3 py-3">Rating</x-ui.table.head>
                            <x-ui.table.head class="px-3 py-3">Review</x-ui.table.head>
                            <x-ui.table.head class="px-3 py-3">Status</x-ui.table.head>
                        </x-ui.table.columns></x-ui.table.header>
                        <x-ui.table.rows class="divide-y divide-slate-100">
                            @forelse($customerReviews as $review)
                                <x-ui.table.row :key="'customer-review-'.$review->id" class="text-sm text-slate-700">
                                    <x-ui.table.cell class="px-3 py-3 font-semibold text-slate-800">{{ $review->product?->name ?? 'Deleted product' }}</x-ui.table.cell>
                                    <x-ui.table.cell class="px-3 py-3 text-amber-500">{{ $review->rating }}/5</x-ui.table.cell>
                                    <x-ui.table.cell class="max-w-sm px-3 py-3"><span class="block truncate font-semibold text-slate-800">{{ $review->title ?: 'Untitled review' }}</span><span class="mt-1 block truncate text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($review->review, 90) }}</span></x-ui.table.cell>
                                    <x-ui.table.cell class="px-3 py-3"><span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-bold capitalize text-slate-600">{{ $review->status }}</span></x-ui.table.cell>
                                </x-ui.table.row>
                            @empty
                                <x-ui.table.empty>No reviews found for this customer.</x-ui.table.empty>
                            @endforelse
                        </x-ui.table.rows>
                    </x-ui.table>
                    @if($customerReviews->hasPages())
                        <div class="mt-3 flex items-center justify-between gap-3 text-xs text-slate-500">
                            <span>Page {{ $customerReviews->currentPage() }} of {{ $customerReviews->lastPage() }}</span>
                            <div class="flex gap-2">
                                <x-ui.button type="button" size="sm" variant="outline" color="slate" wire:click="previousCustomerReviewsPage" :disabled="$customerReviews->onFirstPage()">Previous</x-ui.button>
                                <x-ui.button type="button" size="sm" variant="outline" color="slate" wire:click="nextCustomerReviewsPage" :disabled="! $customerReviews->hasMorePages()">Next</x-ui.button>
                            </div>
                        </div>
                    @endif
                </section>
            </div>
        @endif
    </x-ui.modal>
</div>
