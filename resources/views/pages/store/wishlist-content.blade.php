<main class="bg-store-soft py-6 sm:py-8">
    <x-store.ui.container>
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-store-ink sm:text-3xl">My Wishlist <span
                        class="text-lg font-medium text-store-muted" x-text="`(${wishlist.length} items)`"></span></h1>
                <p class="mt-1 text-sm text-store-muted">Save your favorite products and shop them anytime.</p>
            </div><button type="button"
                class="h-10 rounded-control border border-store-blue bg-white px-4 text-sm font-bold text-store-blue disabled:cursor-not-allowed disabled:opacity-50"
                @click="wishlist.forEach((id) => { const product = @js($products).find((item) => item.id === id); if (product) addToCart(product); }); wishlist = []"
                :disabled="wishlist.length === 0">Move All to Cart</button>
        </div>
        <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
            @foreach ($products as $product)
                <div x-show="wishlist.includes({{ $product['id'] }})"><x-store.catalog.product-card :product="$product"
                        compact /></div>
            @endforeach
        </div>
        <div x-cloak x-show="wishlist.length === 0"
            class="mt-6 rounded-card border border-store-border bg-white p-12 text-center"><x-ui.icon name="heart"
                class="mx-auto size-10 !text-store-placeholder" />
            <h2 class="mt-4 text-lg font-bold text-store-ink">Your wishlist is empty</h2><a
                href="{{ route('store.category') }}" wire:navigate
                class="mt-5 inline-flex h-10 items-center rounded-control bg-store-blue px-4 text-sm font-bold text-white">Browse
                products</a>
        </div>
    </x-store.ui.container>
</main>
