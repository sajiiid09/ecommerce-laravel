<div class="p-5 sm:p-8">
    <div class="mx-auto max-w-[1480px]">
        <div class="mb-6 flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
            <div>
                <p class="text-sm text-slate-500">StoreZ / Content / Media Library</p>
                <h1 class="mt-1 text-[28px] font-extrabold tracking-tight text-slate-900">Media Library</h1>
                <p class="mt-1 text-sm text-slate-500">Upload, organize and reuse media across the storefront.</p>
            </div>
            <label class="inline-flex cursor-pointer items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-blue-700">
                Upload files
                <input type="file" wire:model="file" accept="image/*" class="sr-only">
            </label>
        </div>

        @if(session('status')) <div class="mb-4 rounded-lg bg-emerald-50 p-3 text-sm font-semibold text-emerald-700">{{ session('status') }}</div> @endif
        @error('file') <div class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ $message }}</div> @enderror

        <div class="mb-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($stats as $label => $value)
                <x-admin.cms.stat-card wire:key="media-stat-{{ $label }}" :label="$label" :value="is_numeric($value) ? number_format($value) : $value" :accent="$loop->iteration === 2 ? 'green' : ($loop->iteration === 3 ? 'orange' : 'blue')"><x-ui.icon name="photo" class="size-5" /></x-admin.cms.stat-card>
            @endforeach
        </div>

        <div class="grid gap-5 xl:grid-cols-[220px_minmax(0,1fr)_280px]">
            <aside class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <h2 class="mb-3 text-sm font-bold text-slate-900">Folders</h2>
                <div class="mb-3 flex gap-1"><input wire:model="folderName" placeholder="New folder" class="min-w-0 flex-1 rounded border border-slate-200 px-2 py-1 text-xs"><button wire:click="createFolder" class="rounded bg-slate-900 px-2 text-xs font-bold text-white">+</button></div>
                <button wire:click="$set('folderId', null)" class="w-full rounded-lg px-3 py-2 text-left text-sm {{ !$folderId ? 'bg-blue-50 font-bold text-blue-700' : 'text-slate-600' }}">All Media</button>
                @foreach($folders as $folder)
                    <button wire:key="media-folder-{{ $folder->id }}" wire:click="$set('folderId', {{ $folder->id }})" class="mt-1 flex w-full justify-between rounded-lg px-3 py-2 text-left text-sm {{ (int) $folderId === (int) $folder->id ? 'bg-blue-50 font-bold text-blue-700' : 'text-slate-600' }}"><span>{{ $folder->name }}</span><span class="text-xs text-slate-400">{{ $folder->assets_count }}</span></button>
                @endforeach
            </aside>

            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex flex-col gap-3 md:flex-row">
                    <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search filename, title or alt text" class="min-w-0 flex-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none">
                    <select wire:model.live="type" class="rounded-lg border border-slate-200 px-3 py-2 text-sm"><option value="">All types</option><option value="image">Images</option><option value="video">Video</option></select>
                    <button wire:click="$set('search', '')" wire:loading.attr="disabled" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-600 disabled:opacity-60">Reset</button>
                </div>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    @forelse($assets as $asset)
                        <button wire:key="media-asset-{{ $asset->id }}" wire:click="$set('selectedAssetId', {{ $asset->id }})" class="overflow-hidden rounded-lg border {{ $selectedAssetId === $asset->id ? 'border-blue-500 ring-2 ring-blue-100' : 'border-slate-200' }} text-left">
                            <div class="aspect-square bg-slate-100">@if(str_starts_with((string) $asset->mime_type, 'image/'))<img src="{{ $asset->url() }}" alt="{{ $asset->alt_text ?: $asset->filename }}" class="size-full object-cover">@endif</div>
                            <div class="p-2"><p class="truncate text-xs font-bold text-slate-800">{{ $asset->filename }}</p><p class="text-[10px] text-slate-500">{{ number_format(($asset->size ?? 0) / 1024, 1) }} KB</p></div>
                        </button>
                    @empty
                        <p class="col-span-full py-12 text-center text-sm text-slate-500">No media assets match your filters.</p>
                    @endforelse
                </div>
                <div class="mt-5">{{ $assets->links() }}</div>
            </section>

            <aside class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-bold text-slate-900">Asset Details</h2>
                @if($selectedAsset)
                    <div class="mt-4 aspect-video overflow-hidden rounded-lg bg-slate-100">@if(str_starts_with((string) $selectedAsset->mime_type, 'image/'))<img src="{{ $selectedAsset->url() }}" alt="" class="size-full object-contain">@endif</div>
                    <div class="mt-4 space-y-3 text-xs"><div><span class="text-slate-500">Filename</span><p class="font-semibold text-slate-900">{{ $selectedAsset->filename }}</p></div><div><span class="text-slate-500">Type</span><p class="font-semibold text-slate-900">{{ $selectedAsset->mime_type }}</p></div><div><span class="text-slate-500">Usages</span><p class="font-semibold text-slate-900">{{ $selectedAsset->usages_count }}</p></div><input wire:model="title" placeholder="Title" class="w-full rounded border border-slate-200 px-2 py-1.5"><input wire:model="alt_text" placeholder="Alt text" class="w-full rounded border border-slate-200 px-2 py-1.5"><textarea wire:model="caption" placeholder="Caption" rows="2" class="w-full rounded border border-slate-200 px-2 py-1.5"></textarea><button wire:click="saveMetadata" wire:loading.attr="disabled" class="w-full rounded-lg bg-blue-600 px-3 py-2 text-xs font-bold text-white disabled:opacity-60">Save metadata</button></div>
                    <div class="mt-4 flex gap-2"><button wire:click="deleteAsset({{ $selectedAsset->id }})" wire:confirm="Delete this media asset?" wire:loading.attr="disabled" class="flex-1 rounded-lg border border-red-200 px-3 py-2 text-xs font-bold text-red-600 disabled:opacity-60">Delete</button><button wire:click="$set('selectedAssetId', null)" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-slate-600">Close</button></div>
                @else
                    <p class="mt-4 text-sm text-slate-500">Select an asset to view its details and usages.</p>
                @endif
            </aside>
        </div>
    </div>
</div>
