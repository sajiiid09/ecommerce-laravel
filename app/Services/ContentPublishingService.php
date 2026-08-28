<?php

namespace App\Services;

class ContentPublishingService
{
    public function __construct(private readonly ContentCache $cache) {}

    public function invalidate(string $area, ?string $key = null, ?string $previousKey = null): void
    {
        $keys = match ($area) {
            'page' => array_filter([
                $key ? $this->cache->page($key) : null,
                $previousKey ? $this->cache->page($previousKey) : null,
            ]),
            'homepage' => ['cms:homepage'],
            'banner' => $key ? [$this->cache->banners($key)] : [],
            'menu' => $key ? [$this->cache->menu($key), $this->cache->navigation($key)] : [],
            'settings' => $key && str_contains($key, ':') ? [$this->cache->settings(...explode(':', $key, 2))] : [],
            'redirect' => $key ? [$this->cache->redirect($key)] : [],
            'announcement' => $key ? [$this->cache->announcements($key)] : [],
            default => [],
        };

        $this->cache->forget($keys);
    }
}
