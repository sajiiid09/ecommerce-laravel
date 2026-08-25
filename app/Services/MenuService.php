<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class MenuService
{
    public function __construct(private readonly ContentCache $cache) {}

    public function menu(string $key): ?Menu
    {
        return cache()->remember($this->cache->menu($key), 300, fn (): ?Menu => Menu::enabled()
            ->where('key', $key)
            ->with([
                'items' => fn ($query) => $query->whereNull('parent_id')->where('enabled', true)->orderBy('sort_order'),
                'items.children',
                'items.targets.target',
            ])
            ->first());
    }

    public function saveMenu(Menu $menu, array $data): Menu
    {
        $previousKey = $menu->key;
        $menu->fill($data);
        $menu->save();
        $this->cache->forget(array_filter([
            $previousKey ? $this->cache->menu($previousKey) : null,
            $this->cache->menu($menu->key),
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
        $this->cache->forget($this->cache->menu($key));
    }

    public function itemUrl(MenuItem $item): string
    {
        $targetClass = $this->targetClass($item->type);

        if ($targetClass) {
            $target = $item->targets()->where('target_type', $targetClass)->first()?->target;

            return match ($item->type) {
                'page' => $target instanceof Page && $target->status === 'published' ? '/'.$target->slug : '#',
                'category' => $target instanceof Category && $target->is_active ? '/category/'.$target->slug : '#',
                'brand' => $target instanceof Brand && $target->is_active ? '/brands/'.$target->slug : '#',
                'product' => $target instanceof Product && $target->status?->value === 'published' ? '/product/'.$target->slug : '#',
                default => '#',
            };
        }

        return $item->type === 'route' && $item->route_name
            ? route($item->route_name)
            : ($item->url ?: '#');
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
