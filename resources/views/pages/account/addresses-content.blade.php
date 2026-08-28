<main class="bg-store-soft py-6 sm:py-8">
    <x-store.ui.container>
        <div class="grid gap-5 lg:grid-cols-[240px_minmax(0,1fr)]">
            <x-store.account.sidebar />
            <div>
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-extrabold text-store-ink sm:text-3xl">Addresses</h1>
                        <p class="mt-1 text-sm text-store-muted">Manage your saved shipping addresses.</p>
                    </div>
                    <x-ui.button type="button" icon="plus" wire:click="openCreate">Add address</x-ui.button>
                </div>

                @if (session('status'))
                    <p class="mt-4 rounded-control bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</p>
                @endif

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    @forelse ($addresses as $address)
                        <article wire:key="address-{{ $address->id }}" class="rounded-card border border-store-border bg-white p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h2 class="font-bold text-store-ink">{{ $address->label }}</h2>
                                        @if ($address->is_default)
                                            <span class="rounded-full bg-store-blue-soft px-2.5 py-1 text-xs font-bold text-store-blue">Default</span>
                                        @endif
                                    </div>
                                    <p class="mt-3 text-sm font-semibold text-store-ink">{{ $address->recipient_name }}</p>
                                    <p class="mt-1 text-sm text-store-muted">{{ $address->phone }}</p>
                                </div>
                                <x-ui.icon name="map-pin" class="size-5 shrink-0 !text-store-blue" />
                            </div>
                            <address class="mt-4 not-italic text-sm leading-6 text-store-muted">
                                {{ $address->address_line }}<br>
                                {{ $address->city }}{{ $address->district ? ', '.$address->district : '' }}{{ $address->postal_code ? ' '.$address->postal_code : '' }}<br>
                                {{ $address->country }}
                            </address>
                            <div class="mt-5 flex flex-wrap gap-2 border-t border-store-border pt-4">
                                <x-ui.button type="button" size="sm" variant="outline" color="slate" wire:click="openEdit({{ $address->id }})">Edit</x-ui.button>
                                @if (! $address->is_default)
                                    <x-ui.button type="button" size="sm" variant="outline" color="blue" wire:click="setDefault({{ $address->id }})">Set as default</x-ui.button>
                                @endif
                                <x-ui.button type="button" size="sm" variant="outline" color="red" wire:click="deleteAddress({{ $address->id }})" wire:confirm="Delete this address?">Delete</x-ui.button>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-card border border-store-border bg-white p-10 text-center md:col-span-2">
                            <x-ui.icon name="map-pin" class="mx-auto size-10 !text-store-muted" />
                            <h2 class="mt-4 font-bold text-store-ink">No saved addresses</h2>
                            <p class="mt-1 text-sm text-store-muted">Add an address to make checkout faster.</p>
                            <x-ui.button type="button" class="mt-5" wire:click="openCreate">Add your first address</x-ui.button>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </x-store.ui.container>

    <x-ui.modal
        id="address-editor"
        width="2xl"
        :heading="$editingAddressId ? 'Edit address' : 'Add address'"
        description="Save a reusable shipping address for faster checkout."
        stickyHeader
    >
        <form wire:submit="saveAddress" class="space-y-5">
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block text-sm font-semibold text-store-ink">Address label<x-ui.input wire:model="label" placeholder="Home or Office" class="mt-2" />@error('label') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror</label>
                <label class="block text-sm font-semibold text-store-ink">Recipient name<x-ui.input wire:model="recipientName" autocomplete="name" class="mt-2" />@error('recipientName') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror</label>
                <label class="block text-sm font-semibold text-store-ink">Phone<x-ui.input wire:model="phone" type="tel" autocomplete="tel" class="mt-2" />@error('phone') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror</label>
                <label class="block text-sm font-semibold text-store-ink">Country code<x-ui.input wire:model="country" maxlength="2" class="mt-2 uppercase" />@error('country') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror</label>
            </div>
            <label class="block text-sm font-semibold text-store-ink">Address<x-ui.input wire:model="addressLine" placeholder="House, road, or apartment" class="mt-2" />@error('addressLine') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror</label>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block text-sm font-semibold text-store-ink">City / Area<x-ui.input wire:model="city" class="mt-2" />@error('city') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror</label>
                <label class="block text-sm font-semibold text-store-ink">District<x-ui.input wire:model="district" class="mt-2" />@error('district') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror</label>
                <label class="block text-sm font-semibold text-store-ink">Postal code<x-ui.input wire:model="postalCode" class="mt-2" />@error('postalCode') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror</label>
            </div>
            <x-ui.checkbox wire:model="isDefault" label="Set as default address" description="Use this address automatically during checkout." size="sm" />
            <div class="flex justify-end gap-3 border-t border-store-border pt-4">
                <x-ui.button type="button" variant="outline" color="slate" wire:click="$dispatch('close-modal', { id: 'address-editor' })">Cancel</x-ui.button>
                <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="saveAddress">
                    <span wire:loading.remove wire:target="saveAddress">Save address</span>
                    <span wire:loading wire:target="saveAddress">Saving...</span>
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</main>
