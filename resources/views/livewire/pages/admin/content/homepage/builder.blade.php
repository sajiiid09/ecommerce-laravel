<div class="p-5 sm:p-8">
    <div class="mx-auto max-w-[1480px]">
        <div class="mb-6">
            <x-admin.cms.page-header eyebrow="StoreZ / Content / Homepage Builder" title="Homepage Builder" description="Arrange storefront sections, manage content, and publish a new homepage version.">
                <x-slot:actions>
                    <a href="{{ url('/admin/content/banners?placementFilter=hero') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 hover:border-blue-300 hover:text-blue-700">
                        <x-ui.icon name="photo" class="size-4" /> Manage hero slides
                    </a>
                    <a href="{{ url('/') }}" target="_blank" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 hover:border-blue-300 hover:text-blue-700">
                        <x-ui.icon name="eye" class="size-4" /> Preview storefront
                    </a>
                    <x-ui.button type="button" icon="check" wire:click="saveHomepage" wire:loading.attr="disabled" wire:target="saveHomepage">
                        <span wire:loading.remove wire:target="saveHomepage">Save layout</span>
                        <span wire:loading wire:target="saveHomepage">Saving...</span>
                    </x-ui.button>
                </x-slot:actions>
            </x-admin.cms.page-header>
        </div>

        @if(session('status'))
            <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('status') }}</div>
        @endif

        <div class="space-y-5">
            <x-admin.cms.panel title="Homepage sections">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-500">{{ count($sections) }} sections configured</p>
                    <x-ui.button type="button" icon="plus" wire:click="addSection" wire:loading.attr="disabled" wire:target="addSection">Add section</x-ui.button>
                </div>

                <div class="overflow-x-auto">
                    <x-ui.table class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm" table:class="w-full min-w-[1000px] text-left">
                        <colgroup>
                            <col class="w-[7%]">
                            <col class="w-[28%]">
                            <col class="w-[14%]">
                            <col class="w-[18%]">
                            <col class="w-[17%]">
                            <col class="w-[16%]">
                        </colgroup>
                        <x-ui.table.header class="bg-slate-50 text-[11px] font-semibold tracking-wide text-slate-500">
                            <x-ui.table.columns>
                                <x-ui.table.head class="w-20">Order</x-ui.table.head>
                                <x-ui.table.head>Section</x-ui.table.head>
                                <x-ui.table.head>Type</x-ui.table.head>
                                <x-ui.table.head>Query</x-ui.table.head>
                                <x-ui.table.head>Status</x-ui.table.head>
                                <x-ui.table.head class="text-right">Actions</x-ui.table.head>
                            </x-ui.table.columns>
                        </x-ui.table.header>
                        <x-ui.table.rows class="divide-y divide-slate-100">
                            @forelse($sections as $index => $section)
                                <x-ui.table.row :key="'homepage-section-'.$section['section_key']" class="text-slate-700 hover:bg-slate-50">
                                    <x-ui.table.cell class="px-4 py-4">
                                        <span class="grid size-8 place-items-center rounded-md bg-slate-100 text-xs font-extrabold text-slate-600">{{ $index + 1 }}</span>
                                    </x-ui.table.cell>
                                    <x-ui.table.cell class="px-4 py-4">
                                        <p class="font-bold text-slate-800">{{ $section['title'] ?: ucwords(str_replace('_', ' ', $section['type'])) }}</p>
                                        <p class="mt-1 text-xs text-slate-400">{{ $section['section_key'] }}</p>
                                    </x-ui.table.cell>
                                    <x-ui.table.cell class="px-4 py-4 text-xs font-semibold text-slate-500">{{ ucwords(str_replace('_', ' ', $section['type'])) }}</x-ui.table.cell>
                                    <x-ui.table.cell class="px-4 py-4 text-xs font-semibold text-slate-500">{{ $this->sectionQueryLabel($section) }}</x-ui.table.cell>
                                    <x-ui.table.cell class="px-4 py-4">
                                        <x-ui.checkbox wire:model.live="sections.{{ $index }}.enabled" :label="$section['enabled'] ? 'Enabled' : 'Disabled'" size="sm" />
                                    </x-ui.table.cell>
                                    <x-ui.table.cell class="px-4 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <x-ui.button type="button" variant="outline" color="slate" size="xs" wire:click="move({{ $index }}, -1)" :disabled="$index === 0" aria-label="Move {{ $section['title'] ?: $section['type'] }} up">↑</x-ui.button>
                                            <x-ui.button type="button" variant="outline" color="slate" size="xs" wire:click="move({{ $index }}, 1)" :disabled="$index === count($sections) - 1" aria-label="Move {{ $section['title'] ?: $section['type'] }} down">↓</x-ui.button>
                                            <x-ui.button type="button" variant="outline" color="blue" size="xs" wire:click="editSection({{ $index }})">Edit</x-ui.button>
                                            <x-ui.button type="button" variant="outline" color="red" size="xs" wire:click="removeSection({{ $index }})" wire:confirm="Remove this homepage section?">Delete</x-ui.button>
                                        </div>
                                    </x-ui.table.cell>
                                </x-ui.table.row>
                            @empty
                                <x-ui.table.empty>No homepage sections yet.</x-ui.table.empty>
                            @endforelse
                        </x-ui.table.rows>
                    </x-ui.table>
                </div>
            </x-admin.cms.panel>

            <x-admin.cms.panel title="Version history" description="Restore a saved version or publish it to the storefront.">
                <div class="space-y-3">
                    @forelse($revisions as $revision)
                        <div wire:key="homepage-revision-{{ $revision->id }}" class="rounded-lg border border-slate-200 p-3">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-sm font-bold text-slate-800">Version {{ $revision->version }}</span>
                                <x-admin.cms.badge :tone="$revision->status === 'published' ? 'success' : 'neutral'">{{ ucfirst($revision->status) }}</x-admin.cms.badge>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">{{ $revision->created_at?->format('M d, Y H:i') }}</p>
                            <div class="mt-3 flex gap-3 text-xs font-bold">
                                <button wire:click="restoreRevision({{ $revision->id }})" wire:confirm="Restore this homepage revision?" wire:loading.attr="disabled" wire:target="restoreRevision({{ $revision->id }})" class="text-blue-600 disabled:opacity-60">Restore</button>
                                <button wire:click="publishRevision({{ $revision->id }})" wire:confirm="Publish this homepage revision?" wire:loading.attr="disabled" wire:target="publishRevision({{ $revision->id }})" class="text-emerald-600 disabled:opacity-60">Publish</button>
                            </div>
                        </div>
                    @empty
                        <p class="py-5 text-sm text-slate-500">No saved revisions yet.</p>
                    @endforelse
                </div>
            </x-admin.cms.panel>
        </div>

        @php($selected = $sectionDraft !== [] ? $sectionDraft : null)
        <x-ui.modal id="homepage-section-editor" width="5xl" :heading="$isAddingSection || $sectionDraft === [] ? 'Add section' : 'Edit section'" :description="$isAddingSection || $sectionDraft === [] ? 'Choose a section type, configure it, and add it to the current draft.' : 'Update the selected homepage section and save it to the current draft.'" stickyFooter>
            @if($selected)
                <form id="homepage-section-form" wire:submit="saveSection" class="space-y-5">
                    @if($isAddingSection)
                        <div class="rounded-lg border border-blue-100 bg-blue-50 p-3">
                            <label class="block text-sm font-semibold text-slate-700">
                                Section type
                                <select wire:model.live="sectionDraft.type" aria-label="Section type" class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                                    @foreach($sectionTypes as $type)
                                        <option wire:key="homepage-modal-type-{{ $type }}" value="{{ $type }}">{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <p class="mt-1 text-xs text-slate-500">This determines which storefront section will be created.</p>
                        </div>
                    @endif
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Section {{ $isAddingSection ? count($sections) + 1 : $editingSectionIndex + 1 }}</p>
                            <p class="text-sm font-bold text-slate-800">{{ ucwords(str_replace('_', ' ', $selected['type'])) }}</p>
                        </div>
                        <x-ui.checkbox wire:model.live="sectionDraft.enabled" label="Enabled" size="sm" />
                    </div>

                    <div class="space-y-4">
                        <label class="block text-sm font-semibold text-slate-700">Section title<x-ui.input wire:model.live="sectionDraft.title" class="mt-1" /></label>
                        <label class="block text-sm font-semibold text-slate-700">Eyebrow<x-ui.input wire:model.live="sectionDraft.eyebrow" class="mt-1" /></label>
                        <label class="block text-sm font-semibold text-slate-700">Subtitle<x-ui.textarea wire:model.live="sectionDraft.subtitle" rows="3" class="mt-1" /></label>

                        @if($selected['type'] === 'products')
                            <div class="space-y-4 rounded-lg border border-blue-100 bg-blue-50/50 p-4">
                                <div>
                                    <p class="text-sm font-bold text-slate-800">Product query</p>
                                    <p class="mt-1 text-xs text-slate-500">Choose which published products this carousel should display.</p>
                                </div>
                                <label class="block text-sm font-semibold text-slate-700">Product source
                                    <x-ui.select wire:model.live="sectionDraft.settings.source" class="mt-1 w-full">
                                        <x-ui.select.option value="featured">Featured</x-ui.select.option>
                                        <x-ui.select.option value="newest">Newest</x-ui.select.option>
                                        <x-ui.select.option value="bestsellers">Best sellers</x-ui.select.option>
                                        <x-ui.select.option value="on_sale">On sale</x-ui.select.option>
                                        <x-ui.select.option value="category">Category</x-ui.select.option>
                                        <x-ui.select.option value="brand">Brand</x-ui.select.option>
                                    </x-ui.select>
                                </label>
                                @if(($selected['settings']['source'] ?? 'featured') === 'category')
                                    <label class="block text-sm font-semibold text-slate-700">Category
                                        <x-ui.select wire:model.live="sectionDraft.settings.category" placeholder="Select category" class="mt-1 w-full">
                                            @foreach($categoryOptions as $category)
                                                <x-ui.select.option wire:key="homepage-category-{{ $category['id'] }}" value="{{ $category['slug'] }}">{{ $category['name'] }}</x-ui.select.option>
                                            @endforeach
                                        </x-ui.select>
                                    </label>
                                @endif
                                @if(($selected['settings']['source'] ?? 'featured') === 'brand')
                                    <label class="block text-sm font-semibold text-slate-700">Brand
                                        <x-ui.select wire:model.live="sectionDraft.settings.brand" placeholder="Select brand" class="mt-1 w-full">
                                            @foreach($brandOptions as $brand)
                                                <x-ui.select.option wire:key="homepage-brand-{{ $brand['id'] }}" value="{{ $brand['slug'] }}">{{ $brand['name'] }}</x-ui.select.option>
                                            @endforeach
                                        </x-ui.select>
                                    </label>
                                @endif
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <label class="block text-sm font-semibold text-slate-700">Sort order
                                        <x-ui.select wire:model.live="sectionDraft.settings.sort" class="mt-1 w-full">
                                            <x-ui.select.option value="default">Default</x-ui.select.option>
                                            <x-ui.select.option value="newest">Newest</x-ui.select.option>
                                            <x-ui.select.option value="price_asc">Price: low to high</x-ui.select.option>
                                            <x-ui.select.option value="price_desc">Price: high to low</x-ui.select.option>
                                        </x-ui.select>
                                    </label>
                                    <label class="block text-sm font-semibold text-slate-700">Product limit
                                        <x-ui.input type="number" min="1" max="24" wire:model.live="sectionDraft.settings.limit" class="mt-1" />
                                    </label>
                                </div>
                            </div>
                        @endif
                        @if(in_array($selected['type'], ['hero', 'banners'], true))
                            <label class="block text-sm font-semibold text-slate-700">Call to action<x-ui.input wire:model.live="sectionDraft.settings.cta" class="mt-1" placeholder="Shop now" /></label>
                        @endif
                        @if($selected['type'] === 'hero')
                            <label class="block text-sm font-semibold text-slate-700">CTA URL<x-ui.input wire:model.live="sectionDraft.settings.url" class="mt-1" placeholder="/offers" /></label>
                        @endif
                        @if(in_array($selected['type'], ['categories', 'brands'], true))
                            <label class="block text-sm font-semibold text-slate-700">Item limit<x-ui.input type="number" min="1" max="24" wire:model.live="sectionDraft.settings.limit" class="mt-1" /></label>
                        @endif
                        @if($selected['type'] === 'banners')
                            <div class="grid gap-3 sm:grid-cols-2">
                                <label class="block text-sm font-semibold text-slate-700">Placement<select wire:model.live="sectionDraft.settings.placement" class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"><option value="homepage">Homepage</option><option value="offers">Offers</option><option value="category">Category</option><option value="global">Global</option></select></label>
                                <label class="block text-sm font-semibold text-slate-700">Banner limit<x-ui.input type="number" min="1" max="12" wire:model.live="sectionDraft.settings.limit" class="mt-1" /></label>
                            </div>
                        @endif
                        @if(in_array($selected['type'], ['shop_by_need', 'newsletter'], true))
                            <label class="block text-sm font-semibold text-slate-700">Content JSON<x-ui.textarea wire:model.live="sectionDraft.settings.content_json" rows="4" class="mt-1 font-mono text-xs" placeholder="[{&quot;title&quot;:&quot;...&quot;}]" spellcheck="false" /></label>
                        @endif

                        @if($selected['type'] === 'testimonials')
                            @php($testimonials = $selected['settings']['testimonials'] ?? [])
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                <div class="flex items-center justify-between gap-3">
                                    <div><p class="text-sm font-bold text-slate-800">Testimonials</p><p class="mt-1 text-xs text-slate-500">Add customer feedback and arrange the carousel order.</p></div>
                                    <x-ui.button type="button" variant="outline" color="blue" size="sm" icon="plus" wire:click="addTestimonial">Add testimonial</x-ui.button>
                                </div>
                                <div class="mt-4 space-y-3">
                                    @forelse($testimonials as $testimonialIndex => $testimonial)
                                        <div wire:key="homepage-testimonial-{{ $testimonial['id'] ?? $testimonialIndex }}" class="rounded-lg border border-slate-200 bg-white p-3">
                                            <div class="flex items-start justify-between gap-3">
                                                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Testimonial {{ $testimonialIndex + 1 }}</p>
                                                <div class="flex items-center gap-2 text-xs font-bold">
                                                    <button type="button" wire:click="moveTestimonial({{ $testimonialIndex }}, -1)" class="text-slate-500 disabled:opacity-40" @disabled($testimonialIndex === 0) aria-label="Move testimonial up">↑</button>
                                                    <button type="button" wire:click="moveTestimonial({{ $testimonialIndex }}, 1)" class="text-slate-500 disabled:opacity-40" @disabled($testimonialIndex === count($testimonials) - 1) aria-label="Move testimonial down">↓</button>
                                                    <button type="button" wire:click="removeTestimonial({{ $testimonialIndex }})" wire:confirm="Remove this testimonial?" class="text-red-600">Remove</button>
                                                </div>
                                            </div>
                                            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                                <label class="text-xs font-semibold text-slate-700">Name<x-ui.input wire:model.live="sectionDraft.settings.testimonials.{{ $testimonialIndex }}.name" class="mt-1" /></label>
                                                <label class="text-xs font-semibold text-slate-700">Role<x-ui.input wire:model.live="sectionDraft.settings.testimonials.{{ $testimonialIndex }}.role" class="mt-1" /></label>
                                                <label class="text-xs font-semibold text-slate-700">Rating<select wire:model.live="sectionDraft.settings.testimonials.{{ $testimonialIndex }}.rating" class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">@for($rating = 1; $rating <= 5; $rating++)<option value="{{ $rating }}">{{ $rating }} stars</option>@endfor</select></label>
                                                <x-ui.checkbox class="self-end pb-2" wire:model.live="sectionDraft.settings.testimonials.{{ $testimonialIndex }}.enabled" label="Show this testimonial" size="sm" />
                                                <label class="text-xs font-semibold text-slate-700 sm:col-span-2">Quote<x-ui.textarea wire:model.live="sectionDraft.settings.testimonials.{{ $testimonialIndex }}.quote" rows="3" class="mt-1" /></label>
                                            </div>
                                            <button type="button" x-data x-on:click="$dispatch('open-media-picker', { context: 'homepage_testimonial_avatar' }); $wire.selectedTestimonialIndex = {{ $testimonialIndex }}" class="mt-3 rounded-lg border border-dashed border-slate-300 px-3 py-2 text-left text-xs font-bold text-slate-600 hover:border-blue-400 hover:text-blue-700">Choose optional avatar</button>
                                        </div>
                                    @empty
                                        <p class="py-5 text-center text-xs text-slate-500">No testimonials yet.</p>
                                    @endforelse
                                </div>
                            </div>
                        @endif

                        <div class="grid gap-3 sm:grid-cols-2">
                            <button type="button" x-data x-on:click="$dispatch('open-media-picker', { context: 'homepage_desktop' })" class="rounded-lg border border-dashed border-slate-300 px-3 py-3 text-left text-xs font-bold text-slate-600 hover:border-blue-400 hover:text-blue-700">Choose desktop media</button>
                            <button type="button" x-data x-on:click="$dispatch('open-media-picker', { context: 'homepage_mobile' })" class="rounded-lg border border-dashed border-slate-300 px-3 py-3 text-left text-xs font-bold text-slate-600 hover:border-blue-400 hover:text-blue-700">Choose mobile media</button>
                        </div>
                    </div>

                </form>
            @endif
            <x-slot:footer>
                <div class="flex w-full justify-end gap-2">
                    <x-ui.button type="button" variant="outline" color="slate" wire:click="$dispatch('close-modal', { id: 'homepage-section-editor' })">Cancel</x-ui.button>
                    <x-ui.button type="submit" form="homepage-section-form" wire:loading.attr="disabled" wire:target="saveSection">
                        <span wire:loading.remove wire:target="saveSection">{{ $isAddingSection || $sectionDraft === [] ? 'Save section' : 'Update section' }}</span>
                        <span wire:loading wire:target="saveSection">{{ $isAddingSection || $sectionDraft === [] ? 'Saving...' : 'Updating...' }}</span>
                    </x-ui.button>
                </div>
            </x-slot:footer>
        </x-ui.modal>

        <x-admin.media-picker :assets="$mediaAssets" title="Select desktop media" context="homepage_desktop" modal />
        <x-admin.media-picker :assets="$mediaAssets" title="Select mobile media" context="homepage_mobile" modal />
        <x-admin.media-picker :assets="$mediaAssets" title="Select testimonial avatar" context="homepage_testimonial_avatar" modal />
    </div>
</div>
