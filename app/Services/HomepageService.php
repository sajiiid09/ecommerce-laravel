<?php

namespace App\Services;

use App\Models\HomepageRevision;
use App\Models\HomepageSection;
use App\Support\StorefrontCatalog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class HomepageService
{
    private const SECTION_TYPES = [
        'hero', 'trust', 'categories', 'flash_deals', 'bestsellers', 'featured_products',
        'brands', 'new_arrivals', 'banners', 'shop_by_need', 'testimonials', 'newsletter',
    ];

    public function sections(bool $publishedOnly = true): array
    {
        if (! $publishedOnly) {
            return HomepageSection::ordered()
                ->get()
                ->map(fn (HomepageSection $section): array => $this->hydrateSection($section->toArray()))
                ->all();
        }

        return cache()->remember('cms:homepage', 300, function (): array {
            $sections = HomepageSection::ordered()
                ->enabled()
                ->get()
                ->map(fn (HomepageSection $section): array => $this->hydrateSection($section->toArray()))
                ->all();

            return $sections ?: $this->fallback();
        });
    }

    public function saveSections(array $sections): void
    {
        $this->validateSections($sections);

        DB::transaction(function () use ($sections): void {
            $keys = [];

            foreach ($sections as $index => $data) {
                $key = Str::slug((string) $data['section_key']);
                $keys[] = $key;
                $section = HomepageSection::firstOrNew(['section_key' => $key]);
                $section->fill([
                    'type' => $data['type'],
                    'title' => $data['title'] ?? null,
                    'eyebrow' => $data['eyebrow'] ?? null,
                    'subtitle' => $data['subtitle'] ?? null,
                    'enabled' => (bool) ($data['enabled'] ?? true),
                    'sort_order' => $index,
                    'settings' => $data['settings'] ?? [],
                ]);
                $section->forceFill(['updated_by' => auth()->id()]);
                if (! $section->exists) {
                    $section->forceFill(['created_by' => auth()->id()]);
                }
                $section->save();
            }

            $query = HomepageSection::query();

            if ($keys === []) {
                $query->delete();
            } else {
                $query->whereNotIn('section_key', $keys)->delete();
            }

            $version = ((int) HomepageRevision::max('version')) + 1;
            $revision = HomepageRevision::create([
                'version' => $version,
                'snapshot' => $sections,
                'status' => 'draft',
            ]);
            $revision->forceFill(['created_by' => auth()->id()])->save();
        });

        Cache::forget('cms:homepage');
    }

    public function restoreRevision(HomepageRevision $revision): void
    {
        $this->saveSections($revision->snapshot ?? []);
    }

    public function publishRevision(HomepageRevision $revision): void
    {
        DB::transaction(function () use ($revision): void {
            $this->saveSections($revision->snapshot ?? []);
            HomepageRevision::query()->update(['status' => 'draft']);
            $revision->forceFill(['status' => 'published', 'published_at' => now()])->save();
        });
    }

    public function fallback(): array
    {
        return [
            ['section_key' => 'hero', 'type' => 'hero', 'title' => 'Back to Better Deals Every Day!', 'enabled' => true, 'sort_order' => 0, 'settings' => ['cta' => 'Shop Now']],
            ['section_key' => 'trust', 'type' => 'trust', 'title' => 'Why Shop with StoreZ?', 'enabled' => true, 'sort_order' => 1, 'settings' => []],
            ['section_key' => 'categories', 'type' => 'categories', 'title' => 'Shop by Category', 'enabled' => true, 'sort_order' => 2, 'settings' => ['categories' => StorefrontCatalog::categories()]],
            ['section_key' => 'flash-deals', 'type' => 'flash_deals', 'title' => 'Flash Sale', 'enabled' => true, 'sort_order' => 3, 'settings' => ['products' => array_slice(StorefrontCatalog::products(), 0, 6)]],
            ['section_key' => 'bestsellers', 'type' => 'bestsellers', 'title' => 'Best Sellers', 'enabled' => true, 'sort_order' => 4, 'settings' => ['products' => array_slice(StorefrontCatalog::products(), 3, 6)]],
            ['section_key' => 'featured-products', 'type' => 'featured_products', 'title' => 'Fresh Picks for You', 'enabled' => true, 'sort_order' => 5, 'settings' => ['products' => array_slice(StorefrontCatalog::products(), 0, 6)]],
            ['section_key' => 'brands', 'type' => 'brands', 'title' => 'Top Brands You Trust', 'enabled' => true, 'sort_order' => 6, 'settings' => ['brands' => StorefrontCatalog::brands()]],
            ['section_key' => 'new-arrivals', 'type' => 'new_arrivals', 'title' => 'New Arrivals', 'enabled' => true, 'sort_order' => 7, 'settings' => ['products' => array_slice(StorefrontCatalog::products(), 6, 6)]],
            ['section_key' => 'banners', 'type' => 'banners', 'title' => 'Featured Promotions', 'enabled' => true, 'sort_order' => 8, 'settings' => []],
            ['section_key' => 'shop-by-need', 'type' => 'shop_by_need', 'title' => 'Shop by Need', 'enabled' => true, 'sort_order' => 9, 'settings' => []],
            ['section_key' => 'testimonials', 'type' => 'testimonials', 'title' => 'What Our Customers Say', 'enabled' => true, 'sort_order' => 10, 'settings' => []],
            ['section_key' => 'newsletter', 'type' => 'newsletter', 'title' => 'Stay in the loop', 'enabled' => true, 'sort_order' => 11, 'settings' => []],
        ];
    }

    private function validateSections(array $sections): void
    {
        $keys = [];

        foreach ($sections as $section) {
            if (! is_array($section) || ! isset($section['section_key'], $section['type'])) {
                throw new InvalidArgumentException('Each homepage section requires a key and type.');
            }

            if (! in_array($section['type'], self::SECTION_TYPES, true)) {
                throw new InvalidArgumentException('The selected homepage section type is invalid.');
            }

            if (isset($section['settings']) && ! is_array($section['settings'])) {
                throw new InvalidArgumentException('Homepage section settings must be a JSON object.');
            }

            $key = Str::slug((string) $section['section_key']);

            if ($key === '' || in_array($key, $keys, true)) {
                throw new InvalidArgumentException('Homepage section keys must be unique.');
            }

            $keys[] = $key;
        }
    }

    private function hydrateSection(array $section): array
    {
        $settings = $section['settings'] ?? [];

        if (($section['type'] ?? null) === 'categories' && empty($settings['categories'])) {
            $settings['categories'] = StorefrontCatalog::categories();
        }

        if (in_array($section['type'] ?? null, ['featured_products', 'bestsellers', 'new_arrivals', 'flash_deals'], true) && empty($settings['products'])) {
            $offset = match ($section['type']) {
                'bestsellers' => 3,
                'new_arrivals' => 6,
                default => 0,
            };
            $settings['products'] = array_slice(StorefrontCatalog::products(), $offset, 6);
        }

        if (($section['type'] ?? null) === 'brands' && empty($settings['brands'])) {
            $settings['brands'] = StorefrontCatalog::brands();
        }

        $section['settings'] = $settings;

        return $section;
    }
}
