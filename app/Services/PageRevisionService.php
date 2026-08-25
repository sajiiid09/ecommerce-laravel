<?php

namespace App\Services;

use App\Models\Page;
use App\Models\PageRevision;
use Illuminate\Support\Facades\DB;

class PageRevisionService
{
    public function __construct(
        private readonly PageService $pages,
        private readonly ContentPublishingService $publishing,
        private readonly RichTextContentService $richText,
    ) {}

    public function restore(PageRevision $revision): Page
    {
        return DB::transaction(function () use ($revision): Page {
            $page = $revision->page;
            $previousSlug = $page->slug;
            $page->fill($revision->snapshot);
            $page->forceFill(['updated_by' => auth()->id(), 'status' => 'draft']);
            $page->save();
            $content = $this->richText->prepare($page->content_json, $page->content_html ?? '');
            $page->forceFill(['content_json' => $content['content_json'], 'content_html' => $content['content_html']])->save();
            $this->richText->syncUsages($page, $content['media_ids'], 'page.content');
            $this->pages->revision($page, 'draft');
            $this->publishing->invalidate('page', $page->slug, $previousSlug);

            return $page->fresh();
        });
    }
}
