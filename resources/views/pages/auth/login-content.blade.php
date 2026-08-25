<main class="bg-store-soft py-12 sm:py-20"><x-store.ui.container size="narrow">
        <section class="rounded-card border border-store-border bg-white p-6 shadow-store-soft sm:p-8">
            <div class="text-center"><img src="{{ asset('images/brand/storez-logo.png') }}" alt="StoreZ"
                    class="mx-auto w-32">
                <h1 class="mt-6 text-2xl font-extrabold text-store-ink">Welcome back</h1>
                <p class="mt-1 text-sm text-store-muted">Sign in to continue shopping.</p>
            </div>
            <form wire:submit="login" class="mt-8 space-y-4"><label
                    class="block text-sm font-semibold text-store-ink">Email address<input type="email" required
                        wire:model="email" class="mt-2 h-11 w-full rounded-control border border-store-border px-3 outline-none focus:border-store-blue focus:ring-2 focus:ring-store-blue/10">@error('email')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror</label><label
                    class="block text-sm font-semibold text-store-ink">Password<input type="password" required
                        wire:model="password" class="mt-2 h-11 w-full rounded-control border border-store-border px-3 outline-none focus:border-store-blue focus:ring-2 focus:ring-store-blue/10">@error('password')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror</label>
                <div class="flex items-center justify-between text-sm"><label
                        class="flex items-center gap-2 text-store-muted"><input type="checkbox"
                            wire:model="remember" class="size-4 rounded border-store-border text-store-blue">Remember me</label><a
                        href="#forgot" class="font-semibold text-store-blue">Forgot password?</a></div><button
                    type="submit" wire:loading.attr="disabled" wire:target="login"
                    class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-control bg-store-blue text-sm font-bold text-white hover:bg-store-blue-dark disabled:cursor-wait disabled:opacity-75">
                    <span wire:loading.remove wire:target="login">Sign In</span>
                    <span wire:loading.flex wire:target="login" class="items-center justify-center gap-2">
                        <svg class="size-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Signing In...
                    </span>
                </button>
            </form>
            <p class="mt-6 text-center text-sm text-store-muted">New to StoreZ? <a href="{{ route('register') }}"
                    wire:navigate class="font-bold text-store-blue">Create an account</a></p>
        </section>
    </x-store.ui.container></main>
