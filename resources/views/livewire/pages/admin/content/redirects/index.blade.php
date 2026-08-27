<div class="p-5 sm:p-8">
    <div class="mx-auto max-w-[1480px]">
        <x-admin.cms.page-header eyebrow="StoreZ / Content / Redirects" title="Redirect manager"
            description="Protect SEO and customer journeys when storefront paths change.">
            <x-ui.button type="button" icon="plus" wire:click="openCreate" size="sm">Add redirect</x-ui.button>
            <x-ui.button as="a" href="{{ url('/admin/content/redirects/export') }}" variant="outline" color="slate"
                icon="arrow-down-tray" size="sm">
                Export CSV
            </x-ui.button>
        </x-admin.cms.page-header>
        @if(session('status'))
            <div
                class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                {{ session('status') }}
        </div>@endif
        @if($errors->any())
            <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
        </div>@endif
        <div class="mb-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($stats as $label => $value)<x-admin.cms.stat-card :label="$label" :value="number_format($value)"
            accent="blue" />@endforeach
        </div>
        <div>
            <div class="space-y-5">
                <x-admin.cms.panel title="Redirects"
                    description="Edit destinations and identify disabled or chained redirects.">
                    <x-ui.input wire:model.live.debounce.300ms="search"
                        placeholder="Search source paths or destinations..." leftIcon="magnifying-glass" />
                    <x-ui.table :paginator="$redirects" wire:loading loadOn="pagination, search"
                        table:class="min-w-[760px] text-left"
                        class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                        <x-ui.table.header
                            class="bg-slate-50 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                            <x-ui.table.columns>
                                <x-ui.table.head>Source</x-ui.table.head>
                                <x-ui.table.head>Destination</x-ui.table.head>
                                <x-ui.table.head>Code</x-ui.table.head>
                                <x-ui.table.head>Health</x-ui.table.head>
                                <x-ui.table.head>Actions</x-ui.table.head>
                            </x-ui.table.columns>
                        </x-ui.table.header>
                        <x-ui.table.rows>
                            @forelse($redirects as $redirect)
                                <x-ui.table.row :key="$redirect->id" class="text-slate-700 hover:bg-slate-50">
                                    <x-ui.table.cell
                                        class="px-3 py-4 font-bold text-slate-800">{{ $redirect->from_path }}</x-ui.table.cell>
                                    <x-ui.table.cell
                                        class="max-w-xs truncate px-3 py-4 text-slate-600">{{ $redirect->to_url }}</x-ui.table.cell>
                                    <x-ui.table.cell class="px-3 py-4"><x-admin.cms.badge
                                            tone="info">{{ $redirect->status_code }}</x-admin.cms.badge></x-ui.table.cell>
                                    <x-ui.table.cell class="px-3 py-4">@if(isset($health[$redirect->id]))<x-admin.cms.badge
                                    tone="warning">{{ implode(', ', $health[$redirect->id]) }}</x-admin.cms.badge>@else<x-admin.cms.badge
                                            tone="success">Healthy</x-admin.cms.badge>@endif</x-ui.table.cell>
                                    <x-ui.table.cell class="px-3 py-4">
                                        <div class="flex items-center gap-3"><button type="button"
                                                wire:click="editRedirect({{ $redirect->id }})"
                                                class="font-bold text-blue-600">Edit</button><button type="button"
                                                wire:click="deleteRedirect({{ $redirect->id }})"
                                                wire:confirm="Delete this redirect?"
                                                class="font-bold text-red-600">Delete</button></div>
                                    </x-ui.table.cell>
                                </x-ui.table.row>
                            @empty
                                <x-ui.table.empty>No redirects match your search.</x-ui.table.empty>
                            @endforelse
                        </x-ui.table.rows>
                    </x-ui.table>
                </x-admin.cms.panel>
            </div>
            {{-- The redirect editor and CSV importer were moved out of the page layout. --}}
            {{--
            <div class="space-y-5">
                <x-admin.cms.panel :title="$editingId ? 'Edit redirect' : 'Add redirect'"
                    description="Use a relative source path and a validated destination.">
                    <div class="space-y-3"><label class="block text-sm font-semibold text-slate-700">From path<input
                                wire:model.live="from_path" placeholder="/old-path"
                                class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm"></label><label
                            class="block text-sm font-semibold text-slate-700">To URL<input wire:model.live="to_url"
                                placeholder="/new-path or https://..."
                                class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm"></label><label
                            class="block text-sm font-semibold text-slate-700">Status code<select
                                wire:model.live="status_code"
                                class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
                                <option>301</option>
                                <option>302</option>
                                <option>307</option>
                                <option>308</option>
                            </select></label><x-ui.checkbox wire:model.live="enabled" label="Enabled" size="sm" />
                        <div class="flex gap-2"><button wire:click="saveRedirect" wire:loading.attr="disabled"
                                class="flex-1 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-bold text-white disabled:opacity-60">{{
                                $editingId ? 'Update redirect' : 'Save redirect' }}</button>@if($editingId)<button
                                wire:click="cancelEdit"
                                class="rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-bold text-slate-600">Cancel</button>@endif
                        </div>
                    </div>
                </x-admin.cms.panel>
            </div>
            --}}
        </div>

        <x-ui.modal id="redirect-editor" width="lg" :heading="$editingId ? 'Edit redirect' : 'Add redirect'"
            description="Use a relative source path and a validated destination.">
            <form wire:submit="saveRedirect" class="space-y-4">
                <label class="block text-sm font-semibold text-slate-700">From path<x-ui.input
                        wire:model.live="from_path" placeholder="/old-path" class="mt-1" /></label>
                @error('from_path')
                <p class="text-xs text-red-600">{{ $message }}</p>@enderror

                <label class="block text-sm font-semibold text-slate-700">To URL<x-ui.input wire:model.live="to_url"
                        placeholder="/new-path or https://..." class="mt-1" /></label>
                @error('to_url')
                <p class="text-xs text-red-600">{{ $message }}</p>@enderror

                <label class="block text-sm font-semibold text-slate-700">Status code
                    <x-ui.select wire:model.live="status_code" class="mt-1 w-full">
                        <x-ui.select.option value="301">301</x-ui.select.option>
                        <x-ui.select.option value="302">302</x-ui.select.option>
                        <x-ui.select.option value="307">307</x-ui.select.option>
                        <x-ui.select.option value="308">308</x-ui.select.option>
                    </x-ui.select>
                </label>
                @error('status_code')
                <p class="text-xs text-red-600">{{ $message }}</p>@enderror

                <x-ui.checkbox wire:model.live="enabled" label="Enabled"
                    description="Apply this redirect on the storefront." size="sm" />

                <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                    <x-ui.button type="button" variant="outline" color="slate"
                        wire:click="cancelEdit">Cancel</x-ui.button>
                    <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="saveRedirect">
                        <span wire:loading.remove
                            wire:target="saveRedirect">{{ $editingId ? 'Update redirect' : 'Save redirect' }}</span>
                        <span wire:loading wire:target="saveRedirect">Saving...</span>
                    </x-ui.button>
                </div>
            </form>
        </x-ui.modal>
    </div>
</div>