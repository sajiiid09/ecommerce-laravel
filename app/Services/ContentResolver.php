<?php

namespace App\Services;

use App\Models\Page;

class ContentResolver
{
    public function __construct(private readonly RedirectService $redirects) {}

    public function page(string $slug): ?Page
    {
        return Page::published()->where('slug', trim($slug, '/'))->with('featuredMedia')->first();
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
