<div class="p-5 sm:p-8">
    <div class="mx-auto max-w-[1480px]">
        <div class="mb-6 flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
            <div>
                <div class="mb-3 flex items-center gap-2 text-xs font-semibold text-[#2563eb]">
                    <a href="{{ url('/admin') }}">Dashboard</a>
                    <span class="text-[#9ca3af]">&rsaquo;</span>
                    <a href="{{ url('/admin/catalog/products') }}">Products</a>
                    <span class="text-[#9ca3af]">&rsaquo;</span>
                    <span class="text-[#6b7280]">{{ $product?->exists ? 'Edit Product' : 'Add Product' }}</span>
                </div>
                <h1 class="text-[28px] font-extrabold tracking-tight text-[#111827]">{{ $product?->exists ? 'Edit Product' : 'Add New Product' }}</h1>
                <p class="mt-1 text-sm text-[#6b7280]">{{ $product?->exists ? 'Update your product information and settings.' : 'Create a new product and configure pricing, stock and storefront information.' }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if($product?->exists)
                    <button class="flex items-center gap-2 rounded-lg border border-[#e5e7eb] bg-white px-4 py-2.5 text-sm font-semibold text-[#374151] hover:bg-[#f9fafb]">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                        View Product
                    </button>
                @endif
                <button form="product-form" type="submit" wire:loading.attr="disabled" wire:target="saveProduct" class="flex items-center gap-2 rounded-lg bg-[#2563eb] px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#1d4ed8] disabled:cursor-wait disabled:opacity-60">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    <span wire:loading.remove wire:target="saveProduct">{{ $product?->exists ? 'Save Changes' : 'Publish Product' }}</span><span wire:loading wire:target="saveProduct">Saving...</span>
                </button>
            </div>
        </div>

        @if(session('status'))
            <div class="mb-5 rounded-xl border border-[#bbf7d0] bg-[#dcfce7] p-3 text-sm font-semibold text-[#16a34a]">{{ session('status') }}</div>
        @endif

        @if($errors->any())
            <div class="mb-5 rounded-xl border border-[#fecaca] bg-[#fef2f2] p-4 text-sm text-[#dc2626]">
                <p class="font-bold">Please check the form</p>
                <ul class="mt-1 list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="product-form" wire:submit="saveProduct" class="grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="space-y-5">

                <section class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm sm:p-6">
                    <div class="mb-5 flex items-center justify-between">
                        <div><h2 class="font-bold text-[#111827]">Basic Information</h2></div>
                        <span class="rounded-full bg-[#eff6ff] px-2.5 py-1 text-[11px] font-bold text-[#2563eb]">Simple products use one default variant</span>
                    </div>
                    <label class="mt-4 block space-y-1.5 text-sm font-semibold text-[#111827]">Additional Categories
                        <x-ui.select wire:model.live="category_ids" multiple searchable clearable placeholder="Select additional categories" class="w-full" triggerClass="!rounded-lg !border-[#e5e7eb] !bg-white">
                            @foreach($categories as $category)
                                <x-ui.select.option wire:key="product-category-{{ $category->id }}" value="{{ $category->id }}">{{ $category->name }}</x-ui.select.option>
                            @endforeach
                        </x-ui.select>
                        <span class="block text-xs font-normal text-[#9ca3af]">Hold Ctrl/Cmd to select multiple categories. The primary category is included automatically.</span>
                    </label>
                    <div class="grid gap-4 md:grid-cols-3">
                        <label class="space-y-1.5 text-sm font-semibold text-[#111827]">
                            Product Name <span class="text-[#ef4444]">*</span>
                            <x-ui.input wire:model.live="name" placeholder="Enter product name" class="!rounded-lg" controlClass="!rounded-lg !border-[#e5e7eb] !bg-white" />
                        </label>
                        <label class="space-y-1.5 text-sm font-semibold text-[#111827]">
                            Slug <span class="text-[#ef4444]">*</span>
                            <x-ui.input wire:model.live="slug" placeholder="Enter product slug" class="!rounded-lg" controlClass="!rounded-lg !border-[#e5e7eb] !bg-white" />
                        </label>
                        <label class="space-y-1.5 text-sm font-semibold text-[#111827]">
                            Product Type <span class="text-[#ef4444]">*</span>
                            <x-ui.select wire:model.live="product_type" placeholder="Select product type" class="w-full" triggerClass="!rounded-lg !border-[#e5e7eb] !bg-white">
                                <x-ui.select.option value="simple">Simple</x-ui.select.option>
                                <x-ui.select.option value="variable">Variable</x-ui.select.option>
                            </x-ui.select>
                        </label>
                    </div>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <label class="space-y-1.5 text-sm font-semibold text-[#111827]">
                            Brand
                            <x-ui.select wire:model.live="brand_id" placeholder="Select brand" clearable class="w-full" triggerClass="!rounded-lg !border-[#e5e7eb] !bg-white">
                                <x-ui.select.option value="">None</x-ui.select.option>
                                @foreach($brands as $brand)
                                    <x-ui.select.option value="{{ $brand->id }}">{{ $brand->name }}</x-ui.select.option>
                                @endforeach
                            </x-ui.select>
                        </label>
                        <label class="space-y-1.5 text-sm font-semibold text-[#111827]">
                            Primary Category <span class="text-[#ef4444]">*</span>
                            <x-ui.select wire:model.live="primary_category_id" placeholder="Select category" clearable class="w-full" triggerClass="!rounded-lg !border-[#e5e7eb] !bg-white">
                                <x-ui.select.option value="">None</x-ui.select.option>
                                @foreach($categories as $category)
                                    <x-ui.select.option value="{{ $category->id }}">{{ $category->name }}</x-ui.select.option>
                                @endforeach
                            </x-ui.select>
                        </label>
                    </div>
                    <div class="mt-4">
                        <label class="space-y-1.5 text-sm font-semibold text-[#111827]">Short Description</label>
                        <x-ui.textarea wire:model.live="short_description" rows="2" maxlength="160" resize="vertical" placeholder="Enter short description about the product..." class="!rounded-lg !border-[#e5e7eb]" />
                    </div>
                    <div class="mt-4">
                        <div class="mb-1.5 text-sm font-semibold text-[#111827]">Description</div>
                        <x-app.rich-text-editor :value="$description_json" />
                        <div class="mt-3"><x-admin.media-picker :assets="$mediaAssets" title="Insert content media" context="content" modal /></div>
                    </div>
                </section>

                <section class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="font-bold text-[#111827]">Product Images</h2>
                        <button type="button" x-data x-on:click="$dispatch('open-media-picker', { context: 'product-gallery' })" class="rounded-lg border border-[#2563eb] px-3 py-2 text-xs font-bold text-[#2563eb] hover:bg-[#eff6ff]">Select from Media Library</button>
                    </div>
                    <label class="mt-4 flex min-h-[200px] cursor-pointer flex-col items-center justify-center rounded-xl border border-dashed border-[#d1d5db] bg-[#f9fafb] text-center hover:border-[#2563eb] hover:bg-[#eff6ff]">
                        <svg class="size-10 text-[#2563eb]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/></svg>
                        <span class="mt-3 text-sm font-semibold text-[#374151]">Drag & drop images here<br>or click to browse</span>
                        <span class="mt-1 text-[11px] text-[#9ca3af]">Recommended size: 1200 x 1200px<br>Max file size: 5MB</span>
                        <input type="file" wire:model="image" accept="image/*" class="sr-only">
                    </label>
                    @if($image)
                        <p class="mt-3 rounded-lg bg-[#eff6ff] px-3 py-2 text-xs font-semibold text-[#2563eb]">New upload ready: {{ $image->getClientOriginalName() }}</p>
                    @endif
                    <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-4" wire:sort="sortMedia" aria-label="Product gallery. Drag images to reorder.">
                        @forelse($selectedMedia as $asset)
                            @php($mediaIndex = array_search($asset->id, $selectedMediaIds, true))
                            <div wire:key="product-media-{{ $asset->id }}" wire:sort:item="{{ $asset->id }}" class="group relative aspect-square overflow-hidden rounded-lg border {{ $mediaIndex === 0 ? 'border-2 border-[#2563eb]' : 'border-[#e5e7eb]' }} bg-[#f9fafb]">
                                <img src="{{ $asset->url() }}" alt="{{ $asset->alt_text ?: $asset->filename }}" class="size-full object-cover">
                                @if($mediaIndex === 0)<span class="absolute left-2 top-2 rounded bg-[#2563eb] px-1.5 py-0.5 text-[10px] font-bold text-white">Main</span>@endif
                                <div class="absolute inset-x-1 bottom-1 flex justify-center gap-1 opacity-0 transition group-hover:opacity-100"><button type="button" wire:click="moveMedia({{ $mediaIndex }}, -1)" wire:loading.attr="disabled" class="grid size-7 place-items-center rounded bg-white/95 text-xs font-bold text-[#374151] shadow">←</button><button type="button" wire:click="moveMedia({{ $mediaIndex }}, 1)" wire:loading.attr="disabled" class="grid size-7 place-items-center rounded bg-white/95 text-xs font-bold text-[#374151] shadow">→</button><button type="button" wire:click="removeMedia({{ $asset->id }})" wire:confirm="Remove this product image?" wire:loading.attr="disabled" class="grid size-7 place-items-center rounded bg-white/95 text-xs font-bold text-red-600 shadow">×</button></div>
                            </div>
                        @empty
                            <div class="col-span-full rounded-lg border border-dashed border-[#d1d5db] bg-[#f9fafb] px-4 py-6 text-center text-xs text-[#9ca3af]">No product images selected yet. Choose existing media or upload a new image.</div>
                        @endforelse
                    </div>
                    <x-admin.media-picker :assets="$mediaAssets" :selected="$selectedMediaIds[0] ?? null" title="Select product gallery media" context="product-gallery" modal />
                </section>

                <section class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                    <h2 class="mb-4 font-bold text-[#111827]">Pricing</h2>
                    <div class="grid gap-4 sm:grid-cols-4">
                        <label class="space-y-1.5 text-sm font-semibold text-[#111827]">
                            Regular Price <span class="text-[#ef4444]">*</span>
                            <x-ui.input type="number" min="0" wire:model.live="regular_price_minor" placeholder="0.00" class="!rounded-lg" controlClass="!rounded-lg !border-[#e5e7eb] !bg-white" />
                        </label>
                        <label class="space-y-1.5 text-sm font-semibold text-[#111827]">
                            Sale Price
                            <x-ui.input type="number" min="0" wire:model.live="sale_price_minor" placeholder="0.00" class="!rounded-lg" controlClass="!rounded-lg !border-[#e5e7eb] !bg-white" />
                        </label>
                        <label class="space-y-1.5 text-sm font-semibold text-[#111827]">
                            Compare At Price
                            <x-ui.input type="number" min="0" wire:model.live="compare_at_price_minor" placeholder="0.00" class="!rounded-lg" controlClass="!rounded-lg !border-[#e5e7eb] !bg-white" />
                        </label>
                        <label class="space-y-1.5 text-sm font-semibold text-[#111827]">
                            Cost Price
                            <x-ui.input type="number" min="0" wire:model.live="cost_price_minor" placeholder="0.00" class="!rounded-lg" controlClass="!rounded-lg !border-[#e5e7eb] !bg-white" />
                        </label>
                    </div>
                    <div class="mt-4 flex items-center gap-4">
                        <label class="space-y-1.5 text-sm font-semibold text-[#111827]">Tax Class</label>
                        <x-ui.select placeholder="Standard Rate" class="w-44" triggerClass="!rounded-lg !border-[#e5e7eb] !bg-white">
                            <x-ui.select.option value="standard">Standard Rate</x-ui.select.option>
                        </x-ui.select>
                        <x-ui.checkbox wire:model.live="track_quantity" label="Track stock quantity" size="sm" />
                    </div>
                </section>

                <section class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                    <h2 class="mb-4 font-bold text-[#111827]">Search Engine Optimization</h2>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <label class="space-y-1.5 text-sm font-semibold text-[#111827]">
                            Meta Title
                            <x-ui.input wire:model.live="meta_title" placeholder="Product name - StoreZ" class="!rounded-lg" controlClass="!rounded-lg !border-[#e5e7eb] !bg-white" />
                        </label>
                        <label class="space-y-1.5 text-sm font-semibold text-[#111827]">
                            Meta Description
                            <x-ui.input wire:model.live="meta_description" placeholder="Buy online at StoreZ" class="!rounded-lg" controlClass="!rounded-lg !border-[#e5e7eb] !bg-white" />
                        </label>
                        <label class="space-y-1.5 text-sm font-semibold text-[#111827]">
                            URL Key
                            <x-ui.input wire:model.live="slug" placeholder="product-slug" class="!rounded-lg" controlClass="!rounded-lg !border-[#e5e7eb] !bg-white" />
                        </label>
                    </div>
                </section>
            </div>

            <div class="space-y-5">
                <section class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                    <h2 class="font-bold text-[#111827]">Product Status</h2>
                    <div class="mt-4 space-y-4">
                        <label class="block space-y-1.5 text-sm font-semibold text-[#111827]">
                            Status
                            <x-ui.select wire:model.live="status" placeholder="Select status" class="w-full" triggerClass="!rounded-lg !border-[#e5e7eb] !bg-white">
                                <x-ui.select.option value="draft">Draft</x-ui.select.option>
                                <x-ui.select.option value="published">Published</x-ui.select.option>
                                <x-ui.select.option value="archived">Archived</x-ui.select.option>
                            </x-ui.select>
                        </label>
                        <label class="block space-y-1.5 text-sm font-semibold text-[#111827]">
                            Visibility
                            <x-ui.select wire:model.live="visibility" placeholder="Select visibility" class="w-full" triggerClass="!rounded-lg !border-[#e5e7eb] !bg-white">
                                <x-ui.select.option value="visible">Catalog & Search</x-ui.select.option>
                                <x-ui.select.option value="hidden">Hidden</x-ui.select.option>
                            </x-ui.select>
                        </label>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-[#374151]">Stock Management</span>
                                <span class="text-xs text-[#10b981] font-bold">Tracking stock quantity</span>
                            </div>
                            <label class="space-y-1.5 text-sm font-semibold text-[#111827]">
                                Quantity
                                <x-ui.input type="number" min="0" wire:model.live="inventory_quantity" placeholder="0" class="!rounded-lg" controlClass="!rounded-lg !border-[#e5e7eb] !bg-white" />
                            </label>
                            <label class="space-y-1.5 text-sm font-semibold text-[#111827]">
                                Low Stock Threshold
                                <x-ui.input type="number" min="0" wire:model.live="low_stock_threshold" placeholder="10" class="!rounded-lg" controlClass="!rounded-lg !border-[#e5e7eb] !bg-white" />
                            </label>
                            <x-ui.checkbox wire:model.live="allow_backorders" label="Allow backorders" size="sm" />
                        </div>
                        <x-ui.checkbox wire:model.live="is_featured" label="Featured product" size="sm" />
                    </div>
                </section>

                <section class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                    <h2 class="font-bold text-[#111827]">Tags</h2>
                    <p class="mt-1 text-xs text-[#9ca3af]">Add tags to help customers find this product.</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @forelse($tags->whereIn('id', $tag_ids) as $tag)
                            <button type="button" wire:click="$set('tag_ids', {{ json_encode(array_values(array_diff($tag_ids, [$tag->id]))) }})" class="flex items-center gap-1 rounded-full bg-[#eff6ff] px-3 py-1 text-xs font-bold text-[#2563eb]">{{ $tag->name }} ×</button>
                        @empty
                            <span class="text-xs text-[#9ca3af]">No tags selected.</span>
                        @endforelse
                    </div>
                    <x-ui.select wire:model.live="tag_ids" multiple searchable clearable placeholder="Select tags" class="mt-3 w-full" triggerClass="!rounded-lg !border-[#e5e7eb] !bg-[#f9fafb]">
                        @foreach($tags as $tag)
                            <x-ui.select.option wire:key="product-tag-{{ $tag->id }}" value="{{ $tag->id }}">{{ $tag->name }}</x-ui.select.option>
                        @endforeach
                    </x-ui.select>
                </section>

                <section class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
                    <h2 class="font-bold text-[#111827]">Product Attributes</h2>
                    <div class="mt-4 space-y-3">
                        @forelse($attributes as $attribute)
                            <div wire:key="product-attribute-{{ $attribute->id }}" class="block space-y-1.5 text-sm font-semibold text-[#111827]">
                                {{ $attribute->name }} @if($attribute->unit)<span class="text-xs font-normal text-[#9ca3af]">({{ $attribute->unit }})</span>@endif
                                @if(in_array($attribute->type, ['select', 'multi_select'], true))
                                    <x-ui.select wire:model.live="attribute_values.{{ $attribute->id }}" :multiple="$attribute->type === 'multi_select'" searchable clearable placeholder="Select {{ strtolower($attribute->name) }}" class="w-full" triggerClass="!rounded-lg !border-[#e5e7eb] !bg-white" :wire:key="'product-attribute-select-'.$attribute->id">
                                        @foreach($attribute->values as $value)
                                            <x-ui.select.option wire:key="product-attribute-value-{{ $value->id }}" value="{{ $value->id }}">{{ $value->value }}</x-ui.select.option>
                                        @endforeach
                                    </x-ui.select>
                                @elseif($attribute->type === 'number')
                                    <x-ui.input type="number" wire:model.live="attribute_values.{{ $attribute->id }}" class="!rounded-lg" controlClass="!rounded-lg !border-[#e5e7eb] !bg-white" />
                                @elseif($attribute->type === 'boolean')
                                    <x-ui.checkbox wire:model.live="attribute_values.{{ $attribute->id }}" label="Yes" size="sm" />
                                @else
                                    <x-ui.input wire:model.live="attribute_values.{{ $attribute->id }}" class="!rounded-lg" controlClass="!rounded-lg !border-[#e5e7eb] !bg-white" />
                                @endif
                            </div>
                        @empty
                            <p class="text-xs text-[#9ca3af]">Create reusable attributes before adding product specifications.</p>
                        @endforelse
                    </div>
                </section>
            </div>
        </form>
    </div>
</div>
