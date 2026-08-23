@props(['categories' => []])

<nav class="hidden bg-store-blue text-white lg:block" aria-label="Product categories">
    <x-store.ui.container class="flex h-10 items-stretch">
        <a href="#categories" class="flex w-36 items-center gap-2 bg-store-blue-dark px-3 text-xs font-bold"><x-ui.icon name="bars-3" class="size-4 !text-white" />All Categories</a>
        <div class="flex flex-1 items-center justify-around">
            @foreach ($categories as $category)
                <a href="{{ route('store.category', ['slug' => $category['slug']]) }}" wire:navigate class="px-3 text-xs font-semibold hover:text-white/75">{{ $category['name'] }}</a>
            @endforeach
        </div>
        <a href="{{ route('store.offers') }}" class="flex w-36 items-center justify-center gap-2 bg-store-red px-3 text-xs font-bold hover:bg-red-700"><x-ui.icon name="fire" variant="solid" class="size-4 !text-white" />Offers Zone</a>
    </x-store.ui.container>
</nav>
