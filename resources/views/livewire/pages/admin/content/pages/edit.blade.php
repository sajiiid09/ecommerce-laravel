<div class="p-5 sm:p-8">
    <div class="mx-auto max-w-[1200px]">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-sm text-slate-500">StoreZ / Content / Pages / {{ $pageId ? 'Edit' : 'Create' }}</p>
                <h1 class="mt-1 text-3xl font-extrabold text-slate-900">{{ $pageId ? 'Edit Page' : 'Create Page' }}</h1>
            </div>
            <div class="flex gap-2">
                @if($pageId)
                    <a href="{{ url('/admin/content/pages/'.$pageId.'/preview') }}" target="_blank" class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700">Preview</a>
                @endif
                <button wire:click="savePage" wire:loading.attr="disabled" wire:target="savePage" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-bold text-white disabled:opacity-60">
                    <span wire:loading.remove wire:target="savePage">Save Page</span><span wire:loading wire:target="savePage">Saving…</span>
                </button>
            </div>
        </div>

        @if(session('status'))<div class="mb-4 rounded-lg bg-emerald-50 p-3 text-sm font-semibold text-emerald-700">{{ session('status') }}</div>@endif
        @if($errors->any())<div class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif

        <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="space-y-5">
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="font-bold text-slate-900">Basic Information</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <label class="text-sm font-semibold">Title<input wire:model.live="title" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2"></label>
                        <label class="text-sm font-semibold">Slug<input wire:model.live="slug" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2"></label>
                        <label class="text-sm font-semibold">Page type<select wire:model.live="page_type" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2"><option value="standard">Standard</option><option value="landing">Landing</option><option value="legal">Legal</option></select></label>
                        <label class="text-sm font-semibold">Template<input wire:model.live="template" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2"></label>
                    </div>
                    <label class="mt-4 block text-sm font-semibold">Excerpt<textarea wire:model.live="excerpt" rows="3" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2"></textarea></label>
                </section>

                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="mb-3 font-bold text-slate-900">Page Content</h2>
                    <x-app.rich-text-editor :value="$content_json" json-field="content_json" html-field="content_html" />
                    <div class="mt-4"><x-admin.media-picker :assets="$mediaAssets" title="Insert content media" context="content" modal /></div>
                </section>

                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="font-bold text-slate-900">SEO</h2>
                    <div class="mt-4 grid gap-4">
                        <input wire:model.live="meta_title" placeholder="Meta title" class="rounded-lg border border-slate-200 px-3 py-2">
                        <textarea wire:model.live="meta_description" placeholder="Meta description" rows="3" class="rounded-lg border border-slate-200 px-3 py-2"></textarea>
                        <input wire:model.live="canonical_url" placeholder="Canonical URL" class="rounded-lg border border-slate-200 px-3 py-2">
                    </div>
                </section>
            </div>

            <aside class="space-y-5">
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="font-bold text-slate-900">Publish</h2>
                    <select wire:model.live="status" class="mt-4 w-full rounded-lg border border-slate-200 px-3 py-2"><option value="draft">Draft</option><option value="scheduled">Scheduled</option><option value="published">Published</option><option value="archived">Archived</option></select>
                    @if($status === 'scheduled')<input type="datetime-local" wire:model.live="scheduled_at" class="mt-3 w-full rounded-lg border border-slate-200 px-3 py-2">@endif
                    <select wire:model.live="visibility" class="mt-3 w-full rounded-lg border border-slate-200 px-3 py-2"><option value="public">Public</option><option value="private">Private</option></select>
                    <label class="mt-4 flex items-center gap-2 text-sm"><input type="checkbox" wire:model.live="show_in_navigation"> Show in navigation</label>
                    <label class="mt-3 flex items-center gap-2 text-sm"><input type="checkbox" wire:model.live="is_indexable"> Allow indexing</label>
                </section>

                <x-admin.media-picker :assets="$mediaAssets" :selected="$featured_media_id" title="Featured media" context="featured" />

                @if($pageId)
                    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="font-bold text-slate-900">Revision History</h2>
                        <div class="mt-3 space-y-2">
                            @foreach($revisions as $revision)
                                <div wire:key="page-revision-{{ $revision->id }}" class="flex items-center justify-between text-xs">
                                    <span>Revision {{ $revision->revision_number }} · {{ $revision->created_at?->format('M d, Y H:i') }}</span>
                                    <button wire:click="restoreRevision({{ $revision->id }})" wire:confirm="Restore this revision as a draft?" wire:loading.attr="disabled" class="font-bold text-blue-600 disabled:opacity-60">Restore</button>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            </aside>
        </div>
    </div>
</div>
