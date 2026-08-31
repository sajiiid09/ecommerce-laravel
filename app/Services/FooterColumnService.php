<?php

namespace App\Services;

class FooterColumnService
{
    /**
     * @return array<int, array{key: string, title: string, enabled: bool, links: array<int, array{name: string, url: string}>}>
     */
    public function defaults(): array
    {
        return [
            ['key' => 'shop', 'title' => 'Shop', 'enabled' => true, 'links' => [
                ['name' => 'All Categories', 'url' => '/category'],
                ['name' => 'Offers', 'url' => '/offers'],
                ['name' => 'Search Products', 'url' => '/search'],
            ]],
            ['key' => 'customer-service', 'title' => 'Customer Service', 'enabled' => true, 'links' => [
                ['name' => 'My Account', 'url' => '/account'],
                ['name' => 'Orders', 'url' => '/orders'],
                ['name' => 'Wishlist', 'url' => '/wishlist'],
            ]],
            ['key' => 'company', 'title' => 'Company', 'enabled' => true, 'links' => [
                ['name' => 'Featured Brands', 'url' => '/brands/sony'],
                ['name' => 'Electronics', 'url' => '/category/electronics'],
                ['name' => 'Contact StoreZ', 'url' => '/offers'],
            ]],
            ['key' => 'legal', 'title' => 'Legal', 'enabled' => true, 'links' => [
                ['name' => 'Sign In', 'url' => '/login'],
                ['name' => 'Create Account', 'url' => '/register'],
                ['name' => 'StoreZ Home', 'url' => '/'],
            ]],
        ];
    }

    /**
     * @return array<int, array{key: string, title: string, enabled: bool, links: array<int, array{name: string, url: string}>}>
     */
    public function normalize(mixed $columns): array
    {
        $defaults = $this->defaults();

        if (! is_array($columns)) {
            return $defaults;
        }

        $byKey = [];
        foreach ($columns as $column) {
            if (is_array($column) && is_string($column['key'] ?? null)) {
                $byKey[$column['key']] = $column;
            }
        }

        return array_map(function (array $default) use ($byKey): array {
            $column = $byKey[$default['key']] ?? null;

            if (! is_array($column)) {
                return $default;
            }

            $title = is_scalar($column['title'] ?? null) ? trim((string) $column['title']) : '';

            return [
                'key' => $default['key'],
                'title' => $title !== '' && mb_strlen($title) <= 80 ? $title : $default['title'],
                'enabled' => is_bool($column['enabled'] ?? null) ? $column['enabled'] : $default['enabled'],
                'links' => $this->normalizeLinks($column['links'] ?? null, $default['links']),
            ];
        }, $defaults);
    }

    /**
     * @return array<int, array{key: string, title: string, enabled: bool, links: array<int, array{name: string, url: string}>}>
     */
    public function forStorefront(mixed $columns): array
    {
        return array_map(function (array $column): array {
            $column['links'] = array_map(fn (array $link): array => [
                'name' => $link['name'],
                'url' => $this->toAbsoluteUrl($link['url']),
            ], $column['links']);

            return $column;
        }, $this->normalize($columns));
    }

    public function isAllowedUrl(mixed $url): bool
    {
        if (! is_string($url)) {
            return false;
        }

        $url = trim($url);

        if ($url === '' || str_starts_with($url, '//')) {
            return false;
        }

        if (str_starts_with($url, '/')) {
            return true;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        return in_array(strtolower((string) $scheme), ['http', 'https'], true)
            && filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * @param  array<int, array{name: string, url: string}>  $fallback
     * @return array<int, array{name: string, url: string}>
     */
    private function normalizeLinks(mixed $links, array $fallback): array
    {
        if (! is_array($links)) {
            return $fallback;
        }

        $normalized = [];
        foreach ($links as $link) {
            if (! is_array($link)) {
                continue;
            }

            $name = is_scalar($link['name'] ?? null) ? trim((string) $link['name']) : '';
            $url = is_scalar($link['url'] ?? null) ? trim((string) $link['url']) : '';

            if ($name === '' || mb_strlen($name) > 80 || mb_strlen($url) > 2048 || ! $this->isAllowedUrl($url)) {
                continue;
            }

            $normalized[] = ['name' => $name, 'url' => $url];
        }

        return $normalized !== [] ? $normalized : $fallback;
    }

    private function toAbsoluteUrl(string $url): string
    {
        return str_starts_with($url, '/') ? url($url) : $url;
    }
}
