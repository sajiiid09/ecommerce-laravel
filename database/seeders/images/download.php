<?php

/**
 * Download StoreZ demo seed images into database/seeders/images.
 *
 * Usage:
 *   php database/seeders/images/download.php
 *   php database/seeders/images/download.php --force
 *
 * This script is deliberately separate from Laravel database seeding:
 * seeders remain deterministic/offline after this one-time download.
 */
$baseDir = __DIR__;
$manifestPath = $baseDir.'/sources.json';
$force = in_array('--force', $argv, true);

if (! is_file($manifestPath)) {
    fwrite(STDERR, "Missing sources.json\n");
    exit(1);
}

$manifest = json_decode(file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);

$items = [];
foreach (['products', 'variants', 'brands', 'categories'] as $group) {
    foreach ($manifest[$group] ?? [] as $item) {
        $item['group'] = $group;
        $items[] = $item;
    }
}

$ok = 0;
$skipped = 0;
$failed = [];

foreach ($items as $item) {
    $relativePath = $item['path'];
    $destination = $baseDir.'/'.$relativePath;

    if (is_file($destination) && ! $force) {
        echo "[skip] {$relativePath}\n";
        $skipped++;

        continue;
    }

    if (! is_dir(dirname($destination))) {
        mkdir(dirname($destination), 0775, true);
    }

    echo "[get ] {$relativePath}\n";

    $binary = null;
    $usedUrl = null;

    if (! empty($item['image_url'])) {
        $binary = httpGet($item['image_url'], $item['source_url'] ?? null);
        $usedUrl = $item['image_url'];
    }

    // If the direct asset URL fails, try the source page's og:image.
    if ($binary === null && ! empty($item['source_url'])) {
        $page = httpGet($item['source_url']);

        if ($page !== null) {
            $ogImage = extractOgImage($page, $item['source_url']);

            if ($ogImage) {
                $binary = httpGet($ogImage, $item['source_url']);
                $usedUrl = $ogImage;
            }
        }
    }

    if ($binary === null) {
        $failed[] = $relativePath;
        echo "[fail] {$relativePath}\n";

        continue;
    }

    file_put_contents($destination, $binary);

    if (! isLikelyImage($destination)) {
        @unlink($destination);
        $failed[] = $relativePath;
        echo "[fail] {$relativePath} (download was not a valid raster image)\n";

        continue;
    }

    echo "[ ok ] {$relativePath}".($usedUrl ? " <- {$usedUrl}" : '')."\n";
    $ok++;
}

echo "\nDownloaded: {$ok}\n";
echo "Skipped:    {$skipped}\n";
echo 'Failed:     '.count($failed)."\n";

if ($failed) {
    echo "\nFailed files:\n";
    foreach ($failed as $path) {
        echo " - {$path}\n";
    }

    echo "\nSome retailers block automated/hot-linked downloads. "
        .'For any failed item, open its source_url in sources.json, save the product image '
        ."using the exact local filename shown above, then rerun the seeder.\n";

    exit(2);
}

function httpGet(string $url, ?string $referer = null): ?string
{
    $headers = [
        'Accept: image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
        'Accept-Language: en-US,en;q=0.8',
        'Cache-Control: no-cache',
    ];

    if (function_exists('curl_init')) {
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 8,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/150 Safari/537.36',
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_ENCODING => '',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        if ($referer) {
            curl_setopt($ch, CURLOPT_REFERER, $referer);
        }

        $data = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $data !== false && $status >= 200 && $status < 400 ? $data : null;
    }

    $contextHeaders = implode("\r\n", array_merge($headers, [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/150 Safari/537.36',
        $referer ? "Referer: {$referer}" : '',
    ]));

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => $contextHeaders,
            'timeout' => 45,
            'follow_location' => 1,
            'max_redirects' => 8,
        ],
    ]);

    $data = @file_get_contents($url, false, $context);

    return $data === false ? null : $data;
}

function extractOgImage(string $html, string $sourceUrl): ?string
{
    if (
        preg_match(
            '/<meta[^>]+(?:property|name)=["\']og:image(?::secure_url)?["\'][^>]+content=["\']([^"\']+)["\']/i',
            $html,
            $match
        )
        || preg_match(
            '/<meta[^>]+content=["\']([^"\']+)["\'][^>]+(?:property|name)=["\']og:image(?::secure_url)?["\']/i',
            $html,
            $match
        )
    ) {
        return absoluteUrl(html_entity_decode($match[1], ENT_QUOTES), $sourceUrl);
    }

    return null;
}

function absoluteUrl(string $url, string $baseUrl): string
{
    if (preg_match('#^https?://#i', $url)) {
        return $url;
    }

    $parts = parse_url($baseUrl);
    $scheme = $parts['scheme'] ?? 'https';
    $host = $parts['host'] ?? '';

    if (str_starts_with($url, '//')) {
        return $scheme.':'.$url;
    }

    if (str_starts_with($url, '/')) {
        return "{$scheme}://{$host}{$url}";
    }

    $basePath = isset($parts['path']) ? dirname($parts['path']) : '';

    return "{$scheme}://{$host}".rtrim($basePath, '/').'/'.$url;
}

function isLikelyImage(string $path): bool
{
    return @getimagesize($path) !== false;
}
