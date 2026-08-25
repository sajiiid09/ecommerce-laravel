<?php

namespace App\Services;

use App\Models\Category;
use App\Models\MediaAsset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CategoryService
{
    public const MAX_DEPTH = 4;

    public function __construct(private readonly MediaService $media) {}

    public function save(array $data, ?Category $category = null): Category
    {
        $parentId = $data['parent_id'] ?? null;

        if ($category && $parentId && ($category->id === (int) $parentId || $this->descendsFrom((int) $parentId, $category->id))) {
            throw new InvalidArgumentException('A category cannot be its own ancestor.');
        }

        if ($parentId && $this->depthFor((int) $parentId) >= self::MAX_DEPTH) {
            throw new InvalidArgumentException('Categories can be nested up to '.self::MAX_DEPTH.' levels.');
        }

        $slug = Str::slug($data['slug'] ?? $data['name']);
        if (Category::where('slug', $slug)->when($category, fn ($query) => $query->where('id', '<>', $category->id))->exists()) {
            throw new InvalidArgumentException('This category slug is already in use.');
        }

        return DB::transaction(function () use ($data, $category, $parentId, $slug): Category {
            $category ??= new Category;
            $previousMediaId = $category->exists ? $category->media_asset_id : null;
            $category->fill([
                'name' => $data['name'],
                'slug' => $slug,
                'description' => $data['description'] ?? null,
                'parent_id' => $parentId,
                'media_asset_id' => $data['media_asset_id'] ?? null,
                'image_url' => $data['image_url'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'sort_order' => $data['sort_order'] ?? 0,
            ]);
            $category->save();

            if ($category->media_asset_id) {
                $asset = MediaAsset::findOrFail($category->media_asset_id);
                $this->media->attach($asset, $category, 'category.featured_media');
            }

            if ($previousMediaId && $previousMediaId !== $category->media_asset_id) {
                $previousAsset = MediaAsset::find($previousMediaId);
                if ($previousAsset) {
                    $this->media->detach($previousAsset, $category, 'category.featured_media');
                }
            }

            return $category->fresh(['parent', 'mediaAsset']);
        });
    }

    private function depthFor(int $categoryId): int
    {
        $depth = 1;
        $current = Category::find($categoryId);

        while ($current?->parent_id && $depth <= self::MAX_DEPTH + 1) {
            $depth++;
            $current = Category::find($current->parent_id);
        }

        return $depth;
    }

    private function descendsFrom(int $candidate, int $ancestor): bool
    {
        $current = Category::find($candidate);

        while ($current) {
            if ((int) $current->parent_id === $ancestor) {
                return true;
            }

            $current = $current->parent_id ? Category::find($current->parent_id) : null;
        }

        return false;
    }
}
