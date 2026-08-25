<?php

namespace App\Services;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

class BannerService
{
    public function __construct(
        private readonly MediaService $media,
        private readonly ContentPublishingService $publishing,
    ) {}

    public function save(array $data, ?Banner $banner = null): Banner
    {
        return DB::transaction(function () use ($data, $banner): Banner {
            $banner ??= new Banner;
            $previousPlacement = $banner->placement;

            if (! empty($data['starts_at']) && ! empty($data['ends_at']) && $data['ends_at'] < $data['starts_at']) {
                throw new InvalidArgumentException('Banner end time must be after its start time.');
            }

            if (($data['destination_type'] ?? 'url') === 'url' && isset($data['destination_value']) && preg_match('/^(javascript|data|vbscript|file):/i', $data['destination_value'])) {
                throw new InvalidArgumentException('Unsafe banner destination.');
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
            ] as [$mediaId, $relation, $role]) {
                if (! $banner->{$mediaId}) {
                    continue;
                }

                $asset = $banner->{$relation}()->firstOrFail();
                Gate::authorize('view', $asset);
                $this->media->attach($asset, $banner, $role);
            }

            $this->publishing->invalidate('banner', $banner->placement);

            if ($previousPlacement && $previousPlacement !== $banner->placement) {
                $this->publishing->invalidate('banner', $previousPlacement);
            }

            return $banner->fresh(['desktopMedia', 'mobileMedia']);
        });
    }

    public function active(string $placement)
    {
        return Banner::active()->where('placement', $placement)->with(['desktopMedia', 'mobileMedia'])->orderBy('sort_order')->get();
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
