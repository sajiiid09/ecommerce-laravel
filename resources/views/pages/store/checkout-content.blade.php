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

        <div
            class="mt-6 grid gap-5 lg:grid-cols-[minmax(0,1fr)_360px]"
            x-data="{
                deliveryMethod: @js($delivery_method),
                districtFee: @js($districtFee),
                formatAmount(amount) {
                    return Number(amount / 100).toFixed(2);
                },
                get shippingAmount() {
                    return this.districtFee + (this.deliveryMethod === 'express' ? 6000 : 0);
                }
            }"
        >
            <section class="space-y-5">
                <div role="list" aria-label="Checkout progress" class="flex items-center gap-2 rounded-card border border-store-border bg-white p-4 text-sm">
                    @foreach (['Customer & Address', 'Delivery', 'Payment', 'Review'] as $index => $label)
                        @php($stepNumber = $index + 1)
                        @php($isCompleted = $stepNumber < $step)
                        <button
                            type="button"
                            role="listitem"
                            @if ($isCompleted) wire:click="goToStep({{ $stepNumber }})" @else disabled @endif
                            @if ($stepNumber === $step) aria-current="step" @endif
                            aria-label="{{ $label }}"
                            class="flex min-w-0 shrink-0 items-center gap-2 rounded-control focus:outline-none focus-visible:ring-2 focus-visible:ring-store-blue focus-visible:ring-offset-2 {{ $isCompleted ? 'cursor-pointer hover:text-store-blue' : 'cursor-default' }} {{ $stepNumber > $step ? 'text-store-muted' : 'text-store-ink' }} disabled:opacity-100"
                        >
                            <span class="grid size-8 shrink-0 place-items-center rounded-full {{ $stepNumber <= $step ? 'bg-store-blue text-white' : 'bg-store-soft text-store-muted' }}">{{ $stepNumber }}</span>
                            <span class="hidden font-bold sm:inline">{{ $label }}</span>
                        </button>
                        @if ($index < 3)
                            <span class="mx-1 h-px flex-1 bg-store-border"></span>
                        @endif
                    @endforeach
                </div>

                @if ($step === 1)
                    <section class="rounded-card border border-store-border bg-white p-5">
                        <h2 class="text-lg font-extrabold text-store-ink">Customer and delivery address</h2>
                        @auth
                            <div
                                class="mt-5"
                                wire:key="checkout-address-selector"
                                x-data="{
                                    addressSelectionPending: false,
                                    async selectSavedAddress(addressId) {
                                        if (this.addressSelectionPending) {
                                            return;
                                        }

                                        this.addressSelectionPending = true;

                                        try {
                                            await $wire.selectAddress(addressId);
                                        } finally {
                                            this.addressSelectionPending = false;
                                        }
                                    },
                                    async useNewAddress() {
                                        if (this.addressSelectionPending) {
                                            return;
                                        }

                                        this.addressSelectionPending = true;

                                        try {
                                            await $wire.useNewAddress();
                                        } finally {
                                            this.addressSelectionPending = false;
                                        }
                                    }
                                }"
                                x-bind:aria-busy="addressSelectionPending ? 'true' : 'false'"
                            >
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <h3 class="text-sm font-bold text-store-ink">Saved addresses</h3>
                                    <div class="flex items-center gap-3">
                                        <span wire:loading wire:target="selectAddress,useNewAddress" role="status" aria-live="polite" class="text-xs font-semibold text-store-muted">Loading address…</span>
                                        <a href="{{ route('account.addresses') }}" wire:navigate class="text-xs font-bold text-store-blue">Manage addresses</a>
                                    </div>
                                </div>
                                <div class="mt-3 grid gap-3 sm:grid-cols-2" x-bind:class="addressSelectionPending ? 'pointer-events-none opacity-60' : ''">
                                    @forelse ($savedAddresses as $savedAddress)
                                        <button
                                            type="button"
                                            wire:key="checkout-address-{{ $savedAddress->id }}"
                                            @if ($selectedAddressId === $savedAddress->id)
                                                disabled
                                                aria-pressed="true"
                                            @else
                                                x-on:click="selectSavedAddress({{ $savedAddress->id }})"
                                                x-bind:disabled="addressSelectionPending"
                                                wire:loading.attr="disabled"
                                                wire:target="selectAddress,useNewAddress"
                                                aria-pressed="false"
                                            @endif
                                            class="rounded-control border-2 p-4 text-left transition focus:outline-none focus-visible:ring-2 focus-visible:ring-store-blue focus-visible:ring-offset-2 disabled:cursor-wait disabled:opacity-60 {{ $selectedAddressId === $savedAddress->id ? 'cursor-default border-store-blue bg-store-blue-soft' : 'cursor-pointer border-store-border hover:border-store-blue' }}"
                                        >
                                            <span class="flex items-center justify-between gap-2"><span class="font-bold text-store-ink">{{ $savedAddress->label }}</span>@if($savedAddress->is_default)<span class="rounded-full bg-white px-2 py-1 text-[10px] font-bold text-store-blue">Default</span>@endif</span>
                                            <span class="mt-2 block text-sm font-semibold text-store-ink">{{ $savedAddress->recipient_name }}</span>
                                            <span class="mt-1 block truncate text-xs text-store-muted">{{ $savedAddress->address_line }}, {{ $savedAddress->city }}{{ $savedAddress->districtName() ? ', '.$savedAddress->districtName() : '' }}</span>
                                        </button>
                                    @empty
                                        <p class="text-sm text-store-muted sm:col-span-2">You have no saved addresses yet.</p>
                                    @endforelse
                                    <button
                                        type="button"
                                        wire:key="checkout-address-new"
                                        @if ($selectedAddressId === null)
                                            disabled
                                            aria-pressed="true"
                                        @else
                                            x-on:click="useNewAddress()"
                                            x-bind:disabled="addressSelectionPending"
                                            wire:loading.attr="disabled"
                                            wire:target="selectAddress,useNewAddress"
                                            aria-pressed="false"
                                        @endif
                                        class="rounded-control border-2 border-dashed p-4 text-left transition focus:outline-none focus-visible:ring-2 focus-visible:ring-store-blue focus-visible:ring-offset-2 disabled:cursor-wait disabled:opacity-60 {{ $selectedAddressId === null ? 'cursor-default border-store-blue bg-store-blue-soft' : 'cursor-pointer border-store-border hover:border-store-blue' }}"
                                    ><span class="block font-bold text-store-ink">Use a new address</span><span class="mt-1 block text-xs text-store-muted">Enter a one-time shipping address.</span></button>
                                </div>
                                @error('selectedAddressId') <span class="mt-2 block text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                        @endauth

                        @if (! auth()->check() || $selectedAddressId === null)
                            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                                @foreach ([['customer_name', 'Full name', 'text', true], ['customer_email', 'Email', 'email', false], ['customer_phone', 'Phone', 'text', true], ['city', 'City / Area', 'text', true], ['postal_code', 'Postal code', 'text', false]] as [$field, $label, $type, $isRequired])
                                    <label class="text-sm font-semibold text-store-ink">
                                        {{ $label }}@if ($isRequired) <span class="text-red-600" aria-hidden="true">*</span>@endif
                                        <input type="{{ $type }}" wire:model="{{ $field }}" @if ($isRequired) required @endif class="mt-2 h-11 w-full rounded-control border border-store-border px-3">
                                        @error($field)
                                            <span class="mt-1 block text-xs text-red-600">{{ $message }}</span>
                                        @enderror
                                    </label>
                                @endforeach
                                <label class="text-sm font-semibold text-store-ink">
                                    District <span class="text-red-600" aria-hidden="true">*</span>
                                    <x-ui.select wire:model.live.debounce.300ms="districtId" wire:loading.class="pointer-events-none opacity-60" wire:target="districtId" placeholder="Select district" searchable :preventLoading="true" :invalid="$errors->has('districtId')" class="mt-2 w-full">
                                        <x-ui.select.option wire:key="checkout-district-empty" value="">Select district</x-ui.select.option>
                                        @foreach ($districts as $district)
                                            <x-ui.select.option wire:key="checkout-district-{{ $district->id }}" value="{{ $district->id }}">{{ $district->name }}</x-ui.select.option>
                                        @endforeach
                                    </x-ui.select>
                                    @error('districtId')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                                </label>
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
                                    <p class="mt-1">{{ $selectedAddress->address_line }}, {{ $selectedAddress->city }}{{ $selectedAddress->districtName() ? ', '.$selectedAddress->districtName() : '' }}{{ $selectedAddress->postal_code ? ' '.$selectedAddress->postal_code : '' }}</p>
                                </div>
                            @endif
                        @endif
                        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <a href="{{ route('store.cart') }}" wire:navigate class="inline-flex h-11 items-center justify-center rounded-control border border-store-border px-5 text-sm font-bold text-store-ink transition hover:border-store-blue hover:text-store-blue focus:outline-none focus-visible:ring-2 focus-visible:ring-store-blue focus-visible:ring-offset-2">Back to Cart</a>
                            <button type="button" wire:click="nextStep" class="inline-flex h-11 items-center justify-center rounded-control bg-store-blue px-5 text-sm font-bold text-white transition hover:bg-store-blue/90 focus:outline-none focus-visible:ring-2 focus-visible:ring-store-blue focus-visible:ring-offset-2 sm:min-w-56">Continue to Delivery</button>
                        </div>
                    </section>
                @elseif ($step === 2)
                    <section class="rounded-card border border-store-border bg-white p-5">
                        <h2 class="text-lg font-extrabold text-store-ink">Delivery method</h2>
                        <div class="mt-5" wire:key="checkout-delivery-method">
                            <p class="sr-only" aria-live="polite" x-text="`${deliveryMethod === 'express' ? 'Express' : 'Standard'} delivery selected`"></p>
                            <div class="space-y-3">
                                <label class="flex cursor-pointer gap-3 rounded-control border border-store-border bg-white p-4 transition has-[:checked]:border-2 has-[:checked]:border-store-blue has-[:checked]:bg-store-blue-soft">
                                    <input type="radio" name="delivery_method" value="standard" wire:model="delivery_method" x-on:change="deliveryMethod = $event.target.value" aria-label="Standard Delivery" class="mt-1 text-store-blue">
                                    <span><span class="block font-bold text-store-ink">Standard Delivery · ৳{{ number_format($districtFee / 100, 2) }}</span><span class="mt-1 block text-sm text-store-muted">Delivered in 24–48 hours.</span></span>
                                </label>
                                <label class="flex cursor-pointer gap-3 rounded-control border border-store-border bg-white p-4 transition has-[:checked]:border-2 has-[:checked]:border-store-blue has-[:checked]:bg-store-blue-soft">
                                    <input type="radio" name="delivery_method" value="express" wire:model="delivery_method" x-on:change="deliveryMethod = $event.target.value" aria-label="Express Delivery" class="mt-1 text-store-blue">
                                    <span><span class="block font-bold text-store-ink">Express Delivery · ৳{{ number_format(($districtFee + 6000) / 100, 2) }}</span><span class="mt-1 block text-sm text-store-muted">Priority delivery within 12–24 hours.</span></span>
                                </label>
                            </div>
                        </div>
                        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <button type="button" wire:click="previousStep" class="inline-flex h-11 items-center justify-center rounded-control border border-store-border px-5 text-sm font-bold text-store-ink transition hover:border-store-blue hover:text-store-blue focus:outline-none focus-visible:ring-2 focus-visible:ring-store-blue focus-visible:ring-offset-2">Back</button>
                            <button type="button" wire:click="nextStep" class="inline-flex h-11 items-center justify-center rounded-control bg-store-blue px-5 text-sm font-bold text-white transition hover:bg-store-blue/90 focus:outline-none focus-visible:ring-2 focus-visible:ring-store-blue focus-visible:ring-offset-2 sm:min-w-56">Continue to Payment</button>
                        </div>
                    </section>
                @elseif ($step === 3)
                    <section class="rounded-card border border-store-border bg-white p-5">
                        <h2 class="text-lg font-extrabold text-store-ink">Payment method</h2>
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
                        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <button type="button" wire:click="previousStep" class="inline-flex h-11 items-center justify-center rounded-control border border-store-border px-5 text-sm font-bold text-store-ink transition hover:border-store-blue hover:text-store-blue focus:outline-none focus-visible:ring-2 focus-visible:ring-store-blue focus-visible:ring-offset-2">Back</button>
                            <button type="button" wire:click="nextStep" class="inline-flex h-11 items-center justify-center rounded-control bg-store-blue px-5 text-sm font-bold text-white transition hover:bg-store-blue/90 focus:outline-none focus-visible:ring-2 focus-visible:ring-store-blue focus-visible:ring-offset-2 sm:min-w-56">Continue to Review</button>
                        </div>
                    </section>
                @else
                    <section class="rounded-card border border-store-border bg-white p-5">
                        <h2 class="text-lg font-extrabold text-store-ink">Review your order</h2>
                        <dl class="mt-5 divide-y divide-store-border text-sm">
                            <div class="flex justify-between gap-4 py-3"><dt class="text-store-muted">Customer</dt><dd class="text-right font-semibold text-store-ink">{{ $customer_name }}<br>{{ $customer_email }}<br>{{ $customer_phone }}</dd></div>
                            <div class="flex justify-between gap-4 py-3"><dt class="text-store-muted">Address</dt><dd class="text-right font-semibold text-store-ink">{{ $address_line }}<br>{{ $city }}{{ $district ? ', '.$district : '' }}{{ $postal_code ? ' '.$postal_code : '' }}</dd></div>
                            <div class="flex justify-between gap-4 py-3"><dt class="text-store-muted">Delivery</dt><dd class="font-semibold capitalize text-store-ink">{{ $delivery_method }} · &#2547;{{ number_format($shipping / 100, 2) }}</dd></div>
                            <div class="flex justify-between gap-4 py-3"><dt class="text-store-muted">Payment</dt><dd class="font-semibold text-store-ink">{{ $paymentMethods[$payment_method]['label'] ?? $payment_method }}</dd></div>
                        </dl>
                        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <button type="button" wire:click="previousStep" class="inline-flex h-11 items-center justify-center rounded-control border border-store-border px-5 text-sm font-bold text-store-ink transition hover:border-store-blue hover:text-store-blue focus:outline-none focus-visible:ring-2 focus-visible:ring-store-blue focus-visible:ring-offset-2">Back</button>
                            <button type="button" wire:click="placeOrder" wire:loading.attr="disabled" wire:target="placeOrder" class="inline-flex h-11 items-center justify-center rounded-control bg-store-red px-5 text-sm font-bold text-white transition hover:bg-store-red/90 focus:outline-none focus-visible:ring-2 focus-visible:ring-store-red focus-visible:ring-offset-2 disabled:cursor-wait disabled:opacity-75 sm:min-w-56"><span wire:loading.remove wire:target="placeOrder">Place Order</span><span wire:loading wire:target="placeOrder">Preparing checkout...</span></button>
                        </div>
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
                        <div wire:key="checkout-item-{{ $item['id'] }}" class="flex items-center justify-between gap-3 text-sm"><div class="flex min-w-0 items-center gap-3"><img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" loading="lazy" decoding="async" class="size-12 shrink-0 rounded-control border border-store-border object-contain"><div class="min-w-0"><span class="block truncate text-store-muted">{{ $item['name'] }} × {{ $item['quantity'] }}</span>@if(($item['old_price'] ?? null) > $item['price'])<span class="text-xs text-store-muted line-through">৳{{ number_format($item['old_price'] / 100, 2) }}</span>@endif</div></div><div class="shrink-0 text-right"><span class="block font-semibold text-store-ink">৳{{ number_format($item['line_total'] / 100, 2) }}</span>@if(($item['old_price'] ?? null) > $item['price'])<span class="text-xs font-semibold text-store-success">Sale</span>@endif</div></div>
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
                <div class="mt-4 space-y-2 text-sm"><div class="flex justify-between"><span class="text-store-muted">Subtotal</span><span>&#2547;{{ number_format($subtotal / 100, 2) }}</span></div><div class="flex justify-between"><span class="text-store-muted">Delivery</span><span x-text="`৳${formatAmount(shippingAmount)}`">&#2547;{{ number_format($shipping / 100, 2) }}</span></div>@if($discount > 0)<div class="flex justify-between font-semibold text-emerald-600"><span>Discount{{ $couponQuote ? ' ('.$couponQuote['code'].')' : '' }}</span><span>-&#2547;{{ number_format($discount / 100, 2) }}</span></div>@endif</div>
                <div class="my-4 border-t border-store-border"></div>
                <div class="flex justify-between text-lg font-extrabold text-store-ink"><span>Total</span><span x-text="`৳${formatAmount({{ $subtotal - $discount }} + shippingAmount)}`">&#2547;{{ number_format(($subtotal + $shipping - $discount) / 100, 2) }}</span></div>
            </aside>
        </div>
    </x-store.ui.container>
</main>
