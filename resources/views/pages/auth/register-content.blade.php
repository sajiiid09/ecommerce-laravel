<main class="bg-store-soft py-12 sm:py-16"><x-store.ui.container size="narrow">
        <section class="rounded-card border border-store-border bg-white p-6 shadow-store-soft sm:p-8">
            <h1 class="text-2xl font-extrabold text-store-ink">Create your StoreZ account</h1>
            <p class="mt-1 text-sm text-store-muted">Join for easier checkout and order tracking.</p>
            <form action="{{ route('account.dashboard') }}" class="mt-7 space-y-4">
                <div class="grid gap-4 sm:grid-cols-2"><label class="block text-sm font-semibold text-store-ink">First
                        name<input required
                            class="mt-2 h-11 w-full rounded-control border border-store-border px-3 outline-none focus:border-store-blue focus:ring-2 focus:ring-store-blue/10"></label><label
                        class="block text-sm font-semibold text-store-ink">Last name<input required
                            class="mt-2 h-11 w-full rounded-control border border-store-border px-3 outline-none focus:border-store-blue focus:ring-2 focus:ring-store-blue/10"></label>
                </div><label class="block text-sm font-semibold text-store-ink">Email address<input type="email"
                        required
                        class="mt-2 h-11 w-full rounded-control border border-store-border px-3 outline-none focus:border-store-blue focus:ring-2 focus:ring-store-blue/10"></label><label
                    class="block text-sm font-semibold text-store-ink">Password<input type="password" required
                        class="mt-2 h-11 w-full rounded-control border border-store-border px-3 outline-none focus:border-store-blue focus:ring-2 focus:ring-store-blue/10"></label><label
                    class="flex items-start gap-2 text-sm text-store-muted"><input type="checkbox" required
                        class="mt-0.5 size-4 rounded border-store-border text-store-blue">I agree to the StoreZ terms
                    and privacy policy.</label><button
                    class="h-11 w-full rounded-control bg-store-blue text-sm font-bold text-white hover:bg-store-blue-dark">Create
                    Account</button>
            </form>
            <p class="mt-6 text-center text-sm text-store-muted">Already have an account? <a href="{{ route('login') }}"
                    wire:navigate class="font-bold text-store-blue">Sign in</a></p>
        </section>
    </x-store.ui.container></main>
