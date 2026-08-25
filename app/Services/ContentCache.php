<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class ContentCache
{
    public function page(string $slug): string
    {
        return 'cms:page:'.trim($slug, '/');
    }

    public function banners(string $placement): string
    {
        return 'cms:banners:'.$placement;
    }

    public function menu(string $key): string
    {
        return 'cms:menu:'.$key;
    }

    public function announcements(string $placement): string
    {
        return 'cms:announcements:'.$placement;
    }

    public function settings(string $group, string $key): string
    {
        return 'cms:settings:'.$group.':'.$key;
    }

    public function redirect(string $path): string
    {
        return 'cms:redirect:'.trim($path, '/');
    }

    public function forget(array|string $keys): void
    {
        foreach ((array) $keys as $key) {
            Cache::forget($key);
        }
    }
}
