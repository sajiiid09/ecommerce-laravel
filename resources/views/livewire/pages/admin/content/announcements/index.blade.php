<div class="p-5 sm:p-8">
    <div class="mx-auto max-w-[1480px]">
        <div class="mb-5 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm text-slate-500">StoreZ / Content / Announcements</p>
                <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900">Announcements</h1>
                <p class="mt-1 text-sm text-slate-500">Create and manage messages shown across the storefront.</p>
            </div>
            <x-ui.button type="button" icon="plus" wire:click="openCreate">Create announcement</x-ui.button>
        </div>

        @if(session('status'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="mb-4 flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm lg:flex-row lg:items-center">
            <x-ui.input wire:model.live.debounce.300ms="search" type="search" placeholder="Search title or message..." leftIcon="magnifying-glass" class="min-w-0 flex-1" />
            <x-ui.select wire:model.live="placement" class="w-full lg:w-44">
                <x-ui.select.option value="all">All placements</x-ui.select.option>
                <x-ui.select.option value="top_bar">Top bar</x-ui.select.option>
                <x-ui.select.option value="storefront">Storefront</x-ui.select.option>
                <x-ui.select.option value="checkout">Checkout</x-ui.select.option>
                <x-ui.select.option value="account">Account</x-ui.select.option>
            </x-ui.select>
            <x-ui.select wire:model.live="status" class="w-full lg:w-36">
                <x-ui.select.option value="all">All statuses</x-ui.select.option>
                <x-ui.select.option value="draft">Draft</x-ui.select.option>
                <x-ui.select.option value="published">Published</x-ui.select.option>
                <x-ui.select.option value="scheduled">Scheduled</x-ui.select.option>
                <x-ui.select.option value="archived">Archived</x-ui.select.option>
            </x-ui.select>
            <x-ui.select wire:model.live="perPage" class="w-full lg:w-24">
                @foreach([15, 25, 50] as $size)
                    <x-ui.select.option wire:key="announcement-page-size-{{ $size }}" :value="$size">{{ $size }}</x-ui.select.option>
                @endforeach
            </x-ui.select>
        </div>

        <x-ui.table :paginator="$announcementRows" pagination:variant="full" :pagination:options="[15, 25, 50]" wire:loading loadOn="pagination, search" class="w-full overflow-visible rounded-xl border border-slate-200 bg-white p-0 shadow-sm" table:class="w-full table-fixed text-left">
            <colgroup>
                <col class="w-[16%]"><col class="w-[23%]"><col class="w-[12%]"><col class="w-[9%]"><col class="w-[10%]"><col class="w-[13%]"><col class="w-[9%]"><col class="w-[8%]">
            </colgroup>
            <x-ui.table.header class="bg-slate-50 text-xs font-semibold text-slate-500">
                <x-ui.table.columns>
                    <x-ui.table.head class="px-4 py-3">Title</x-ui.table.head>
                    <x-ui.table.head class="px-4 py-3">Message</x-ui.table.head>
                    <x-ui.table.head class="px-4 py-3">Placement</x-ui.table.head>
                    <x-ui.table.head class="px-4 py-3">Style</x-ui.table.head>
                    <x-ui.table.head class="px-4 py-3">Priority</x-ui.table.head>
                    <x-ui.table.head class="px-4 py-3">Schedule</x-ui.table.head>
                    <x-ui.table.head class="px-4 py-3">Status</x-ui.table.head>
                    <x-ui.table.head class="px-4 py-3">Actions</x-ui.table.head>
                </x-ui.table.columns>
            </x-ui.table.header>
            <x-ui.table.rows class="divide-y divide-slate-100">
                @forelse($announcementRows as $announcement)
                    <x-ui.table.row :key="'announcement-'.$announcement->id" class="text-sm text-slate-700 hover:bg-slate-50">
                        <x-ui.table.cell class="truncate px-4 py-4 font-bold text-slate-900">{{ $announcement->internal_title }}</x-ui.table.cell>
                        <x-ui.table.cell class="truncate px-4 py-4 text-slate-500" title="{{ $announcement->message }}">{{ $announcement->message }}</x-ui.table.cell>
                        <x-ui.table.cell class="px-4 py-4 capitalize">{{ str_replace('_', ' ', $announcement->placement) }}</x-ui.table.cell>
                        <x-ui.table.cell class="px-4 py-4 capitalize">{{ $announcement->style }}</x-ui.table.cell>
                        <x-ui.table.cell class="px-4 py-4 capitalize">{{ $announcement->priority }}</x-ui.table.cell>
                        <x-ui.table.cell class="px-4 py-4 text-xs text-slate-500">
                            @if($announcement->starts_at || $announcement->ends_at)
                                {{ $announcement->starts_at?->format('M j, Y g:i A') ?? 'Now' }}<br>
                                {{ $announcement->ends_at?->format('M j, Y g:i A') ?? 'No end' }}
                            @else
                                Always
                            @endif
                        </x-ui.table.cell>
                        <x-ui.table.cell class="px-4 py-4 capitalize">{{ $announcement->status }}</x-ui.table.cell>
                        <x-ui.table.cell class="whitespace-nowrap px-4 py-4">
                            <x-ui.dropdown position="bottom-end" portal>
                                <x-slot:button>
                                    <x-ui.button type="button" size="sm" variant="outline" color="slate" icon-after="chevron-down" aria-label="Actions for {{ $announcement->internal_title }}">Action</x-ui.button>
                                </x-slot:button>
                                <x-slot:menu>
                                    <x-ui.dropdown.item as="button" type="button" wire:click="openEdit({{ $announcement->id }})" icon="pencil">Edit</x-ui.dropdown.item>
                                    <x-ui.dropdown.item as="button" type="button" wire:click="deleteAnnouncement({{ $announcement->id }})" wire:confirm="Permanently delete this announcement?" icon="trash" class="text-red-600">Delete</x-ui.dropdown.item>
                                </x-slot:menu>
                            </x-ui.dropdown>
                        </x-ui.table.cell>
                    </x-ui.table.row>
                @empty
                    <x-ui.table.empty>No announcements found.</x-ui.table.empty>
                @endforelse
            </x-ui.table.rows>
        </x-ui.table>
    </div>

    <x-ui.modal id="announcement-editor" width="xl" :heading="$editingId ? 'Edit announcement' : 'Create announcement'" description="Create a message and choose where it appears on the storefront.">
        <form wire:submit="saveAnnouncement" class="space-y-4">
            <label class="block text-sm font-semibold text-slate-700">
                Internal title
                <x-ui.input wire:model="internal_title" placeholder="Campaign or notice name" class="mt-2" />
                @error('internal_title')<span class="mt-1 block text-xs font-normal text-red-600">{{ $message }}</span>@enderror
            </label>

            <label class="block text-sm font-semibold text-slate-700">
                Message
                <x-ui.textarea wire:model="message" rows="4" placeholder="What should customers know?" class="mt-2" />
                @error('message')<span class="mt-1 block text-xs font-normal text-red-600">{{ $message }}</span>@enderror
            </label>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block text-sm font-semibold text-slate-700">
                    Style
                    <x-ui.select wire:model="style" class="mt-2">
                        <x-ui.select.option value="info">Info (blue)</x-ui.select.option>
                        <x-ui.select.option value="success">Success (green)</x-ui.select.option>
                        <x-ui.select.option value="warning">Warning (amber)</x-ui.select.option>
                        <x-ui.select.option value="danger">Danger (red)</x-ui.select.option>
                    </x-ui.select>
                </label>
                <label class="block text-sm font-semibold text-slate-700">
                    Priority
                    <x-ui.select wire:model="priority" class="mt-2">
                        <x-ui.select.option value="low">Low</x-ui.select.option>
                        <x-ui.select.option value="normal">Normal</x-ui.select.option>
                        <x-ui.select.option value="high">High</x-ui.select.option>
                    </x-ui.select>
                </label>
            </div>

            <label class="block text-sm font-semibold text-slate-700">
                Placement
                <x-ui.select wire:model="form_placement" class="mt-2">
                    <x-ui.select.option value="top_bar">Top bar</x-ui.select.option>
                    <x-ui.select.option value="storefront">Storefront</x-ui.select.option>
                    <x-ui.select.option value="checkout">Checkout</x-ui.select.option>
                    <x-ui.select.option value="account">Account</x-ui.select.option>
                </x-ui.select>
            </label>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block text-sm font-semibold text-slate-700">
                    Link label
                    <x-ui.input wire:model="link_label" placeholder="Shop offers" class="mt-2" />
                </label>
                <label class="block text-sm font-semibold text-slate-700">
                    Link URL
                    <x-ui.input wire:model="link_url" type="text" inputmode="url" placeholder="https://... or /offers" class="mt-2" />
                    <span class="mt-1 block text-xs font-normal text-slate-500">Use a full URL or a storefront path such as /offers.</span>
                    @error('link_url')<span class="mt-1 block text-xs font-normal text-red-600">{{ $message }}</span>@enderror
                </label>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block text-sm font-semibold text-slate-700">
                    Starts at
                    <x-ui.input wire:model="starts_at" type="datetime-local" class="mt-2" />
                    @error('starts_at')<span class="mt-1 block text-xs font-normal text-red-600">{{ $message }}</span>@enderror
                </label>
                <label class="block text-sm font-semibold text-slate-700">
                    Ends at
                    <x-ui.input wire:model="ends_at" type="datetime-local" class="mt-2" />
                    @error('ends_at')<span class="mt-1 block text-xs font-normal text-red-600">{{ $message }}</span>@enderror
                </label>
            </div>

            <label class="block text-sm font-semibold text-slate-700">
                Status
                <x-ui.select wire:model="form_status" class="mt-2">
                    <x-ui.select.option value="draft">Draft</x-ui.select.option>
                    <x-ui.select.option value="published">Published</x-ui.select.option>
                    <x-ui.select.option value="scheduled">Scheduled</x-ui.select.option>
                    <x-ui.select.option value="archived">Archived</x-ui.select.option>
                </x-ui.select>
            </label>

            <x-ui.checkbox wire:model="dismissible" label="Allow customers to dismiss" size="sm" />

            <div class="flex justify-end gap-3 border-t border-slate-200 pt-4">
                <x-ui.button type="button" variant="outline" wire:click="cancelEdit">Cancel</x-ui.button>
                <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="saveAnnouncement">
                    <span wire:loading.remove wire:target="saveAnnouncement">{{ $editingId ? 'Update announcement' : 'Save announcement' }}</span>
                    <span wire:loading wire:target="saveAnnouncement">Saving...</span>
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
