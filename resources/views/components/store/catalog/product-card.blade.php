@props(['product', 'compact' => false])

<article class="group relative flex min-h-full flex-col overflow-hidden rounded-card border border-store-border bg-white transition duration-200 hover:-translate-y-0.5 hover:shadow-store-soft">
    <div class="relative {{ $compact ? 'p-2 sm:p-3' : 'p-4' }}">
        <x-store.ui.discount-badge :discount="$product['discount']" class="absolute left-2 top-2 z-10" />
        <button type="button" class="absolute right-2 top-2 z-10 grid size-9 place-items-center rounded-full bg-white/90 text-store-muted transition hover:text-store-red" @click="toggleWishlist({{ $product['id'] }})" :aria-label="wishlist.includes({{ $product['id'] }}) ? 'Remove from wishlist' : 'Add to wishlist'" :class="wishlist.includes({{ $product['id'] }}) && 'text-store-red'"><x-ui.icon name="heart" class="size-5 !text-current" /><span class="sr-only" x-text="wishlist.includes({{ $product['id'] }}) ? 'Saved' : 'Save'" /></button>
        <a href="{{ route('store.product', ['slug' => $product['slug']]) }}" wire:navigate class="block aspect-square"><img src="{{ asset(ltrim($product['image'], '/')) }}" alt="{{ $product['name'] }}" loading="lazy" class="size-full object-contain transition duration-200 group-hover:scale-[1.03]"></a>
    </div>
    <div class="flex flex-1 flex-col px-3 pb-3 sm:px-4 sm:pb-4">
        <p class="text-[11px] text-store-muted">{{ $product['brand'] }}</p>
        <a href="{{ route('store.product', ['slug' => $product['slug']]) }}" wire:navigate class="mt-1 line-clamp-2 min-h-10 text-sm font-semibold leading-5 text-store-ink hover:text-store-blue">{{ $product['name'] }}</a>
        <x-store.ui.price :price="$product['price']" :old-price="$product['oldPrice']" size="sm" class="mt-2" />
        <x-store.ui.rating :rating="$product['rating']" :reviews="$product['reviews']" class="mt-1" />
        <button type="button" class="mt-3 inline-flex h-10 w-full items-center justify-center gap-2 rounded-control bg-store-blue px-3 text-sm font-bold text-white transition hover:bg-store-blue-dark" @click="addToCart(@js($product))"><x-ui.icon name="shopping-cart" class="size-4 !text-white" />Add to Cart</button>
    </div>
</article>
