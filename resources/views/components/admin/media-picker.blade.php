@props(['assets' => collect(), 'selected' => null, 'title' => 'Select media', 'context' => 'default', 'modal' => false])

<div x-data="{ open: @js(! $modal) }"
    @if($modal) x-on:open-media-picker.window="if ($event.detail?.context === @js($context)) open = true" @endif
    x-show="open" x-cloak {{ $attributes->merge(['class' => 'rounded-xl border border-slate-200 bg-white p-4']) }}>
    <div class="flex items-center justify-between gap-3">
        <h3 class="text-sm font-bold text-slate-900">{{ $title }}</h3>
        <div class="flex items-center gap-3">
            <span class="text-xs text-slate-500">Shared Media Library</span>
            @if($modal)
                <button type="button" x-on:click="open = false" class="text-xs font-bold text-slate-500">Close</button>
            @endif
        </div>
    </div>
    <div class="mt-3 grid grid-cols-3 gap-2 sm:grid-cols-5">
        @forelse($assets as $asset)
            <button type="button" wire:key="media-picker-{{ $context }}-{{ $asset->id }}" wire:click="$dispatch('media-selected', { id: {{ $asset->id }}, url: @js($asset->url()), context: @js($context) })" class="group overflow-hidden rounded-lg border {{ (int) $selected === (int) $asset->id ? 'border-blue-500 ring-2 ring-blue-100' : 'border-slate-200' }} bg-slate-50 text-left">
                <div class="aspect-square bg-slate-100">
                    @if(str_starts_with((string) $asset->mime_type, 'image/'))
                        <img src="{{ $asset->url() }}" alt="{{ $asset->alt_text ?: $asset->filename }}" class="size-full object-cover">
                    @endif
                </div>
                <span class="block truncate px-2 py-1.5 text-[10px] font-semibold text-slate-700">{{ $asset->filename }}</span>
            </button>
        @empty
            <p class="col-span-full py-6 text-center text-xs text-slate-500">No media assets uploaded yet.</p>
        @endforelse
    </div>
</div>
