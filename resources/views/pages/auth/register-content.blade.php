<main class="bg-store-soft py-12 sm:py-16">
    <x-store.ui.container size="narrow">
        <section class="rounded-card border border-store-border bg-white p-6 shadow-store-soft sm:p-8">
            <h1 class="text-2xl font-extrabold text-store-ink">Create your StoreZ account</h1>
            <p class="mt-1 text-sm text-store-muted">Join for easier checkout and order tracking.</p>

            <form wire:submit="register" class="mt-7 space-y-4">
                <label class="block text-sm font-semibold text-store-ink">
                    Name
                    <x-ui.input required wire:model="name" autocomplete="name" class="mt-2" controlClass="!h-11 !rounded-control !border-store-border !px-3 focus:!border-store-blue focus:!ring-store-blue/10" />
                    @error('name') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                </label>
                <label class="block text-sm font-semibold text-store-ink">
                    Email address
                    <x-ui.input type="email" required wire:model="email" autocomplete="email" class="mt-2" controlClass="!h-11 !rounded-control !border-store-border !px-3 focus:!border-store-blue focus:!ring-store-blue/10" />
                    @error('email') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                </label>
                <label class="block text-sm font-semibold text-store-ink">
                    Password
                    <x-ui.input type="password" required wire:model="password" autocomplete="new-password" class="mt-2" controlClass="!h-11 !rounded-control !border-store-border !px-3 focus:!border-store-blue focus:!ring-store-blue/10" />
                    @error('password') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                </label>
                <label class="block text-sm font-semibold text-store-ink">
                    Confirm password
                    <x-ui.input type="password" required wire:model="password_confirmation" autocomplete="new-password" class="mt-2" controlClass="!h-11 !rounded-control !border-store-border !px-3 focus:!border-store-blue focus:!ring-store-blue/10" />
                </label>
                <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="register" class="h-11 w-full !rounded-control !bg-store-blue !text-sm !font-bold !text-white hover:!bg-store-blue-dark">
                    Create Account
                </x-ui.button>
            </form>

            <p class="mt-6 text-center text-sm text-store-muted">Already have an account? <a href="{{ route('login') }}" wire:navigate class="font-bold text-store-blue">Sign in</a></p>
        </section>
    </x-store.ui.container>
</main>
