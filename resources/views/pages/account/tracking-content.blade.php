<main class="bg-store-soft py-6 sm:py-8">
    <x-store.ui.container>
        <div class="grid gap-5 lg:grid-cols-[240px_1fr]"><x-store.account.sidebar />
            <div>
                <nav class="mb-5 text-xs text-store-muted"><a href="{{ route('account.orders') }}" wire:navigate
                        class="hover:text-store-blue">My Orders</a><span class="mx-2">/</span><span
                        class="text-store-ink">Track Order</span></nav>
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-extrabold text-store-ink sm:text-3xl">Track Order
                            #{{ $orderData->order_number }}</h1>
                        <p class="mt-1 text-sm text-store-muted">{{ $orderData->status === 'completed' ? 'Your order is complete.' : 'We are keeping you updated on your order.' }}</p>
                    </div><x-store.ui.status-badge :status="$orderData->status" />
                </div>
                <section class="mt-6 rounded-card border border-store-border bg-white p-5 sm:p-8">
                    <div class="relative ml-2 border-l-2 border-store-blue pl-7 sm:ml-5 sm:pl-10">
                        @foreach ($timeline as $event)
                            <div class="relative pb-8 last:pb-0"><span
                                    class="absolute -left-[2.05rem] grid size-8 place-items-center rounded-full {{ $event['active'] ?? false ? 'bg-store-blue text-white' : ($event['completed'] ?? false ? 'bg-green-100 text-store-success' : 'bg-store-soft text-store-muted') }} sm:-left-[3.05rem]">
                                    @if ($event['active'] ?? false)
                                        <x-ui.icon name="truck" class="size-4 !text-current" />
                                    @elseif ($event['completed'] ?? false)
                                    <x-ui.icon name="check" class="size-4 !text-current" />@else<x-ui.icon
                                            name="clock" class="size-4 !text-current" />
                                    @endif
                                </span>
                                <h2 class="text-sm font-bold text-store-ink">{{ $event['label'] }}</h2>
                                <p class="mt-1 text-xs text-store-muted">
                                    {{ $event['date'] ?? 'Awaiting update' }}
                                </p>
                                <p class="mt-2 text-sm text-store-text">
                                    {{ $event['active'] ?? false ? 'Your order is currently at this stage.' : ($event['completed'] ?? false ? 'This stage has been completed.' : 'We will update you when this stage is reached.') }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>
        </div>
    </x-store.ui.container>
</main>
