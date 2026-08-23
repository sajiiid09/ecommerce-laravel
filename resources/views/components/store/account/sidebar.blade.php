<div x-data="{ open: false }">
    <button type="button" class="inline-flex h-10 items-center gap-2 rounded-control border border-store-blue bg-white px-4 text-sm font-bold text-store-blue lg:hidden" @click="open = true" aria-controls="account-navigation" :aria-expanded="open.toString()"><x-ui.icon name="bars-3" class="size-4 !text-current" />Account menu</button>
    <div x-cloak x-show="open" x-transition.opacity class="fixed inset-0 z-40 bg-store-ink/45 lg:hidden" @click="open = false" aria-hidden="true"></div>
    <aside id="account-navigation" x-cloak x-show="open || window.innerWidth >= 1024" x-transition class="fixed inset-y-0 left-0 z-50 w-[min(88vw,20rem)] overflow-y-auto rounded-none border-r border-store-border bg-white p-3 shadow-2xl lg:static lg:block lg:w-auto lg:rounded-card lg:border lg:shadow-none" @keydown.escape.window="open = false">
        <div class="mb-3 flex items-center justify-between lg:hidden"><span class="font-bold text-store-ink">My account</span><button type="button" class="grid size-11 place-items-center rounded-control text-store-ink hover:bg-store-soft" @click="open = false" aria-label="Close account menu"><x-ui.icon name="x-mark" class="size-5 !text-current" /></button></div>
        <a href="{{ route('account.dashboard') }}" wire:navigate class="block rounded-control bg-store-blue-soft px-3 py-3 text-sm font-bold text-store-blue">Account Overview</a>
        <a href="{{ route('account.orders') }}" wire:navigate class="mt-1 block rounded-control px-3 py-3 text-sm text-store-text hover:bg-store-soft">My Orders</a>
        <a href="{{ route('store.wishlist') }}" wire:navigate class="mt-1 block rounded-control px-3 py-3 text-sm text-store-text hover:bg-store-soft">Wishlist</a>
        <a href="{{ route('account.dashboard') }}#addresses" wire:navigate class="mt-1 block rounded-control px-3 py-3 text-sm text-store-text hover:bg-store-soft">Addresses</a>
    </aside>
</div>
