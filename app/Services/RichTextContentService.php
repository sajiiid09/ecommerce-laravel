<?php

namespace App\Services;

use App\Models\MediaAsset;
use App\Models\MediaUsage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;

class RichTextContentService
{
    public function __construct(private readonly MediaService $media) {}

    private const ALLOWED_TAGS = '<p><br><strong><em><s><h1><h2><h3><ul><ol><li><blockquote><a><img>';

    public function prepare(array|string|null $contentJson, string $contentHtml): array
    {
        $mediaIds = [];
        $mediaUrls = [];
        $normalizedJson = is_array($contentJson)
            ? $this->normalizeNode($contentJson, $mediaIds, $mediaUrls)
            : $contentJson;

        return [
            'content_json' => $normalizedJson,
            'content_html' => $this->sanitizeHtml($contentHtml, $mediaUrls),
            'media_ids' => array_values(array_unique($mediaIds)),
        ];
    }

    public function syncUsages(Model $owner, array $mediaIds, string $role): void
    {
        $usageQuery = $owner->morphMany(MediaUsage::class, 'usable');
        $usageQuery->where('role', $role)->delete();

        foreach ($mediaIds as $mediaId) {
            $asset = MediaAsset::find($mediaId);

            if (! $asset) {
                continue;
            }

            Gate::authorize('view', $asset);
            $this->media->attach($asset, $owner, $role);
        }
    }

    private function normalizeNode(array $node, array &$mediaIds, array &$mediaUrls): ?array
    {
        if (($node['type'] ?? null) === 'image') {
            $mediaId = (int) Arr::get($node, 'attrs.mediaId', 0);
            $asset = $mediaId > 0 ? MediaAsset::find($mediaId) : null;

            if (! $asset) {
                return null;
            }

            Gate::authorize('view', $asset);
            $mediaIds[] = $asset->id;
            $mediaUrls[] = $asset->url();
            $node['attrs'] = [
                'src' => $asset->url(),
                'alt' => (string) Arr::get($node, 'attrs.alt', $asset->alt_text ?? ''),
                'title' => Arr::get($node, 'attrs.title'),
                'mediaId' => $asset->id,
            ];

            return $node;
        }

        if (isset($node['content']) && is_array($node['content'])) {
            $node['content'] = array_values(array_filter(array_map(
                function (array $child) use (&$mediaIds, &$mediaUrls): ?array {
                    return $this->normalizeNode($child, $mediaIds, $mediaUrls);
                },
                $node['content'],
            )));
        }

        return $node;
    }

    private function sanitizeHtml(string $html, array $mediaUrls = []): string
    {
        $html = strip_tags($html, self::ALLOWED_TAGS);
        $document = new \DOMDocument;
        $document->loadHTML(
            '<div>'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING
        );

        foreach (iterator_to_array($document->getElementsByTagName('*')) as $element) {
            $allowedAttributes = $element->tagName === 'a'
                ? ['href', 'title']
                : ($element->tagName === 'img' ? ['src', 'alt', 'title'] : []);

            foreach (iterator_to_array($element->attributes) as $attribute) {
                if (! in_array($attribute->name, $allowedAttributes, true)) {
                    $element->removeAttribute($attribute->name);
                }
            }

            foreach (['href', 'src'] as $attributeName) {
                if (! $element->hasAttribute($attributeName)) {
                    continue;
                }

                $value = trim($element->getAttribute($attributeName));
                $isImage = $element->tagName === 'img';

                if (! $this->isSafeUrl($value, $isImage, $mediaUrls)) {
                    if ($isImage) {
                        $element->parentNode?->removeChild($element);
                    } else {
                        $element->setAttribute($attributeName, '#');
                    }
                }
            }
        }

        $wrapper = $document->documentElement;
        $result = '';

        foreach ($wrapper?->childNodes ?? [] as $child) {
            $result .= $document->saveHTML($child);
        }

        return $result;
    }

    private function isSafeUrl(string $value, bool $image, array $mediaUrls = []): bool
    {
        if ($value === '' || preg_match('/^(javascript|data|vbscript|file):/i', $value)) {
            return false;
        }

        if ($image) {
            return in_array($value, $mediaUrls, true);
        }

        if (str_starts_with($value, '/')) {
            return true;
        }

        return filter_var($value, FILTER_VALIDATE_URL)
            && in_array(strtolower((string) parse_url($value, PHP_URL_SCHEME)), ['http', 'https', 'mailto'], true);
    }
}
