<div id="cart-drawer" x-cloak x-show="cartOpen" x-transition.opacity class="fixed inset-0 z-50" aria-live="polite">
    <div x-show="cartOpen" x-transition.opacity class="absolute inset-0 bg-store-ink/40" @click="closeCart()" aria-hidden="true"></div>

    <aside
        x-ref="cartPanel"
        x-show="cartOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="absolute inset-y-0 right-0 flex h-full w-[min(100%,28rem)] flex-col bg-white shadow-2xl"
        role="dialog"
        aria-modal="true"
        aria-labelledby="cart-drawer-title"
        :aria-busy="(!cartLoaded).toString()"
        @click.stop
        @keydown.escape.stop.prevent="closeCart()"
        @keydown.tab="trapCartFocus($event)"
    >
        <div class="flex items-center justify-between border-b border-store-border px-5 py-4">
            <div>
                <h2 id="cart-drawer-title" class="text-lg font-extrabold text-store-ink">Your Cart</h2>
                <p x-show="!cartLoaded" class="sr-only" aria-live="polite">Loading your cart</p>
                <p x-show="cartLoaded" class="sr-only" aria-live="polite">
                    <span x-text="`${cart.reduce((total, item) => total + item.quantity, 0)} items in your cart`"></span>
                </p>
            </div>
            <button
                type="button"
                x-ref="cartClose"
                @click="closeCart()"
                class="grid size-11 place-items-center rounded-control text-store-muted hover:bg-store-soft hover:text-store-ink"
                aria-label="Close cart"
            >
                <x-ui.icon name="x-mark" class="size-6 !text-current" />
            </button>
        </div>

        <div x-show="!cartLoaded" class="flex min-h-0 flex-1 flex-col overflow-y-auto p-5" aria-label="Loading shopping cart">
            <div class="space-y-4">
                @foreach (range(1, 3) as $item)
                    <div class="flex gap-3 border-b border-store-border py-4">
                        <x-ui.skeleton class="size-16 shrink-0 rounded-control" />
                        <div class="min-w-0 flex-1 space-y-2">
                            <x-ui.skeleton class="h-4 w-4/5" />
                            <x-ui.skeleton class="h-4 w-2/5" />
                            <x-ui.skeleton class="h-8 w-full" />
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div x-show="cartLoaded" class="min-h-0 flex-1 overflow-y-auto px-5" aria-label="Shopping cart contents">
            @forelse($items as $item)
                <article wire:key="drawer-item-{{ $item['id'] }}" class="flex gap-3 border-b border-store-border py-4">
                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" loading="lazy" decoding="async" class="size-16 rounded-control border border-store-border object-contain">
                    <div class="min-w-0 flex-1">
                        <p class="line-clamp-2 text-sm font-bold text-store-ink">{{ $item['name'] }}</p>
                        <div class="mt-1 flex flex-wrap items-baseline gap-2"><span class="text-sm font-extrabold text-store-red">৳{{ number_format($item['price'] / 100, 2) }}</span>@if(($item['old_price'] ?? null) > $item['price'])<span class="text-xs font-medium text-store-muted line-through">৳{{ number_format($item['old_price'] / 100, 2) }}</span>@endif</div>
                        <div class="mt-2 flex items-center justify-between">
                            <div class="inline-flex h-8 items-center rounded-control border border-store-border">
                                <button type="button" wire:click="updateItem({{ $item['id'] }}, {{ max(1, $item['quantity'] - 1) }}, 'decrease')" class="grid size-8 place-items-center text-store-red transition hover:bg-red-50" aria-label="Decrease quantity">−</button>
                                <span class="w-7 text-center text-sm font-semibold">{{ $item['quantity'] }}</span>
                                <button type="button" wire:click="updateItem({{ $item['id'] }}, {{ $item['quantity'] + 1 }}, 'increase')" class="grid size-8 place-items-center" aria-label="Increase quantity">+</button>
                            </div>
                            <button type="button" wire:click="removeItem({{ $item['id'] }})" class="grid size-8 place-items-center rounded-control text-store-muted transition hover:bg-red-50 hover:text-store-red focus:outline-none focus:ring-2 focus:ring-store-red/30" aria-label="Remove {{ $item['name'] }} from cart" title="Remove {{ $item['name'] }} from cart"><x-ui.icon name="ps:trash" variant="regular" class="size-5 !text-current" /></button>
                        </div>
                    </div>
                </article>
            @empty
                <div class="py-12 text-center text-sm text-store-muted">Your cart is empty.</div>
            @endforelse
        </div>

        <div x-show="cartLoaded" class="border-t border-store-border bg-white p-5">
            <div class="flex items-center justify-between text-base font-bold text-store-ink">
                <span>Subtotal</span>
                <span>৳{{ number_format($subtotal / 100, 2) }}</span>
            </div>
            <p class="mt-1 text-xs text-store-muted">Delivery charges calculated at checkout.</p>
            <div class="mt-4 grid grid-cols-2 gap-3">
                <a href="{{ route('store.cart') }}" wire:navigate @click="closeCart()" class="inline-flex h-11 items-center justify-center rounded-control border border-store-blue text-sm font-bold text-store-blue">View Cart</a>
                <a href="{{ route('store.checkout') }}" wire:navigate @click="closeCart()" class="inline-flex h-11 items-center justify-center rounded-control bg-store-red text-sm font-bold text-white">Checkout</a>
            </div>
        </div>
    </aside>
</div>
