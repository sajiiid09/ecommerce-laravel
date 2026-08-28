<div class="p-5 sm:p-8">
    <div class="mx-auto max-w-[1100px]">
        <div>
            <p class="text-sm text-slate-500">StoreZ / Profile</p>
            <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900">Admin profile</h1>
            <p class="mt-1 max-w-2xl text-sm text-slate-500">Manage the administrator name and contact details shown across the admin area.</p>
        </div>

        @if (session('status'))
            <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('status') }}</div>
        @endif

        <form wire:submit="save" class="mt-6 space-y-5">
            <x-admin.cms.panel title="Profile information" description="These details identify you in the StoreZ administration panel.">
                <div class="grid gap-5 sm:grid-cols-2">
                    <label class="text-sm font-semibold text-slate-700">
                        Full name
                        <x-ui.input wire:model="name" class="mt-1" autocomplete="name" />
                        @error('name')<span class="mt-1 block text-xs font-normal text-red-600">{{ $message }}</span>@enderror
                    </label>

                    <label class="text-sm font-semibold text-slate-700">
                        Phone
                        <x-ui.input type="tel" wire:model="phone" class="mt-1" autocomplete="tel" />
                        @error('phone')<span class="mt-1 block text-xs font-normal text-red-600">{{ $message }}</span>@enderror
                    </label>

                    <label class="text-sm font-semibold text-slate-700 sm:col-span-2">
                        Email address
                        <x-ui.input type="email" wire:model="email" class="mt-1" autocomplete="email" />
                        @error('email')<span class="mt-1 block text-xs font-normal text-red-600">{{ $message }}</span>@enderror
                    </label>
                </div>

                <div class="mt-6 flex flex-wrap gap-3 border-t border-slate-200 pt-5">
                    <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">Save changes</span>
                        <span wire:loading wire:target="save">Saving...</span>
                    </x-ui.button>
                    <x-ui.button as="a" href="{{ route('admin.dashboard') }}" variant="outline" color="slate">Back to dashboard</x-ui.button>
                </div>
            </x-admin.cms.panel>
        </form>
    </div>
</div>
