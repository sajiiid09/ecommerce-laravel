<main class="bg-store-soft py-6 sm:py-8">
    <x-store.ui.container>
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-store-blue">Welcome back</p>
                <h1 class="mt-1 text-2xl font-extrabold text-store-ink sm:text-3xl">{{ $user->name }}</h1>
                <p class="mt-1 text-sm text-store-muted">Manage your account and orders.</p>
            </div>
            <a href="{{ route('store.category') }}" wire:navigate class="inline-flex h-10 items-center rounded-control bg-store-blue px-4 text-sm font-bold text-white">Continue Shopping</a>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-3">
            <div class="rounded-card border border-store-border bg-white p-5"><p class="text-sm text-store-muted">Orders placed</p><p class="mt-2 text-3xl font-extrabold text-store-ink">{{ $ordersCount }}</p></div>
            <div class="rounded-card border border-store-border bg-white p-5"><p class="text-sm text-store-muted">Wishlist items</p><p class="mt-2 text-3xl font-extrabold text-store-ink" x-text="wishlist.length">0</p></div>
            <div class="rounded-card border border-store-border bg-white p-5"><p class="text-sm text-store-muted">Saved addresses</p><p class="mt-2 text-3xl font-extrabold text-store-ink">0</p></div>
        </div>

        <div class="mt-6 grid gap-5 lg:grid-cols-[240px_1fr]">
            <x-store.account.sidebar />
            <section class="rounded-card border border-store-border bg-white p-5">
                <h2 class="text-lg font-extrabold text-store-ink">Profile</h2>
                @if(session('status')) <p class="mt-3 rounded-control bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</p> @endif
                <form wire:submit="updateProfile" class="mt-5 grid gap-4 sm:grid-cols-2">
                    <label class="text-sm font-semibold text-store-ink">Name<input wire:model="name" class="mt-2 h-11 w-full rounded-control border border-store-border px-3"></label>
                    <label class="text-sm font-semibold text-store-ink">Phone<input wire:model="phone" class="mt-2 h-11 w-full rounded-control border border-store-border px-3"></label>
                    <label class="text-sm font-semibold text-store-ink sm:col-span-2">Email<input type="email" wire:model="email" class="mt-2 h-11 w-full rounded-control border border-store-border px-3">@error('email') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror</label>
                    <button type="submit" class="h-11 rounded-control bg-store-blue px-4 text-sm font-bold text-white sm:col-span-2 sm:w-fit">Save profile</button>
                </form>
            </section>
        </div>
    </x-store.ui.container>
</main>
