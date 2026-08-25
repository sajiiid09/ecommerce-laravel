<?php

use App\Models\Announcement;
use App\Models\Banner;
use App\Models\Page;
use App\Models\RedirectHit;
use App\Services\ContentPublishingService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('content:sync-scheduled', function (ContentPublishingService $publishing): void {
    $now = now();
    $pages = Page::query()->where('status', 'scheduled')->whereNotNull('scheduled_at')->where('scheduled_at', '<=', $now)->lazyById(100);
    $banners = Banner::query()->where('status', 'scheduled')->whereNotNull('starts_at')->where('starts_at', '<=', $now)->lazyById(100);
    $announcements = Announcement::query()->where('status', 'scheduled')->whereNotNull('starts_at')->where('starts_at', '<=', $now)->lazyById(100);

    foreach ($pages as $page) {
        $page->update(['status' => 'published', 'published_at' => $now]);
        $publishing->invalidate('page', $page->slug);
    }

    foreach ($banners as $banner) {
        $banner->update(['status' => $banner->ends_at && $banner->ends_at->lte($now) ? 'archived' : 'published']);
        $publishing->invalidate('banner', $banner->placement);
    }

    foreach ($announcements as $announcement) {
        $announcement->update(['status' => $announcement->ends_at && $announcement->ends_at->lte($now) ? 'archived' : 'published']);
        $publishing->invalidate('announcement', $announcement->placement);
    }

    RedirectHit::where('created_at', '<', $now->copy()->subDays(90))->delete();
    $this->info('Scheduled content synchronized.');
})->purpose('Publish scheduled CMS content and prune redirect history');
