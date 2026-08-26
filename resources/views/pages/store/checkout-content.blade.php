<main class="bg-store-soft py-6 sm:py-8">
    <x-store.ui.container>
        <x-store.ui.breadcrumb :items="[['label' => 'Cart', 'url' => route('store.cart')], ['label' => 'Checkout']]" />
        <h1 class="text-2xl font-extrabold tracking-tight text-store-ink sm:text-3xl">Checkout</h1>

        @if ($errors->has('cart'))
            <div class="mt-4 rounded-control bg-red-50 p-4 text-sm text-red-700">{{ $errors->first('cart') }}</div>
        @endif

        <div class="mt-6 grid gap-5 lg:grid-cols-[minmax(0,1fr)_360px]">
            <section class="space-y-5">
                <div class="flex items-center gap-2 rounded-card border border-store-border bg-white p-4 text-sm">
                    @foreach (['Customer & Address', 'Delivery', 'Payment', 'Review'] as $index => $label)
                        <span class="grid size-8 shrink-0 place-items-center rounded-full {{ $step >= $index + 1 ? 'bg-store-blue text-white' : 'bg-store-soft text-store-muted' }}">{{ $index + 1 }}</span>
                        <span class="hidden font-bold text-store-ink sm:inline">{{ $label }}</span>
                        @if ($index < 3)
                            <span class="mx-1 h-px flex-1 bg-store-border"></span>
                        @endif
                    @endforeach
                </div>

                @if ($step === 1)
                    <section class="rounded-card border border-store-border bg-white p-5">
                        <h2 class="text-lg font-extrabold text-store-ink">Customer and delivery address</h2>
                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            @foreach ([['customer_name', 'Full name', 'text'], ['customer_email', 'Email', 'email'], ['customer_phone', 'Phone', 'text'], ['city', 'City / Area', 'text'], ['district', 'District', 'text'], ['postal_code', 'Postal code', 'text']] as [$field, $label, $type])
                                <label class="text-sm font-semibold text-store-ink">
                                    {{ $label }}
                                    <input type="{{ $type }}" wire:model="{{ $field }}" class="mt-2 h-11 w-full rounded-control border border-store-border px-3">
                                    @error($field)
                                        <span class="mt-1 block text-xs text-red-600">{{ $message }}</span>
                                    @enderror
                                </label>
                            @endforeach
                            <label class="text-sm font-semibold text-store-ink sm:col-span-2">
                                Address
                                <input wire:model="address_line" class="mt-2 h-11 w-full rounded-control border border-store-border px-3">
                                @error('address_line')
                                    <span class="mt-1 block text-xs text-red-600">{{ $message }}</span>
                                @enderror
                            </label>
                        </div>
                        <button type="button" wire:click="nextStep" class="mt-5 inline-flex h-11 w-full items-center justify-center rounded-control bg-store-blue text-sm font-bold text-white">Continue to Delivery</button>
                    </section>
                @elseif ($step === 2)
                    <section class="rounded-card border border-store-border bg-white p-5">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-extrabold text-store-ink">Delivery method</h2>
                            <button type="button" wire:click="previousStep" class="text-sm font-semibold text-store-blue">Back</button>
                        </div>
                        <div class="mt-5 space-y-3">
                            <label class="flex cursor-pointer gap-3 rounded-control border-2 border-store-blue bg-store-blue-soft p-4">
                                <input type="radio" wire:model="delivery_method" value="standard" class="mt-1 text-store-blue">
                                <span><span class="block font-bold text-store-ink">Standard Delivery · Free</span><span class="mt-1 block text-sm text-store-muted">Delivered in 24–48 hours.</span></span>
                            </label>
                            <label class="flex cursor-pointer gap-3 rounded-control border border-store-border p-4">
                                <input type="radio" wire:model="delivery_method" value="express" class="mt-1 text-store-blue">
                                <span><span class="block font-bold text-store-ink">Express Delivery · &#2547;60</span><span class="mt-1 block text-sm text-store-muted">Priority delivery within 12–24 hours.</span></span>
                            </label>
                        </div>
                        <button type="button" wire:click="nextStep" class="mt-5 inline-flex h-11 w-full items-center justify-center rounded-control bg-store-blue text-sm font-bold text-white">Continue to Payment</button>
                    </section>
                @elseif ($step === 3)
                    <section class="rounded-card border border-store-border bg-white p-5">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-extrabold text-store-ink">Payment method</h2>
                            <button type="button" wire:click="previousStep" class="text-sm font-semibold text-store-blue">Back</button>
                        </div>
                        <div class="mt-5 space-y-3">
                            @foreach ($paymentMethods as $method => $details)
                                <label wire:key="payment-method-{{ $method }}" class="flex cursor-pointer gap-3 rounded-control border p-4 {{ $payment_method === $method ? 'border-store-blue bg-store-blue-soft' : 'border-store-border' }}">
                                    <input type="radio" wire:model="payment_method" value="{{ $method }}" class="mt-1 text-store-blue">
                                    <span><span class="block font-bold text-store-ink">{{ $details['label'] }}</span><span class="mt-1 block text-sm text-store-muted">{{ $details['description'] }}</span></span>
                                </label>
                            @endforeach
                        </div>
                        @error('payment_method')
                            <span class="mt-2 block text-xs text-red-600">{{ $message }}</span>
                        @enderror
                        <button type="button" wire:click="nextStep" class="mt-5 inline-flex h-11 w-full items-center justify-center rounded-control bg-store-blue text-sm font-bold text-white">Review Order</button>
                    </section>
                @else
                    <section class="rounded-card border border-store-border bg-white p-5">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-extrabold text-store-ink">Review your order</h2>
                            <button type="button" wire:click="previousStep" class="text-sm font-semibold text-store-blue">Back</button>
                        </div>
                        <dl class="mt-5 divide-y divide-store-border text-sm">
                            <div class="flex justify-between gap-4 py-3"><dt class="text-store-muted">Customer</dt><dd class="text-right font-semibold text-store-ink">{{ $customer_name }}<br>{{ $customer_email }}<br>{{ $customer_phone }}</dd></div>
                            <div class="flex justify-between gap-4 py-3"><dt class="text-store-muted">Address</dt><dd class="text-right font-semibold text-store-ink">{{ $address_line }}<br>{{ $city }}{{ $district ? ', '.$district : '' }}{{ $postal_code ? ' '.$postal_code : '' }}</dd></div>
                            <div class="flex justify-between gap-4 py-3"><dt class="text-store-muted">Delivery</dt><dd class="font-semibold capitalize text-store-ink">{{ $delivery_method }} · &#2547;{{ number_format($shipping / 100, 2) }}</dd></div>
                            <div class="flex justify-between gap-4 py-3"><dt class="text-store-muted">Payment</dt><dd class="font-semibold text-store-ink">{{ $paymentMethods[$payment_method]['label'] ?? $payment_method }}</dd></div>
                        </dl>
                        <button type="button" wire:click="placeOrder" wire:loading.attr="disabled" wire:target="placeOrder" class="mt-5 inline-flex h-11 w-full items-center justify-center rounded-control bg-store-red text-sm font-bold text-white disabled:cursor-wait disabled:opacity-75"><span wire:loading.remove wire:target="placeOrder">Place Order</span><span wire:loading wire:target="placeOrder">Preparing checkout...</span></button>
                    </section>
                @endif
            </section>

            <aside class="h-fit rounded-card border border-store-border bg-white p-5 lg:sticky lg:top-5">
                @if (session('payment_cancelled'))
                    <p class="mb-4 rounded-control bg-amber-50 p-3 text-sm text-amber-800">{{ session('payment_cancelled') }}</p>
                @endif
                <h2 class="text-lg font-extrabold text-store-ink">Order summary</h2>
                <div class="mt-4 space-y-3 border-b border-store-border pb-4">
                    @foreach ($items as $item)
                        <div wire:key="checkout-item-{{ $item['id'] }}" class="flex justify-between gap-3 text-sm"><span class="text-store-muted">{{ $item['name'] }} × {{ $item['quantity'] }}</span><span class="font-semibold text-store-ink">&#2547;{{ number_format($item['line_total'] / 100, 2) }}</span></div>
                    @endforeach
                </div>
                <div class="mt-4 space-y-2 text-sm"><div class="flex justify-between"><span class="text-store-muted">Subtotal</span><span>&#2547;{{ number_format($subtotal / 100, 2) }}</span></div><div class="flex justify-between"><span class="text-store-muted">Delivery</span><span>&#2547;{{ number_format($shipping / 100, 2) }}</span></div></div>
                <div class="my-4 border-t border-store-border"></div>
                <div class="flex justify-between text-lg font-extrabold text-store-ink"><span>Total</span><span>&#2547;{{ number_format(($subtotal + $shipping) / 100, 2) }}</span></div>
            </aside>
        </div>
    </x-store.ui.container>
</main>
