<div class="p-5 sm:p-8">
    <div class="mx-auto max-w-[1480px]">
        <x-admin.cms.page-header eyebrow="StoreZ / Content / Homepage Builder" title="Homepage Builder" description="Arrange storefront sections, manage content, and publish a new homepage version.">
            <x-slot:actions>
                <a href="{{ url('/') }}" target="_blank" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 hover:border-blue-300 hover:text-blue-700">
                    <x-ui.icon name="eye" class="size-4" /> Preview storefront
                </a>
                <button wire:click="saveHomepage" wire:loading.attr="disabled" wire:target="saveHomepage" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-700 disabled:opacity-60">
                    <x-ui.icon name="check" class="size-4" />
                    <span wire:loading.remove wire:target="saveHomepage">Save layout</span>
                    <span wire:loading wire:target="saveHomepage">Saving...</span>
                </button>
            </x-slot:actions>
        </x-admin.cms.page-header>

        @if(session('status'))
            <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('status') }}</div>
        @endif

        <div class="grid gap-5 xl:grid-cols-[280px_minmax(0,1fr)_320px]">
            <x-admin.cms.panel title="Homepage sections" description="Drag order controls are available on each section.">
                <div class="mb-4 flex gap-2">
                    <select wire:model.live="sectionType" class="min-w-0 flex-1 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                        @foreach($sectionTypes as $type)
                            <option wire:key="homepage-type-{{ $type }}" value="{{ $type }}">{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                        @endforeach
                    </select>
                    <button wire:click="addSection" wire:loading.attr="disabled" wire:target="addSection" class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-bold text-white disabled:opacity-60" aria-label="Add section">+</button>
                </div>

                <div class="space-y-2">
                    @forelse($sections as $index => $section)
                        <button wire:key="homepage-section-{{ $section['section_key'] }}" wire:click="selectSection({{ $index }})" class="group flex w-full items-center gap-3 rounded-lg border px-3 py-3 text-left transition {{ $selectedSectionIndex === $index ? 'border-blue-300 bg-blue-50 ring-1 ring-blue-100' : 'border-slate-200 bg-white hover:border-blue-200' }}">
                            <span class="grid size-8 shrink-0 place-items-center rounded-md {{ $selectedSectionIndex === $index ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-500' }} text-xs font-extrabold">{{ $index + 1 }}</span>
                            <span class="min-w-0 flex-1"><span class="block truncate text-sm font-bold text-slate-800">{{ $section['title'] ?: ucwords(str_replace('_', ' ', $section['type'])) }}</span><span class="mt-0.5 block text-[11px] uppercase tracking-wide text-slate-400">{{ str_replace('_', ' ', $section['type']) }}</span></span>
                            <span class="size-2 rounded-full {{ $section['enabled'] ? 'bg-emerald-500' : 'bg-slate-300' }}" title="{{ $section['enabled'] ? 'Enabled' : 'Disabled' }}"></span>
                        </button>
                    @empty
                        <p class="py-8 text-center text-sm text-slate-500">No homepage sections yet.</p>
                    @endforelse
                </div>
            </x-admin.cms.panel>

            @php($selected = $sections[$selectedSectionIndex] ?? null)
            <x-admin.cms.panel :title="$selected ? 'Edit section' : 'Section editor'" description="Update the selected section content and display options.">
                @if($selected)
                    <div class="mb-5 flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                        <div><p class="text-xs font-bold uppercase tracking-wide text-slate-400">Section {{ $selectedSectionIndex + 1 }}</p><p class="text-sm font-bold text-slate-800">{{ ucwords(str_replace('_', ' ', $selected['type'])) }}</p></div>
                        <label class="flex items-center gap-2 text-xs font-semibold text-slate-600"><input type="checkbox" wire:model.live="sections.{{ $selectedSectionIndex }}.enabled" class="rounded border-slate-300 text-blue-600"> Enabled</label>
                    </div>
                    <div class="space-y-4">
                        <label class="block text-sm font-semibold text-slate-700">Section title<input wire:model.live="sections.{{ $selectedSectionIndex }}.title" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></label>
                        <label class="block text-sm font-semibold text-slate-700">Eyebrow<input wire:model.live="sections.{{ $selectedSectionIndex }}.eyebrow" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></label>
                        <label class="block text-sm font-semibold text-slate-700">Subtitle<textarea wire:model.live="sections.{{ $selectedSectionIndex }}.subtitle" rows="3" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></textarea></label>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <button type="button" x-data x-on:click="$dispatch('open-media-picker', { context: 'homepage_desktop' })" class="rounded-lg border border-dashed border-slate-300 px-3 py-3 text-left text-xs font-bold text-slate-600 hover:border-blue-400 hover:text-blue-700">Choose desktop media</button>
                            <button type="button" x-data x-on:click="$dispatch('open-media-picker', { context: 'homepage_mobile' })" class="rounded-lg border border-dashed border-slate-300 px-3 py-3 text-left text-xs font-bold text-slate-600 hover:border-blue-400 hover:text-blue-700">Choose mobile media</button>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-xs text-slate-500">Section-specific settings are stored with the homepage revision and rendered by the storefront catalog provider.</div>
                    </div>
                @else
                    <p class="py-12 text-center text-sm text-slate-500">Select a section to start editing.</p>
                @endif
            </x-admin.cms.panel>

            <div class="space-y-5">
                <x-admin.cms.panel title="Storefront preview" description="A quick view of the current section order.">
                    <div class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                        <div class="flex items-center justify-between border-b border-slate-200 bg-white px-3 py-2"><span class="text-xs font-bold text-slate-700">StoreZ storefront</span><span class="text-[10px] text-slate-400">Desktop</span></div>
                        <div class="space-y-2 p-3">
                            @foreach($sections as $index => $section)
                                <div wire:key="homepage-preview-{{ $section['section_key'] }}" class="rounded-lg border border-slate-200 bg-white p-3 {{ $section['enabled'] ? '' : 'opacity-40' }}"><div class="flex items-center gap-2"><span class="text-[10px] font-bold text-slate-400">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span><span class="truncate text-xs font-bold text-slate-700">{{ $section['title'] ?: ucwords(str_replace('_', ' ', $section['type'])) }}</span></div><div class="mt-2 h-2 w-2/3 rounded bg-slate-100"></div><div class="mt-1 h-2 w-1/2 rounded bg-slate-100"></div></div>
                            @endforeach
                        </div>
                    </div>
                </x-admin.cms.panel>

                <x-admin.cms.panel title="Version history" description="Restore a saved version or publish it to the storefront.">
                    <div class="space-y-3">
                        @forelse($revisions as $revision)
                            <div wire:key="homepage-revision-{{ $revision->id }}" class="rounded-lg border border-slate-200 p-3"><div class="flex items-center justify-between gap-2"><span class="text-sm font-bold text-slate-800">Version {{ $revision->version }}</span><x-admin.cms.badge :tone="$revision->status === 'published' ? 'success' : 'neutral'">{{ ucfirst($revision->status) }}</x-admin.cms.badge></div><p class="mt-1 text-xs text-slate-500">{{ $revision->created_at?->format('M d, Y H:i') }}</p><div class="mt-3 flex gap-3 text-xs font-bold"><button wire:click="restoreRevision({{ $revision->id }})" wire:confirm="Restore this homepage revision?" wire:loading.attr="disabled" wire:target="restoreRevision({{ $revision->id }})" class="text-blue-600 disabled:opacity-60">Restore</button><button wire:click="publishRevision({{ $revision->id }})" wire:confirm="Publish this homepage revision?" wire:loading.attr="disabled" wire:target="publishRevision({{ $revision->id }})" class="text-emerald-600 disabled:opacity-60">Publish</button></div></div>
                        @empty
                            <p class="py-5 text-sm text-slate-500">No saved revisions yet.</p>
                        @endforelse
                    </div>
                </x-admin.cms.panel>
            </div>
        </div>
        <x-admin.media-picker :assets="$mediaAssets" title="Select desktop media" context="homepage_desktop" modal />
        <x-admin.media-picker :assets="$mediaAssets" title="Select mobile media" context="homepage_mobile" modal />
    </div>
</div>
