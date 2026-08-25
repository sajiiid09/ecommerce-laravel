<?php

namespace App\Services;

use App\Models\MediaAsset;
use App\Models\Page;
use App\Models\PageRevision;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PageService
{
    public function __construct(
        private readonly RichTextContentService $richText,
        private readonly MediaService $media,
        private readonly ContentPublishingService $publishing,
    ) {}

    public function save(array $data, ?Page $page = null): Page
    {
        return DB::transaction(function () use ($data, $page): Page {
            $page ??= new Page;
            $previousSlug = $page->exists ? $page->slug : null;
            $previousFeaturedMediaId = $page->exists ? $page->featured_media_id : null;
            $slug = Str::slug((string) ($data['slug'] ?? $data['title']));

            if (in_array($slug, ['admin', 'category', 'search', 'product', 'offers', 'brands', 'cart', 'checkout', 'login', 'register', 'account', 'orders', 'wishlist', 'logout'], true)) {
                throw new InvalidArgumentException('This slug is reserved by the application.');
            }

            if (Page::where('slug', $slug)->when($page->id, fn ($query) => $query->where('id', '<>', $page->id))->exists()) {
                throw new InvalidArgumentException('This page slug is already in use.');
            }

            $content = $this->richText->prepare($data['content_json'] ?? null, (string) ($data['content_html'] ?? ''));
            $data['slug'] = $slug;
            $data['content_json'] = $content['content_json'];
            $data['content_html'] = $content['content_html'];

            if (($data['status'] ?? 'draft') === 'scheduled' && empty($data['scheduled_at'])) {
                throw new InvalidArgumentException('Scheduled pages require a scheduled date.');
            }

            if (($data['status'] ?? 'draft') === 'published') {
                $data['published_at'] ??= $page->published_at ?? now();
            }

            $page->fill($data);
            $page->forceFill([
                'author_id' => $page->author_id ?? auth()->id(),
                'updated_by' => auth()->id(),
            ]);
            $page->save();

            $this->revision($page, $data['status'] ?? 'draft');
            $this->richText->syncUsages($page, $content['media_ids'], 'page.content');

            if ($page->featured_media_id) {
                $asset = $page->featuredMedia()->firstOrFail();
                Gate::authorize('view', $asset);
                $this->media->attach($asset, $page, 'page.featured_media');
            }

            if ($previousFeaturedMediaId && $previousFeaturedMediaId !== $page->featured_media_id) {
                $previousAsset = MediaAsset::find($previousFeaturedMediaId);
                if ($previousAsset) {
                    $this->media->detach($previousAsset, $page, 'page.featured_media');
                }
            }

            $this->publishing->invalidate('page', $page->slug, $previousSlug);

            return $page->fresh();
        });
    }

    public function revision(Page $page, string $status = 'draft'): PageRevision
    {
        $number = ((int) $page->revisions()->max('revision_number')) + 1;

        $revision = $page->revisions()->create([
            'revision_number' => $number,
            'snapshot' => $page->only([
                'title', 'slug', 'page_type', 'template', 'excerpt', 'content_json', 'content_html',
                'status', 'visibility', 'meta_title', 'meta_description', 'canonical_url', 'featured_media_id',
            ]),
            'status' => $status,
            'published_at' => $status === 'published' ? now() : null,
        ]);

        $revision->forceFill(['created_by' => auth()->id()])->save();

        return $revision;
    }
}
