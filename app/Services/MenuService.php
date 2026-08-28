<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MenuService
{
    public function __construct(private readonly ContentCache $cache) {}

    public function menu(string $key): ?Menu
    {
        $cacheKey = $this->cache->menu($key);
        if (cache()->has($cacheKey)) {
            $cached = cache()->get($cacheKey);

            if ($cached instanceof Menu || $cached === null) {
                return $cached;
            }

            cache()->forget($cacheKey);
        }

        return cache()->remember($cacheKey, 300, fn (): ?Menu => Menu::enabled()
            ->where('key', $key)
            ->with([
                'items' => fn ($query) => $query->whereNull('parent_id')->where('enabled', true)->orderBy('sort_order'),
                'items.children' => fn ($query) => $query->where('enabled', true)->orderBy('sort_order'),
                'items.children.targets.target',
                'items.targets.target',
            ])
            ->first());
    }

    /**
     * @return array<int, array{label: string, url: string, enabled: bool, children: array<int, array{label: string, url: string, enabled: bool}>}>
     */
    public function navigation(string $key): array
    {
        return Cache::remember($this->cache->navigation($key), 300, function () use ($key): array {
            $menu = $this->menu($key);

            if (! $menu) {
                return [];
            }

            return $menu->items
                ->map(fn (MenuItem $item): array => [
                    'label' => $item->label,
                    'url' => $this->itemUrl($item),
                    'enabled' => $item->enabled,
                    'children' => $item->children->map(fn (MenuItem $child): array => [
                        'label' => $child->label,
                        'url' => $this->itemUrl($child),
                        'enabled' => $child->enabled,
                    ])->all(),
                ])->all();
        });
    }

    public function saveMenu(Menu $menu, array $data): Menu
    {
        $previousKey = $menu->key;
        $menu->fill($data);
        $menu->save();
        $this->cache->forget(array_filter([
            $previousKey ? $this->cache->menu($previousKey) : null,
            $this->cache->menu($menu->key),
            $previousKey ? $this->cache->navigation($previousKey) : null,
            $this->cache->navigation($menu->key),
        ]));

        return $menu;
    }

    public function saveItem(MenuItem $item, array $data): MenuItem
    {
        return DB::transaction(function () use ($item, $data): MenuItem {
            $item->fill([
                'label' => $data['label'] ?? $item->label,
                'type' => $data['type'] ?? $item->type,
                'url' => $data['type'] === 'custom_url' ? ($data['url'] ?? null) : null,
                'route_name' => $data['type'] === 'route' ? ($data['route_name'] ?? null) : null,
                'parent_id' => $data['parent_id'] ?? null,
                'enabled' => (bool) ($data['enabled'] ?? true),
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'settings' => $data['settings'] ?? [],
            ]);
            $item->save();

            $item->targets()->delete();

            if ($targetClass = $this->targetClass($item->type)) {
                $target = $targetClass::query()->findOrFail((int) $data['target_id']);
                $item->targets()->create([
                    'target_type' => $targetClass,
                    'target_id' => $target->getKey(),
                ]);
            }

            $this->invalidate($item->menu()->value('key'));

            return $item->fresh(['targets.target']);
        });
    }

    public function invalidate(string $key): void
    {
        $this->cache->forget([
            $this->cache->menu($key),
            $this->cache->navigation($key),
        ]);
    }

    public function itemUrl(MenuItem $item): string
    {
        $targetClass = $this->targetClass($item->type);

        if ($targetClass) {
            $target = $item->relationLoaded('targets')
                ? $item->targets->firstWhere('target_type', $targetClass)?->target
                : $item->targets()->where('target_type', $targetClass)->first()?->target;

            return match ($item->type) {
                'page' => $target instanceof Page && $target->status === 'published' ? '/'.$target->slug : '#',
                'category' => $target instanceof Category && $target->is_active ? '/category/'.$target->slug : '#',
                'brand' => $target instanceof Brand && $target->is_active ? '/brands/'.$target->slug : '#',
                'product' => $target instanceof Product && $target->status?->value === 'published' ? '/product/'.$target->slug : '#',
                default => '#',
            };
        }

        if ($item->type === 'route' && $item->route_name) {
            return route($item->route_name);
        }

        if (filled($item->url) && str_starts_with($item->url, '/')) {
            return url($item->url);
        }

        return $item->url ?: '#';
    }

    private function targetClass(string $type): ?string
    {
        return match ($type) {
            'page' => Page::class,
            'category' => Category::class,
            'brand' => Brand::class,
            'product' => Product::class,
            default => null,
        };
    }
}
