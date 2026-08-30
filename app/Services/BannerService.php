<?php

namespace App\Services;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\MediaAsset;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

class BannerService
{
    public function __construct(
        private readonly MediaService $media,
        private readonly ContentPublishingService $publishing,
        private readonly ContentCache $cache,
    ) {}

    public function save(array $data, ?Banner $banner = null): Banner
    {
        return DB::transaction(function () use ($data, $banner): Banner {
            $banner ??= new Banner;
            $previousPlacement = $banner->placement;
            $previousMediaIds = [
                'desktop_media_id' => $banner->desktop_media_id,
                'mobile_media_id' => $banner->mobile_media_id,
                'side_media_id' => $banner->side_media_id,
            ];

            if (! empty($data['starts_at']) && ! empty($data['ends_at']) && $data['ends_at'] < $data['starts_at']) {
                throw new InvalidArgumentException('Banner end time must be after its start time.');
            }

            if (($data['status'] ?? $banner->status) === 'scheduled' && empty($data['starts_at'])) {
                throw new InvalidArgumentException('Scheduled banners require a start time.');
            }

            if (($data['destination_type'] ?? 'url') === 'url' && isset($data['destination_value']) && preg_match('/^(javascript|data|vbscript|file):/i', $data['destination_value'])) {
                throw new InvalidArgumentException('Unsafe banner destination.');
            }

            if (($data['placement'] ?? $banner->placement) !== 'hero') {
                $data['side_media_id'] = null;
                $data['side_image_mode'] = 'cutout';
            }

            $banner->fill($data);
            $banner->forceFill([
                'created_by' => $banner->created_by ?? auth()->id(),
                'updated_by' => auth()->id(),
            ]);
            $banner->save();

            foreach ([
                ['desktop_media_id', 'desktopMedia', 'banner.desktop'],
                ['mobile_media_id', 'mobileMedia', 'banner.mobile'],
                ['side_media_id', 'sideMedia', 'banner.side'],
            ] as [$mediaId, $relation, $role]) {
                if (! $banner->{$mediaId}) {
                    continue;
                }

                $asset = $banner->{$relation}()->firstOrFail();
                Gate::authorize('view', $asset);
                $this->media->attach($asset, $banner, $role);
            }

            foreach ($previousMediaIds as $mediaField => $previousMediaId) {
                if ($previousMediaId && (int) $previousMediaId !== (int) $banner->{$mediaField}) {
                    $asset = MediaAsset::find($previousMediaId);
                    if ($asset) {
                        $role = match ($mediaField) {
                            'desktop_media_id' => 'banner.desktop',
                            'mobile_media_id' => 'banner.mobile',
                            default => 'banner.side',
                        };
                        $this->media->detach($asset, $banner, $role);
                    }
                }
            }

            $this->publishing->invalidate('banner', $banner->placement);

            if ($previousPlacement && $previousPlacement !== $banner->placement) {
                $this->publishing->invalidate('banner', $previousPlacement);
            }

            if ($banner->placement === 'hero' || $previousPlacement === 'hero') {
                $this->publishing->invalidate('homepage');
            }

            return $banner->fresh(['desktopMedia', 'mobileMedia', 'sideMedia']);
        });
    }

    public function active(string $placement): Collection
    {
        $cacheKey = $this->cache->banners($placement);
        $cached = Cache::get($cacheKey);

        if ($cached instanceof Collection && $cached->every(fn (mixed $banner): bool => $banner instanceof Banner)) {
            return $cached;
        }

        if ($cached !== null) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, 300, fn (): Collection => Banner::active()
            ->where('placement', $placement)
            ->with(['desktopMedia', 'mobileMedia', 'sideMedia'])
            ->orderBy('sort_order')
            ->get());
    }

    /**
     * @return array{id: int, eyebrow: string, title: string, description: string, ctaLabel: string, image: ?string, mobileImage: ?string, sideImage: ?string, sideImageMode: string, theme: string, url: ?string}
     */
    public function present(Banner $banner): array
    {
        $theme = (string) ($banner->settings['theme'] ?? 'blue');

        if (! in_array($theme, ['blue', 'red', 'green', 'amber'], true)) {
            $theme = 'blue';
        }

        return [
            'id' => $banner->id,
            'eyebrow' => (string) $banner->eyebrow,
            'title' => (string) $banner->title,
            'description' => (string) $banner->description,
            'ctaLabel' => (string) $banner->cta_label,
            'image' => $banner->desktopMedia?->url(),
            'mobileImage' => $banner->mobileMedia?->url(),
            'sideImage' => $banner->sideMedia?->url(),
            'sideImageMode' => in_array($banner->side_image_mode, ['cutout', 'contained'], true) ? $banner->side_image_mode : 'cutout',
            'theme' => $theme,
            'url' => $this->destination($banner),
        ];
    }

    public function destination(Banner $banner): ?string
    {
        return match ($banner->destination_type) {
            'page' => ($page = Page::published()->find($banner->destination_value)) ? '/'.$page->slug : null,
            'category' => ($category = Category::active()->find($banner->destination_value)) ? '/category/'.$category->slug : null,
            'brand' => ($brand = Brand::active()->find($banner->destination_value)) ? '/brands/'.$brand->slug : null,
            'product' => ($product = Product::published()->find($banner->destination_value)) ? '/product/'.$product->slug : null,
            default => $banner->destination_value,
        };
    }
}
