<?php

namespace App\Services;

use App\Models\Page;
use Illuminate\Support\Facades\Cache;

class ContentResolver
{
    public function __construct(
        private readonly RedirectService $redirects,
        private readonly ContentCache $cache,
    ) {}

    public function page(string $slug): ?Page
    {
        $normalizedSlug = trim($slug, '/');
        $cacheKey = $this->cache->page($normalizedSlug);

        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);

            if ($cached instanceof Page || $cached === null) {
                return $cached;
            }

            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, 300, fn (): ?Page => Page::published()
            ->where('slug', $normalizedSlug)
            ->with('featuredMedia')
            ->first());
    }

    public function resolve(string $path): array
    {
        $slug = trim($path, '/');

        if ($page = $this->page($slug)) {
            return ['type' => 'page', 'page' => $page];
        }

        if ($redirect = $this->redirects->destination($path)) {
            return ['type' => 'redirect'] + $redirect;
        }

        return ['type' => 'missing'];
    }
}
