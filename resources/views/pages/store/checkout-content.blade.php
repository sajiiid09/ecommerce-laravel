<main class="bg-store-soft py-6 sm:py-8">
    <x-store.ui.container>
        <nav aria-label="Breadcrumb" class="mb-5 text-xs text-store-muted">
            <x-ui.breadcrumbs class="flex flex-wrap items-center gap-2">
                <x-ui.breadcrumbs.item href="{{ route('store.home') }}" wire:navigate class="!text-xs !text-store-muted hover:!text-store-blue">Home</x-ui.breadcrumbs.item>
                <x-ui.breadcrumbs.item href="{{ route('store.cart') }}" wire:navigate class="!text-xs !text-store-muted hover:!text-store-blue">Cart</x-ui.breadcrumbs.item>
                <x-ui.breadcrumbs.item aria-current="page" class="!text-xs !font-medium !text-store-ink">Checkout</x-ui.breadcrumbs.item>
            </x-ui.breadcrumbs>
        </nav>
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
                        @auth
                            <div class="mt-5">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <h3 class="text-sm font-bold text-store-ink">Saved addresses</h3>
                                    <a href="{{ route('account.addresses') }}" wire:navigate class="text-xs font-bold text-store-blue">Manage addresses</a>
                                </div>
                                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                    @forelse ($savedAddresses as $savedAddress)
                                        <button type="button" wire:key="checkout-address-{{ $savedAddress->id }}" wire:click="selectAddress({{ $savedAddress->id }})" class="rounded-control border-2 p-4 text-left {{ $selectedAddressId === $savedAddress->id ? 'border-store-blue bg-store-blue-soft' : 'border-store-border' }}">
                                            <span class="flex items-center justify-between gap-2"><span class="font-bold text-store-ink">{{ $savedAddress->label }}</span>@if($savedAddress->is_default)<span class="rounded-full bg-white px-2 py-1 text-[10px] font-bold text-store-blue">Default</span>@endif</span>
                                            <span class="mt-2 block text-sm font-semibold text-store-ink">{{ $savedAddress->recipient_name }}</span>
                                            <span class="mt-1 block truncate text-xs text-store-muted">{{ $savedAddress->address_line }}, {{ $savedAddress->city }}</span>
                                        </button>
                                    @empty
                                        <p class="text-sm text-store-muted sm:col-span-2">You have no saved addresses yet.</p>
                                    @endforelse
                                    <button type="button" wire:click="useNewAddress" class="rounded-control border-2 border-dashed p-4 text-left {{ $selectedAddressId === null ? 'border-store-blue bg-store-blue-soft' : 'border-store-border' }}"><span class="block font-bold text-store-ink">Use a new address</span><span class="mt-1 block text-xs text-store-muted">Enter a one-time shipping address.</span></button>
                                </div>
                                @error('selectedAddressId') <span class="mt-2 block text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                        @endauth

                        @if (! auth()->check() || $selectedAddressId === null)
                            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                                @foreach ([['customer_name', 'Full name', 'text', true], ['customer_email', 'Email', 'email', false], ['customer_phone', 'Phone', 'text', true], ['city', 'City / Area', 'text', true], ['district', 'District', 'text', true], ['postal_code', 'Postal code', 'text', false]] as [$field, $label, $type, $isRequired])
                                    <label class="text-sm font-semibold text-store-ink">
                                        {{ $label }}@if ($isRequired) <span class="text-red-600" aria-hidden="true">*</span>@endif
                                        <input type="{{ $type }}" wire:model="{{ $field }}" @if ($isRequired) required @endif class="mt-2 h-11 w-full rounded-control border border-store-border px-3">
                                        @error($field)
                                            <span class="mt-1 block text-xs text-red-600">{{ $message }}</span>
                                        @enderror
                                    </label>
                                @endforeach
                                <label class="text-sm font-semibold text-store-ink sm:col-span-2">
                                    Address <span class="text-red-600" aria-hidden="true">*</span>
                                    <input wire:model="address_line" required class="mt-2 h-11 w-full rounded-control border border-store-border px-3">
                                    @error('address_line')
                                        <span class="mt-1 block text-xs text-red-600">{{ $message }}</span>
                                    @enderror
                                </label>
                            </div>
                        @else
                            @php($selectedAddress = $savedAddresses->firstWhere('id', $selectedAddressId))
                            @if ($selectedAddress)
                                <div class="mt-5 rounded-control border border-store-blue bg-store-blue-soft p-4 text-sm text-store-ink">
                                    <p class="font-bold">Shipping to {{ $selectedAddress->label }}</p>
                                    <p class="mt-1">{{ $selectedAddress->recipient_name }} · {{ $selectedAddress->phone }}</p>
                                    <p class="mt-1">{{ $selectedAddress->address_line }}, {{ $selectedAddress->city }}{{ $selectedAddress->district ? ', '.$selectedAddress->district : '' }}{{ $selectedAddress->postal_code ? ' '.$selectedAddress->postal_code : '' }}</p>
                                </div>
                            @endif
                        @endif
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
                        <div wire:key="checkout-item-{{ $item['id'] }}" class="flex items-center justify-between gap-3 text-sm"><div class="flex min-w-0 items-center gap-3"><img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" loading="lazy" decoding="async" class="size-12 shrink-0 rounded-control border border-store-border object-contain"><span class="min-w-0 text-store-muted">{{ $item['name'] }} × {{ $item['quantity'] }}</span></div><span class="shrink-0 font-semibold text-store-ink">&#2547;{{ number_format($item['line_total'] / 100, 2) }}</span></div>
                    @endforeach
                </div>
                <form wire:submit="applyCoupon" class="mt-4">
                    <label class="text-sm font-semibold text-store-ink">Coupon code</label>
                    <div class="mt-2 flex gap-2">
                        <input wire:model="couponCode" type="text" maxlength="50" placeholder="Enter coupon code" class="h-10 min-w-0 flex-1 rounded-control border border-store-border px-3 text-sm uppercase">
                        @if($couponQuote)
                            <button type="button" wire:click="removeCoupon" class="h-10 rounded-control border border-store-border px-3 text-sm font-semibold text-store-muted">Remove</button>
                        @else
                            <button type="submit" wire:loading.attr="disabled" wire:target="applyCoupon" class="h-10 rounded-control bg-store-blue px-4 text-sm font-bold text-white disabled:opacity-60">Apply</button>
                        @endif
                    </div>
                    @error('couponCode') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                    @if($couponQuote) <p class="mt-1 text-xs font-semibold text-emerald-600">{{ $couponQuote['code'] }} applied.</p> @endif
                </form>
                <div class="mt-4 space-y-2 text-sm"><div class="flex justify-between"><span class="text-store-muted">Subtotal</span><span>&#2547;{{ number_format($subtotal / 100, 2) }}</span></div><div class="flex justify-between"><span class="text-store-muted">Delivery</span><span>&#2547;{{ number_format($shipping / 100, 2) }}</span></div>@if($discount > 0)<div class="flex justify-between font-semibold text-emerald-600"><span>Discount{{ $couponQuote ? ' ('.$couponQuote['code'].')' : '' }}</span><span>-&#2547;{{ number_format($discount / 100, 2) }}</span></div>@endif</div>
                <div class="my-4 border-t border-store-border"></div>
                <div class="flex justify-between text-lg font-extrabold text-store-ink"><span>Total</span><span>&#2547;{{ number_format(($subtotal + $shipping - $discount) / 100, 2) }}</span></div>
            </aside>
        </div>
    </x-store.ui.container>
</main>
