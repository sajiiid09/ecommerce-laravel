<div class="p-5 sm:p-8">
    <div class="mx-auto max-w-[1480px]">
        <div class="mb-5 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm text-slate-500">StoreZ / Marketing / Coupons</p>
                <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900">Coupons</h1>
                <p class="mt-1 text-sm text-slate-500">Create and manage checkout discounts.</p>
            </div>
            <x-ui.button type="button" wire:click="openCreate" icon="plus">Create coupon</x-ui.button>
        </div>

        <div class="mb-4 flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center">
            <x-ui.input wire:model.live.debounce.300ms="searchQuery" type="search" placeholder="Search coupon codes..." leftIcon="magnifying-glass" class="min-w-0 flex-1" />
            <x-ui.select wire:model.live="status" class="w-full sm:w-40">
                <x-ui.select.option value="all">All statuses</x-ui.select.option>
                <x-ui.select.option value="active">Active</x-ui.select.option>
                <x-ui.select.option value="scheduled">Scheduled</x-ui.select.option>
                <x-ui.select.option value="expired">Expired</x-ui.select.option>
                <x-ui.select.option value="inactive">Inactive</x-ui.select.option>
            </x-ui.select>
            <x-ui.select wire:model.live="perPage" class="w-full sm:w-24">
                @foreach([15, 25, 50] as $size)
                    <x-ui.select.option :value="$size">{{ $size }}</x-ui.select.option>
                @endforeach
            </x-ui.select>
        </div>

        <x-ui.table :paginator="$couponRows" pagination:variant="full" :pagination:options="[15, 25, 50]" wire:loading loadOn="pagination, search" class="w-full overflow-hidden rounded-xl border border-slate-200 bg-white p-0 shadow-sm" table:class="w-full table-fixed text-left">
            <colgroup><col class="w-[17%]"><col class="w-[15%]"><col class="w-[14%]"><col class="w-[14%]"><col class="w-[12%]"><col class="w-[14%]"><col class="w-[14%]"></colgroup>
            <x-ui.table.header class="bg-slate-50 text-xs font-semibold text-slate-500">
                <x-ui.table.columns>
                    <x-ui.table.head class="px-4 py-3">Code</x-ui.table.head><x-ui.table.head class="px-4 py-3">Discount</x-ui.table.head><x-ui.table.head class="px-4 py-3">Scope</x-ui.table.head><x-ui.table.head class="px-4 py-3">Minimum</x-ui.table.head><x-ui.table.head class="px-4 py-3">Usage</x-ui.table.head><x-ui.table.head class="px-4 py-3">Validity</x-ui.table.head><x-ui.table.head class="px-4 py-3">Actions</x-ui.table.head>
                </x-ui.table.columns>
            </x-ui.table.header>
            <x-ui.table.rows class="divide-y divide-slate-100">
                @forelse($couponRows as $coupon)
                    @php($couponStatus = $couponService->status($coupon))
                    <x-ui.table.row :key="'coupon-'.$coupon->id" class="text-sm text-slate-700 hover:bg-slate-50">
                        <x-ui.table.cell class="truncate px-4 py-4 font-bold text-slate-900">{{ $coupon->code }}</x-ui.table.cell>
                        <x-ui.table.cell class="px-4 py-4">{{ $coupon->discount_type->value === 'percentage' ? $coupon->percentage.'%' : 'BDT '.number_format($coupon->amount_minor / 100, 2) }}</x-ui.table.cell>
                        <x-ui.table.cell class="px-4 py-4">{{ $coupon->products_count || $coupon->categories_count ? 'Targeted' : 'Storewide' }}</x-ui.table.cell>
                        <x-ui.table.cell class="px-4 py-4">BDT {{ number_format($coupon->minimum_subtotal_minor / 100, 2) }}</x-ui.table.cell>
                        <x-ui.table.cell class="px-4 py-4">{{ $coupon->redeemed_count }}{{ $coupon->usage_limit ? ' / '.$coupon->usage_limit : '' }}</x-ui.table.cell>
                        <x-ui.table.cell class="px-4 py-4">
                            <x-ui.badge variant="solid" :color="match ($couponStatus) {
                                'active' => 'emerald',
                                'scheduled' => 'amber',
                                default => 'red',
                            }" pill size="sm">
                                {{ ucfirst($couponStatus) }}
                            </x-ui.badge>
                        </x-ui.table.cell>
                        <x-ui.table.cell class="whitespace-nowrap px-4 py-4">
                            <x-ui.dropdown position="bottom-end" portal>
                                <x-slot:button>
                                    <x-ui.button
                                        type="button"
                                        size="sm"
                                        variant="outline"
                                        color="slate"
                                        icon-after="chevron-down"
                                        aria-label="Actions for {{ $coupon->code }}"
                                    >Action</x-ui.button>
                                </x-slot:button>
                                <x-slot:menu>
                                    <x-ui.dropdown.item as="button" type="button" wire:click="openEdit({{ $coupon->id }})" icon="pencil">Edit</x-ui.dropdown.item>
                                    <x-ui.dropdown.item as="button" type="button" wire:click="toggleActive({{ $coupon->id }})" icon="power">{{ $coupon->is_active ? 'Deactivate' : 'Activate' }}</x-ui.dropdown.item>
                                    <x-ui.dropdown.separator />
                                    <x-ui.dropdown.item as="button" type="button" wire:click="deleteCoupon({{ $coupon->id }})" icon="trash" class="text-red-600">Delete</x-ui.dropdown.item>
                                </x-slot:menu>
                            </x-ui.dropdown>
                        </x-ui.table.cell>
                    </x-ui.table.row>
                @empty
                    <x-ui.table.empty>No coupons found.</x-ui.table.empty>
                @endforelse
            </x-ui.table.rows>
        </x-ui.table>
    </div>

    <x-ui.modal id="coupon-editor" width="3xl" :heading="$editingId ? 'Edit coupon' : 'Create coupon'" description="Set the discount and checkout eligibility rules.">
        <form wire:submit="saveCoupon" class="space-y-5">
            <div class="grid items-end gap-4 sm:grid-cols-[1fr_auto]">
                <label class="block text-sm font-semibold text-slate-700">
                    Coupon code
                    <x-ui.input wire:model="code" placeholder="SAVE10" class="mt-2" autocomplete="off" />
                </label>
                <x-ui.button type="button" wire:click="generateCode" variant="outline" class="w-full sm:w-auto">Generate code</x-ui.button>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block text-sm font-semibold text-slate-700">
                    Discount type
                    <x-ui.select wire:model.live="discountType" class="mt-2">
                        <x-ui.select.option value="percentage">Percentage</x-ui.select.option>
                        <x-ui.select.option value="fixed">Fixed BDT</x-ui.select.option>
                    </x-ui.select>
                </label>
                @if($discountType === 'percentage')
                    <label class="block text-sm font-semibold text-slate-700">
                        Percentage discount
                        <x-ui.input wire:model="percentage" type="number" min="1" max="100" step="1" suffix="%" class="mt-2" inputmode="numeric" />
                    </label>
                @endif
                @if($discountType === 'fixed')
                    <label class="block text-sm font-semibold text-slate-700">
                        Fixed discount amount
                        <x-ui.input wire:model="amount" type="number" min="0.01" step="0.01" prefix="BDT" class="mt-2" inputmode="decimal" />
                    </label>
                @endif
                <label class="block text-sm font-semibold text-slate-700">
                    Minimum subtotal
                    <x-ui.input wire:model="minimumSubtotal" type="number" min="0" step="0.01" prefix="BDT" class="mt-2" inputmode="decimal" />
                    <span class="mt-1 block text-xs font-normal text-slate-500">The cart subtotal required before this coupon can be used.</span>
                </label>
                <label class="block text-sm font-semibold text-slate-700">
                    Coupon scope
                    <x-ui.select wire:model.live="scope" class="mt-2">
                        <x-ui.select.option value="storewide">Storewide</x-ui.select.option>
                        <x-ui.select.option value="targeted">Selected products/categories</x-ui.select.option>
                    </x-ui.select>
                </label>
            </div>

            @if($scope === 'targeted')
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm font-semibold text-slate-700">
                        Products this coupon applies to
                        <x-ui.select wire:model="productIds" multiple searchable class="mt-2">
                            @foreach($products as $product)
                                <x-ui.select.option :value="$product->id">{{ $product->name }}</x-ui.select.option>
                            @endforeach
                        </x-ui.select>
                    </label>
                    <label class="block text-sm font-semibold text-slate-700">
                        Categories this coupon applies to
                        <x-ui.select wire:model="categoryIds" multiple searchable class="mt-2">
                            @foreach($categories as $category)
                                <x-ui.select.option :value="$category->id">{{ $category->name }}</x-ui.select.option>
                            @endforeach
                        </x-ui.select>
                        <span class="mt-1 block text-xs font-normal text-slate-500">Category matches include products in subcategories.</span>
                    </label>
                </div>
            @endif

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block text-sm font-semibold text-slate-700">
                    Starts at
                    <x-ui.input wire:model="startsAt" type="datetime-local" class="mt-2" />
                    <span class="mt-1 block text-xs font-normal text-slate-500">Optional start date and time.</span>
                </label>
                <label class="block text-sm font-semibold text-slate-700">
                    Ends at
                    <x-ui.input wire:model="endsAt" type="datetime-local" class="mt-2" />
                    <span class="mt-1 block text-xs font-normal text-slate-500">Optional expiry date and time.</span>
                </label>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block text-sm font-semibold text-slate-700">
                    Global usage limit
                    <x-ui.input wire:model="usageLimit" type="number" min="1" step="1" placeholder="Unlimited" class="mt-2" inputmode="numeric" />
                    <span class="mt-1 block text-xs font-normal text-slate-500">Maximum number of orders using this coupon.</span>
                </label>
                <label class="block text-sm font-semibold text-slate-700">
                    Per-customer usage limit
                    <x-ui.input wire:model="perCustomerLimit" type="number" min="1" step="1" placeholder="Unlimited" class="mt-2" inputmode="numeric" />
                    <span class="mt-1 block text-xs font-normal text-slate-500">Leave blank to allow unlimited uses per customer.</span>
                </label>
            </div>
            <x-ui.checkbox wire:model="isActive" label="Coupon is active" />
            <div class="flex justify-end gap-3 border-t border-slate-200 pt-4"><x-ui.button type="button" variant="outline" wire:click="$dispatch('close-modal', { id: 'coupon-editor' })">Cancel</x-ui.button><x-ui.button type="submit">Save coupon</x-ui.button></div>
        </form>
    </x-ui.modal>
</div>
