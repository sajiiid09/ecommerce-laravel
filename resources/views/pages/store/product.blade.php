@php
    $related = $related ?? collect();
    $options = $product['options'] ?? [];
    $gallery = $product['gallery'] ?? [$product['image']];
    $imageUrl = fn (string $image): string => str($image)->startsWith(['http://', 'https://', '/']) ? $image : asset(ltrim($image, '/'));
@endphp

<main
    x-data="{
        quantity: 1,
        selectedImage: @js($gallery[0]),
        variants: @js($product['variants'] ?? []),
        selectedVariantId: @js($product['variantId'] ?? null),
        selectedOptions: Object.fromEntries(@js($options).map((option) => [option.name, option.values[0]?.value ?? null])),
        get variant() {
            const selected = this.variants.find((variant) => variant.id === this.selectedVariantId);
            const matching = this.variants.find((variant) => variant.optionValues.every((optionValue) => this.selectedOptions[optionValue.option] === optionValue.value));
            return matching || selected || this.variants[0] || { id: null, price: @js($product['price']), compareAtPrice: @js($product['oldPrice']), available: @js($product['inStock']), gallery: [] };
        },
        selectOption(name, value) {
            this.selectedOptions[name] = value;
            this.selectedVariantId = this.variant.id;
            if (this.variant.gallery?.length) this.selectedImage = this.variant.gallery[0];
        },
        formatMoney(value) {
            return '৳' + new Intl.NumberFormat().format(Math.round((value || 0) / 100));
        }
    }"
    class="bg-white py-6 sm:py-8"
>
    <x-store.ui.container>
        <nav aria-label="Breadcrumb" class="mb-5 text-xs text-store-muted">
            <x-ui.breadcrumbs class="flex flex-wrap items-center gap-2">
                <x-ui.breadcrumbs.item href="{{ route('store.home') }}" wire:navigate class="!text-xs !text-store-muted hover:!text-store-blue">Home</x-ui.breadcrumbs.item>
                <x-ui.breadcrumbs.item href="{{ route('store.category') }}" wire:navigate class="!text-xs !text-store-muted hover:!text-store-blue">All Products</x-ui.breadcrumbs.item>
                <x-ui.breadcrumbs.item aria-current="page" class="!text-xs !font-medium !text-store-ink">{{ $product['name'] }}</x-ui.breadcrumbs.item>
            </x-ui.breadcrumbs>
        </nav>

        <div class="grid gap-8 lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">
            <section class="grid gap-3 sm:grid-cols-[88px_minmax(0,1fr)]">
                <div class="order-2 flex gap-2 overflow-x-auto sm:order-1 sm:flex-col">
                    @foreach ($gallery as $image)
                        <button type="button" class="size-20 shrink-0 rounded-control border bg-white p-2"
                            :class="selectedImage === @js($image) ? 'border-2 border-store-blue' : 'border-store-border'"
                            @click="selectedImage = @js($image)"
                            aria-label="Show image {{ $loop->iteration }} of {{ count($gallery) }}">
                            <img src="{{ $imageUrl($image) }}" alt="{{ $product['name'] }}" loading="lazy" decoding="async" class="size-full object-contain">
                        </button>
                    @endforeach
                </div>
                <div class="order-1 flex aspect-square items-center justify-center rounded-card border border-store-border bg-white p-8 sm:order-2">
                    <img :src="selectedImage" src="{{ $imageUrl($gallery[0]) }}" alt="{{ $product['name'] }}" loading="eager" fetchpriority="high" decoding="async" class="size-full object-contain">
                </div>
            </section>

            <section>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-store-blue">{{ $product['brand'] }}</p>
                        <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-store-ink sm:text-3xl">{{ $product['name'] }}</h1>
                    </div>
                    <button type="button" class="grid size-11 shrink-0 place-items-center rounded-full border border-store-border text-store-muted hover:border-store-red hover:text-store-red" @click="toggleWishlist({{ $product['id'] }})" :class="wishlist.includes({{ $product['id'] }}) && 'text-store-red'" :aria-label="wishlist.includes({{ $product['id'] }}) ? 'Remove from wishlist' : 'Add to wishlist'">
                        <x-ui.icon name="heart" class="size-5 !text-current" />
                    </button>
                </div>

                <div class="mt-3 flex flex-wrap items-center gap-3">
                    <x-store.ui.rating :rating="$product['rating']" :reviews="$product['reviews']" />
                    <span class="text-store-border">|</span>
                    <span class="text-sm text-store-muted">SKU: {{ $product['sku'] ?? '—' }}</span>
                </div>

                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <span class="text-2xl font-black text-store-red" x-text="formatMoney(variant.price)">{{ '৳'.number_format($product['price'] / 100, 2) }}</span>
                    <span x-show="variant.compareAtPrice" x-text="formatMoney(variant.compareAtPrice)" class="text-sm text-store-muted line-through">{{ $product['oldPrice'] ? '৳'.number_format($product['oldPrice'] / 100, 2) : '' }}</span>
                </div>

                <p x-show="variant.compareAtPrice > variant.price" class="mt-2 flex items-center gap-1 text-sm font-semibold text-store-success">
                    <x-ui.icon name="check-circle" variant="solid" class="size-4 !text-current" />
                    Save <span x-text="formatMoney(variant.compareAtPrice - variant.price)"></span> on this product
                </p>

                <div class="my-5 border-t border-store-border"></div>

                <p class="text-sm font-semibold text-store-ink">Availability:
                    <span class="ml-2 inline-flex items-center gap-1 font-medium" :class="variant.available ? 'text-store-success' : 'text-store-error'">
                        <span class="size-2 rounded-full" :class="variant.available ? 'bg-store-success' : 'bg-store-error'"></span>
                        <span x-text="variant.available ? 'In Stock' : 'Out of Stock'">In Stock</span>
                    </span>
                </p>

                @if ($product['shortDescription'] ?? null)
                    <p class="mt-4 text-sm leading-6 text-store-text">{{ $product['shortDescription'] }}</p>
                @endif

                @foreach ($options as $option)
                    <div class="mt-5">
                        <p class="text-sm font-semibold text-store-ink">{{ $option['label'] }}:
                            <span class="ml-2 font-normal text-store-muted" x-text="selectedOptions['{{ $option['name'] }}']"></span>
                        </p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($option['values'] as $value)
                                <button type="button" class="rounded-full border px-4 py-2 text-sm"
                                    :class="selectedOptions['{{ $option['name'] }}'] === @js($value['value']) ? 'border-store-blue bg-store-blue-soft font-bold text-store-blue' : 'border-store-border'"
                                    @click="selectOption(@js($option['name']), @js($value['value']))">
                                    {{ $value['label'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <span class="text-sm font-semibold text-store-ink">Quantity:</span>
                    <x-store.ui.quantity-stepper model="quantity" />
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <button type="button" class="inline-flex h-12 items-center justify-center gap-2 rounded-control bg-store-blue text-sm font-bold text-white disabled:opacity-50" :disabled="!variant.available" x-on:click="$wire.addToCart(variant.id, quantity)">
                        <x-ui.icon name="shopping-cart" class="size-5 !text-white" />Add to Cart
                    </button>
                    <button type="button" class="inline-flex h-12 items-center justify-center gap-2 rounded-control bg-store-red text-sm font-bold text-white disabled:opacity-50" :disabled="!variant.available">
                        <x-ui.icon name="bolt" variant="solid" class="size-5 !text-white" />Buy Now
                    </button>
                </div>
            </section>
        </div>

        <section class="mt-8 rounded-card border border-store-border bg-white">
            <div class="border-b border-store-border px-5">
                <h2 class="py-4 text-sm font-bold text-store-blue">Description & Specifications</h2>
            </div>
            <div class="grid gap-6 p-5 text-sm leading-7 text-store-text lg:grid-cols-[1.3fr_1fr]">
                <div>
                    @if (! empty($product['descriptionHtml']))
                        <div class="prose prose-sm max-w-none">{!! $product['descriptionHtml'] !!}</div>
                    @elseif ($product['shortDescription'] ?? null)
                        <p>{{ $product['shortDescription'] }}</p>
                    @else
                        <p>{{ $product['name'] }} is carefully selected from trusted suppliers to bring you dependable quality and excellent value.</p>
                    @endif
                </div>
                <dl class="divide-y divide-store-border rounded-control border border-store-border">
                    <div class="flex justify-between gap-4 px-3 py-2"><dt class="font-semibold">Brand</dt><dd>{{ $product['brand'] }}</dd></div>
                    <div class="flex justify-between gap-4 px-3 py-2"><dt class="font-semibold">Categories</dt><dd>{{ implode(', ', $product['categories'] ?? []) ?: '—' }}</dd></div>
                    @foreach ($product['attributes'] ?? [] as $attribute)
                        <div class="flex justify-between gap-4 px-3 py-2"><dt class="font-semibold">{{ $attribute['name'] }}</dt><dd>{{ $attribute['value'] }}{{ $attribute['unit'] ? ' '.$attribute['unit'] : '' }}</dd></div>
                    @endforeach
                    <div class="flex justify-between gap-4 px-3 py-2"><dt class="font-semibold">SKU</dt><dd>{{ $product['sku'] ?? '—' }}</dd></div>
                </dl>
            </div>
        </section>

        @if (config('features.reviews'))
            <livewire:components.store.product-reviews :product-id="$product['id']" :lazy="false" />
        @endif

        <section class="mt-8">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-extrabold tracking-tight text-store-ink">You May Also Like</h2>
                <a href="{{ route('store.category') }}" wire:navigate class="text-sm font-semibold text-store-blue">View All →</a>
            </div>
            <livewire:components.store.related-products
                :product-id="$product['id']"
                :category-slug="$product['categorySlug'] ?? null"
                :lazy="false"
            />
        </section>
    </x-store.ui.container>
</main>
