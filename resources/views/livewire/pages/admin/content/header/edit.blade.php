<div class="p-5 sm:p-8">
    <div class="mx-auto max-w-[1280px]">
        <x-admin.cms.page-header eyebrow="StoreZ / Content / Header" title="Header settings"
            description="Configure the storefront header and its announcement message.">
            <x-ui.button type="button" icon="check" wire:click="saveHeader" wire:loading.attr="disabled"
                wire:target="saveHeader" size="sm">
                <span wire:loading.remove wire:target="saveHeader">Save header</span>
                <span wire:loading wire:target="saveHeader">Saving...</span>
            </x-ui.button>
        </x-admin.cms.page-header>

        @if(session('status'))
            <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="space-y-5">
            <x-admin.cms.panel title="Branding" description="Choose a logo asset or provide a fallback URL.">
                <div class="space-y-4">
                    <label class="block text-sm font-semibold text-slate-700">
                        Logo URL
                        <x-ui.input wire:model.live="logo_url" placeholder="https://..." class="mt-1" />
                    </label>

                    <div class="rounded-xl border border-dashed border-blue-200 bg-blue-50/50 p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                            <div class="min-w-0 flex-1">
                                <label class="block text-sm font-semibold text-slate-700" for="header-logo-file">Upload a new logo</label>
                                <x-ui.input id="header-logo-file" type="file" wire:model="header_logo_file" accept="image/*" class="mt-1" />
                                <p class="mt-1 text-xs font-normal text-slate-500">Choose an image from your computer. Maximum 10 MB.</p>
                                @error('header_logo_file')<span class="mt-1 block text-xs font-normal text-red-600">{{ $message }}</span>@enderror
                            </div>
                            <x-ui.button type="button" variant="primary" color="blue" icon="arrow-up"
                                wire:click="uploadHeaderLogo" wire:loading.attr="disabled"
                                wire:target="uploadHeaderLogo,header_logo_file">
                                <span wire:loading.remove wire:target="uploadHeaderLogo">Upload and select</span>
                                <span wire:loading wire:target="uploadHeaderLogo">Uploading...</span>
                            </x-ui.button>
                        </div>
                    </div>

                    {{-- The shared media picker is intentionally hidden; header logos use direct upload. --}}
                    {{-- <x-admin.media-picker :assets="$mediaAssets" :selected="$logo_media_id" title="Header logo" context="header_logo" /> --}}

                    <label class="block text-sm font-semibold text-slate-700">
                        Support text
                        <x-ui.input wire:model.live="support_text" placeholder="Need help? Call us" class="mt-1" />
                    </label>
                </div>
            </x-admin.cms.panel>

            <x-admin.cms.panel title="Header behavior">
                <div class="space-y-3">
                    <p class="text-xs text-slate-500">Visibility changes are saved automatically.</p>
                    <x-ui.checkbox wire:model.live="show_search" label="Show search"
                        description="Display the storefront search field." variant="cards" size="sm" />
                    <x-ui.checkbox wire:model.live="sticky" label="Sticky header"
                        description="Keep the header visible while scrolling." variant="cards" size="sm" />
                    <x-ui.checkbox wire:model.live="show_announcement" label="Show announcements"
                        description="Display the announcement bar." variant="cards" size="sm" />

                    <label class="block text-sm font-semibold text-slate-700">
                        Desktop menu
                        <x-ui.select wire:model.live="desktop_menu_key" class="mt-1 w-full">
                            <x-ui.select.option value="">No menu</x-ui.select.option>
                            @foreach($menus as $menu)
                                <x-ui.select.option wire:key="header-desktop-menu-{{ $menu->key }}" value="{{ $menu->key }}">
                                    {{ $menu->name }}
                                </x-ui.select.option>
                            @endforeach
                        </x-ui.select>
                    </label>

                    <label class="block text-sm font-semibold text-slate-700">
                        Mobile menu
                        <x-ui.select wire:model.live="mobile_menu_key" class="mt-1 w-full">
                            <x-ui.select.option value="">Use category fallback</x-ui.select.option>
                            @foreach($menus as $menu)
                                <x-ui.select.option wire:key="header-mobile-menu-{{ $menu->key }}" value="{{ $menu->key }}">
                                    {{ $menu->name }}
                                </x-ui.select.option>
                            @endforeach
                        </x-ui.select>
                    </label>
                </div>
            </x-admin.cms.panel>

            <x-admin.cms.panel title="Announcement" description="Manage the single message shown in the storefront announcement bar.">
                @if($announcement)
                    <div class="flex flex-col gap-4 rounded-lg border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="font-bold text-slate-800">{{ $announcement->internal_title }}</h2>
                                <x-admin.cms.badge :tone="$announcement->status === 'published' ? 'success' : ($announcement->status === 'scheduled' ? 'warning' : 'neutral')">
                                    {{ ucfirst($announcement->status) }}
                                </x-admin.cms.badge>
                            </div>
                            <p class="mt-2 text-sm text-slate-600">{{ $announcement->message }}</p>
                            <p class="mt-2 text-xs text-slate-500">
                                {{ ucfirst($announcement->placement) }} · {{ ucfirst($announcement->priority) }} priority
                                @if($announcement->link_url) · Link: {{ $announcement->link_label ?: $announcement->link_url }} @endif
                            </p>
                        </div>
                        <div class="flex shrink-0 gap-2">
                            <x-ui.button type="button" variant="outline" color="blue" size="sm" icon="pencil-square"
                                wire:click="editAnnouncement({{ $announcement->id }})">
                                Edit
                            </x-ui.button>
                            <x-ui.button type="button" variant="outline" color="red" size="sm" icon="trash"
                                wire:click="deleteAnnouncement({{ $announcement->id }})"
                                wire:confirm="Permanently delete this announcement?">
                                Delete
                            </x-ui.button>
                        </div>
                    </div>
                @else
                    <div class="flex flex-col gap-3 rounded-lg border border-dashed border-slate-300 bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-slate-600">No announcement exists. Add one to display a message above the storefront header.</p>
                        <x-ui.button type="button" icon="plus" wire:click="openAnnouncementCreate">Add announcement</x-ui.button>
                    </div>
                @endif
            </x-admin.cms.panel>

            {{-- The storefront preview card is intentionally hidden to keep the Header editor full width. --}}
            {{--
            <x-admin.cms.panel title="Header preview" description="The saved settings rendered by the storefront header component.">
                <iframe src="{{ route('admin.content.header.preview') }}" title="Storefront header preview" class="h-64 w-full rounded-xl border border-slate-200 bg-white"></iframe>
                <p class="mt-3 text-xs text-slate-500">Save changes, then refresh this preview to verify responsive behavior.</p>
            </x-admin.cms.panel>
            --}}
        </div>

        <x-ui.modal id="header-announcement-editor" width="xl"
            :heading="$announcement_id ? 'Edit announcement' : 'Add announcement'"
            description="Create the single message shown in the storefront announcement bar.">
            <form wire:submit="saveAnnouncement" class="space-y-4">
                <label class="block text-sm font-semibold text-slate-700">
                    Internal title
                    <x-ui.input wire:model.live="announcement_internal_title" placeholder="Campaign or notice name" class="mt-1" />
                </label>
                @error('announcement_internal_title')<p class="text-xs text-red-600">{{ $message }}</p>@enderror

                <label class="block text-sm font-semibold text-slate-700">
                    Message
                    <x-ui.textarea wire:model.live="announcement_message" rows="4" placeholder="What should customers know?" class="mt-1" />
                </label>
                @error('announcement_message')<p class="text-xs text-red-600">{{ $message }}</p>@enderror

                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="block text-sm font-semibold text-slate-700">
                        Style
                        <x-ui.select wire:model.live="announcement_style" class="mt-1 w-full">
                            <x-ui.select.option value="info">Info</x-ui.select.option>
                            <x-ui.select.option value="success">Success</x-ui.select.option>
                            <x-ui.select.option value="warning">Warning</x-ui.select.option>
                            <x-ui.select.option value="danger">Danger</x-ui.select.option>
                        </x-ui.select>
                    </label>
                    <label class="block text-sm font-semibold text-slate-700">
                        Priority
                        <x-ui.select wire:model.live="announcement_priority" class="mt-1 w-full">
                            <x-ui.select.option value="low">Low</x-ui.select.option>
                            <x-ui.select.option value="normal">Normal</x-ui.select.option>
                            <x-ui.select.option value="high">High</x-ui.select.option>
                        </x-ui.select>
                    </label>
                </div>

                <label class="block text-sm font-semibold text-slate-700">
                    Placement
                    <x-ui.select wire:model.live="announcement_placement" class="mt-1 w-full">
                        <x-ui.select.option value="top_bar">Top bar</x-ui.select.option>
                        <x-ui.select.option value="storefront">Storefront</x-ui.select.option>
                        <x-ui.select.option value="checkout">Checkout</x-ui.select.option>
                        <x-ui.select.option value="account">Account</x-ui.select.option>
                    </x-ui.select>
                </label>

                <div class="grid gap-3 sm:grid-cols-2">
                    <x-ui.input wire:model.live="announcement_link_label" placeholder="Link label" />
                    <x-ui.input wire:model.live="announcement_link_url" type="url" placeholder="https://..." />
                </div>
                @error('announcement_link_url')<p class="text-xs text-red-600">{{ $message }}</p>@enderror

                <div class="grid gap-3 sm:grid-cols-2">
                    <x-ui.input wire:model.live="announcement_starts_at" type="datetime-local" />
                    <x-ui.input wire:model.live="announcement_ends_at" type="datetime-local" />
                </div>
                @error('announcement_starts_at')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                @error('announcement_ends_at')<p class="text-xs text-red-600">{{ $message }}</p>@enderror

                <label class="block text-sm font-semibold text-slate-700">
                    Status
                    <x-ui.select wire:model.live="announcement_status" class="mt-1 w-full">
                        <x-ui.select.option value="draft">Draft</x-ui.select.option>
                        <x-ui.select.option value="published">Published</x-ui.select.option>
                        <x-ui.select.option value="scheduled">Scheduled</x-ui.select.option>
                        <x-ui.select.option value="archived">Archived</x-ui.select.option>
                    </x-ui.select>
                </label>

                <x-ui.checkbox wire:model.live="announcement_dismissible" label="Allow customers to dismiss" size="sm" />

                <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                    <x-ui.button type="button" variant="outline" color="slate" wire:click="cancelAnnouncementEdit">
                        Cancel
                    </x-ui.button>
                    <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="saveAnnouncement">
                        <span wire:loading.remove wire:target="saveAnnouncement">
                            {{ $announcement_id ? 'Update announcement' : 'Save announcement' }}
                        </span>
                        <span wire:loading wire:target="saveAnnouncement">Saving...</span>
                    </x-ui.button>
                </div>
            </form>
        </x-ui.modal>
    </div>
</div>
