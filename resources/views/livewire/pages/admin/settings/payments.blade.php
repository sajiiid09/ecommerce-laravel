<div class="p-5 sm:p-8">
    <div class="mx-auto max-w-[1100px]">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm text-slate-500">StoreZ / Settings / Payments</p>
                <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900">Payment settings</h1>
                <p class="mt-1 max-w-2xl text-sm text-slate-500">Configure payment providers without changing deployment files. Credentials are encrypted before they are stored.</p>
            </div>
            <span class="rounded-full px-3 py-1.5 text-xs font-bold {{ $enabled && isset($maskedCredentials['secret_key'], $maskedCredentials['webhook_secret']) ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                {{ $enabled && isset($maskedCredentials['secret_key'], $maskedCredentials['webhook_secret']) ? 'Stripe enabled' : 'Stripe disabled' }}
            </span>
        </div>

        @if (session('status'))
            <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif

        <form wire:submit="save" class="mt-6 space-y-5">
            <x-admin.cms.panel title="Stripe" description="Use Stripe-hosted Checkout for secure card payments. Card numbers and CVC values never enter StoreZ.">
                <div class="space-y-5">
                    <div class="flex flex-wrap items-center justify-between gap-4 rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <div>
                            <p class="font-bold text-slate-900">Accept Stripe payments</p>
                            <p class="mt-1 text-xs text-slate-500">Stripe will appear at checkout only after valid credentials are saved.</p>
                        </div>
                        <label class="inline-flex items-center gap-2 text-sm font-bold text-slate-700">
                            <input type="checkbox" wire:model="enabled" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            Enabled
                        </label>
                    </div>

                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                        <p class="font-bold">Keep secrets private</p>
                        <p class="mt-1">Secrets are encrypted with the application APP_KEY. Back up APP_KEY securely; without it, saved credentials cannot be decrypted. Use test mode for the showcase.</p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="text-sm font-semibold text-slate-700">
                            Account mode
                            <select wire:model="mode" class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm">
                                <option value="test">Test mode</option>
                                <option value="live">Live mode</option>
                            </select>
                            @error('mode')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                        </label>
                        <div class="rounded-lg border border-slate-200 p-3 text-xs text-slate-500">
                            <p class="font-bold text-slate-700">Current configuration</p>
                            <p class="mt-1">Mode: <span class="font-semibold uppercase">{{ $mode }}</span></p>
                            @if ($updatedAt)<p class="mt-1">Updated {{ $updatedAt }}{{ $updatedBy ? ' by '.$updatedBy : '' }}</p>@else<p class="mt-1">No credentials saved yet.</p>@endif
                        </div>
                    </div>

                    @if ($mode === 'live')
                        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            <p class="font-bold">Live mode warning</p>
                            <p class="mt-1">Live credentials charge real cards. Confirm that the selected keys are intentional before enabling Stripe.</p>
                        </div>
                    @endif

                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="text-sm font-semibold text-slate-700">
                            Publishable key <span class="font-normal text-slate-400">(optional)</span>
                            <input type="password" wire:model="publishable_key" autocomplete="new-password" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm" placeholder="pk_test_...">
                            @if (isset($maskedCredentials['publishable_key']))<span class="mt-1 block text-xs text-slate-500">Saved: {{ $maskedCredentials['publishable_key'] }}</span>@endif
                            <label class="mt-2 flex items-center gap-2 text-xs font-normal text-slate-500"><input type="checkbox" wire:model="clear_publishable_key" class="rounded border-slate-300 text-red-600"> Clear saved key</label>
                            @error('publishable_key')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                        </label>
                        <label class="text-sm font-semibold text-slate-700">
                            Secret key
                            <input type="password" wire:model="secret_key" autocomplete="new-password" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm" placeholder="sk_test_...">
                            @if (isset($maskedCredentials['secret_key']))<span class="mt-1 block text-xs text-slate-500">Saved: {{ $maskedCredentials['secret_key'] }}</span>@endif
                            <label class="mt-2 flex items-center gap-2 text-xs font-normal text-slate-500"><input type="checkbox" wire:model="clear_secret_key" class="rounded border-slate-300 text-red-600"> Clear saved key</label>
                            @error('secret_key')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                        </label>
                    </div>

                    <label class="block text-sm font-semibold text-slate-700">
                        Webhook signing secret
                        <input type="password" wire:model="webhook_secret" autocomplete="new-password" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm" placeholder="whsec_...">
                        @if (isset($maskedCredentials['webhook_secret']))<span class="mt-1 block text-xs text-slate-500">Saved: {{ $maskedCredentials['webhook_secret'] }}</span>@endif
                        <label class="mt-2 flex items-center gap-2 text-xs font-normal text-slate-500"><input type="checkbox" wire:model="clear_webhook_secret" class="rounded border-slate-300 text-red-600"> Clear saved secret</label>
                        @error('webhook_secret')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>

                    <div class="flex flex-wrap gap-3 border-t border-slate-200 pt-5">
                        <button type="submit" wire:loading.attr="disabled" wire:target="save" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-700 disabled:cursor-wait disabled:opacity-60"><span wire:loading.remove wire:target="save">Save settings</span><span wire:loading wire:target="save">Saving...</span></button>
                        <button type="button" wire:click="testConnection" wire:loading.attr="disabled" wire:target="testConnection" class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 hover:border-blue-300 hover:text-blue-700 disabled:cursor-wait disabled:opacity-60"><span wire:loading.remove wire:target="testConnection">Test connection</span><span wire:loading wire:target="testConnection">Testing...</span></button>
                        <button type="button" wire:click="clearCredentials" wire:confirm="Clear all saved Stripe credentials and disable Stripe?" wire:loading.attr="disabled" wire:target="clearCredentials" class="rounded-lg border border-red-200 px-4 py-2.5 text-sm font-bold text-red-600 hover:bg-red-50 disabled:opacity-60">Clear credentials</button>
                    </div>
                </div>
            </x-admin.cms.panel>
        </form>
    </div>
</div>
