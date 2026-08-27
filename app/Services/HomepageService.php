<?php

namespace App\Services;

use App\Models\HomepageRevision;
use App\Models\HomepageSection;
use App\Models\MediaAsset;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use InvalidArgumentException;

class HomepageService
{
    private const SECTION_TYPES = [
        'hero', 'trust', 'categories', 'products', 'brands', 'banners', 'shop_by_need',
        'testimonials', 'newsletter',
        'flash_deals', 'bestsellers', 'featured_products', 'new_arrivals',
    ];

    private const LEGACY_PRODUCT_SECTION_SOURCES = [
        'featured_products' => 'featured',
        'new_arrivals' => 'newest',
        'bestsellers' => 'bestsellers',
        'flash_deals' => 'on_sale',
    ];

    public function __construct(
        private readonly CatalogQueryService $catalog,
        private readonly BannerService $banners,
    ) {}

    public function sections(bool $publishedOnly = true): array
    {
        if (! Schema::hasTable('homepage_sections')) {
            return $this->hydratedFallback();
        }

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

            return $sections ?: $this->hydratedFallback();
        });
    }

    public function preview(array $sections): array
    {
        return collect($sections)
            ->map(fn (array $section): array => $this->hydrateSection($section))
            ->all();
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
            ['section_key' => 'hero', 'type' => 'hero', 'title' => 'Back to Better Deals Every Day!', 'enabled' => true, 'sort_order' => 0, 'settings' => ['cta' => 'Shop Now', 'heroBanners' => []]],
            ['section_key' => 'trust', 'type' => 'trust', 'title' => 'Why Shop with StoreZ?', 'enabled' => true, 'sort_order' => 1, 'settings' => []],
            ['section_key' => 'categories', 'type' => 'categories', 'title' => 'Shop by Category', 'enabled' => true, 'sort_order' => 2, 'settings' => []],
            ['section_key' => 'flash-deals', 'type' => 'products', 'title' => 'Flash Sale', 'enabled' => true, 'sort_order' => 3, 'settings' => ['source' => 'on_sale', 'sort' => 'default', 'limit' => 6]],
            ['section_key' => 'bestsellers', 'type' => 'products', 'title' => 'Best Sellers', 'enabled' => true, 'sort_order' => 4, 'settings' => ['source' => 'bestsellers', 'sort' => 'default', 'limit' => 6]],
            ['section_key' => 'featured-products', 'type' => 'products', 'title' => 'Fresh Picks for You', 'enabled' => true, 'sort_order' => 5, 'settings' => ['source' => 'featured', 'sort' => 'default', 'limit' => 6]],
            ['section_key' => 'brands', 'type' => 'brands', 'title' => 'Top Brands You Trust', 'enabled' => true, 'sort_order' => 6, 'settings' => []],
            ['section_key' => 'new-arrivals', 'type' => 'products', 'title' => 'New Arrivals', 'enabled' => true, 'sort_order' => 7, 'settings' => ['source' => 'newest', 'sort' => 'default', 'limit' => 6]],
            ['section_key' => 'banners', 'type' => 'banners', 'title' => 'Featured Promotions', 'enabled' => true, 'sort_order' => 8, 'settings' => []],
            ['section_key' => 'shop-by-need', 'type' => 'shop_by_need', 'title' => 'Shop by Need', 'enabled' => true, 'sort_order' => 9, 'settings' => []],
            ['section_key' => 'testimonials', 'type' => 'testimonials', 'title' => 'What Our Customers Say', 'enabled' => true, 'sort_order' => 10, 'settings' => ['testimonials' => [
                ['id' => 'fallback-1', 'name' => 'Nusrat Jahan', 'role' => 'Verified customer', 'rating' => 5, 'quote' => 'Great experience! Fast delivery and products were exactly as described.', 'avatar_media_id' => null, 'enabled' => true, 'sort_order' => 0],
                ['id' => 'fallback-2', 'name' => 'Rafiq Ahmed', 'role' => 'Verified customer', 'rating' => 5, 'quote' => 'Love the combo deals and COD option. Very convenient.', 'avatar_media_id' => null, 'enabled' => true, 'sort_order' => 1],
                ['id' => 'fallback-3', 'name' => 'Tania Rahman', 'role' => 'Verified customer', 'rating' => 5, 'quote' => 'Quality products, best prices and excellent customer service.', 'avatar_media_id' => null, 'enabled' => true, 'sort_order' => 2],
            ]]],
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

            if ($section['type'] === 'testimonials') {
                $this->validateTestimonials(($section['settings'] ?? [])['testimonials'] ?? null);
            }

            if ($section['type'] === 'products') {
                $this->validateProductSettings($section['settings'] ?? []);
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

        if (isset(self::LEGACY_PRODUCT_SECTION_SOURCES[$section['type'] ?? ''])) {
            $settings['source'] ??= self::LEGACY_PRODUCT_SECTION_SOURCES[$section['type']];
            $section['type'] = 'products';
        }

        if (($section['type'] ?? null) === 'categories') {
            $settings['categories'] = array_slice($this->catalog->categoryOptions(), 0, max(1, min(24, (int) ($settings['limit'] ?? 12))));
        }

        if (($section['type'] ?? null) === 'brands') {
            $settings['brands'] = array_slice($this->catalog->brandOptions(), 0, max(1, min(24, (int) ($settings['limit'] ?? 12))));
        }

        if (($section['type'] ?? null) === 'banners' && Schema::hasTable('banners')) {
            $placement = (string) ($settings['placement'] ?? 'homepage');
            $settings['banners'] = $this->banners->active($placement)
                ->take(max(1, min(12, (int) ($settings['limit'] ?? 6))))
                ->map(fn ($banner): array => $this->banners->present($banner))
                ->all();
        }

        if (($section['type'] ?? null) === 'hero') {
            $settings['heroBanners'] = Schema::hasTable('banners')
                ? $this->banners->active('hero')
                    ->map(fn ($banner): array => $this->banners->present($banner))
                    ->all()
                : [];

            foreach (['desktop_media_id' => 'desktopImage', 'mobile_media_id' => 'mobileImage'] as $mediaKey => $imageKey) {
                if (! empty($settings[$mediaKey]) && ($asset = MediaAsset::find($settings[$mediaKey]))) {
                    $settings[$imageKey] = $asset->url();
                }
            }
        }

        if (($section['type'] ?? null) === 'testimonials') {
            $settings['testimonials'] = $this->hydrateTestimonials($settings);
        }

        if (($section['type'] ?? null) === 'products') {
            $settings = $this->catalog->normalizeHomepageProductSettings($settings);
            $settings['products'] = $this->catalog->homepageProducts($settings);
        }

        $section['settings'] = $settings;

        return $section;
    }

    private function validateProductSettings(array $settings): void
    {
        $sources = ['featured', 'newest', 'bestsellers', 'on_sale', 'category', 'brand'];
        $sorts = ['default', 'newest', 'price_asc', 'price_desc'];
        $source = (string) ($settings['source'] ?? 'featured');
        $sort = (string) ($settings['sort'] ?? 'default');
        $limit = $settings['limit'] ?? 6;

        if (! in_array($source, $sources, true) || ! in_array($sort, $sorts, true)) {
            throw new InvalidArgumentException('Homepage product query settings are invalid.');
        }

        if ((! is_int($limit) && ! ctype_digit((string) $limit)) || (int) $limit < 1 || (int) $limit > 24) {
            throw new InvalidArgumentException('Homepage product limits must be between 1 and 24.');
        }

        if ($source === 'category' && blank($settings['category'] ?? null)) {
            throw new InvalidArgumentException('A category is required for category product sections.');
        }

        if ($source === 'brand' && blank($settings['brand'] ?? null)) {
            throw new InvalidArgumentException('A brand is required for brand product sections.');
        }
    }

    private function hydratedFallback(): array
    {
        return collect($this->fallback())
            ->map(fn (array $section): array => $this->hydrateSection($section))
            ->all();
    }

    private function validateTestimonials(mixed $testimonials): void
    {
        if ($testimonials === null) {
            return;
        }

        if (! is_array($testimonials)) {
            throw new InvalidArgumentException('Testimonials must be an array.');
        }

        $ids = [];

        foreach ($testimonials as $testimonial) {
            if (! is_array($testimonial)) {
                throw new InvalidArgumentException('Each testimonial must be an object.');
            }

            $id = (string) ($testimonial['id'] ?? '');
            $name = trim((string) ($testimonial['name'] ?? ''));
            $quote = trim((string) ($testimonial['quote'] ?? ''));
            $rating = $testimonial['rating'] ?? null;

            if ($id === '' || in_array($id, $ids, true)) {
                throw new InvalidArgumentException('Testimonial IDs must be unique.');
            }

            if ($name === '' || mb_strlen($name) > 255 || $quote === '' || mb_strlen($quote) > 2000) {
                throw new InvalidArgumentException('Testimonials require a name and quote within the allowed length.');
            }

            if (! is_int($rating) && ! ctype_digit((string) $rating)) {
                throw new InvalidArgumentException('Testimonial ratings must be whole numbers from 1 to 5.');
            }

            if ((int) $rating < 1 || (int) $rating > 5) {
                throw new InvalidArgumentException('Testimonial ratings must be whole numbers from 1 to 5.');
            }

            if (! empty($testimonial['avatar_media_id'])) {
                $asset = MediaAsset::find((int) $testimonial['avatar_media_id']);

                if (! $asset) {
                    throw new InvalidArgumentException('The selected testimonial avatar is invalid.');
                }

                Gate::authorize('view', $asset);
            }

            $ids[] = $id;
        }
    }

    private function hydrateTestimonials(array $settings): array
    {
        $testimonials = $settings['testimonials'] ?? null;

        if (! is_array($testimonials)) {
            $legacyQuote = trim((string) ($settings['content_json'] ?? ''));

            return $legacyQuote === '' ? [] : [[
                'id' => 'legacy-testimonial',
                'name' => 'StoreZ customer',
                'role' => 'Verified customer',
                'rating' => 5,
                'quote' => $legacyQuote,
                'avatar_media_id' => null,
                'avatar' => null,
                'enabled' => true,
                'sort_order' => 0,
            ]];
        }

        $mediaIds = collect($testimonials)
            ->pluck('avatar_media_id')
            ->filter()
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values();
        $avatars = $mediaIds->isEmpty()
            ? collect()
            : MediaAsset::query()->whereIn('id', $mediaIds)->get()->keyBy('id');

        return collect($testimonials)
            ->filter(fn (mixed $testimonial): bool => is_array($testimonial) && ($testimonial['enabled'] ?? true))
            ->sortBy('sort_order')
            ->values()
            ->map(function (array $testimonial, int $index) use ($avatars): array {
                $avatarId = (int) ($testimonial['avatar_media_id'] ?? 0);

                return [
                    'id' => (string) ($testimonial['id'] ?? 'testimonial-'.$index),
                    'name' => (string) ($testimonial['name'] ?? 'StoreZ customer'),
                    'role' => (string) ($testimonial['role'] ?? 'Verified customer'),
                    'rating' => max(1, min(5, (int) ($testimonial['rating'] ?? 5))),
                    'quote' => (string) ($testimonial['quote'] ?? ''),
                    'avatar_media_id' => $avatarId ?: null,
                    'avatar' => $avatars->get($avatarId)?->url(),
                    'enabled' => true,
                    'sort_order' => (int) ($testimonial['sort_order'] ?? $index),
                ];
            })
            ->all();
    }
}
