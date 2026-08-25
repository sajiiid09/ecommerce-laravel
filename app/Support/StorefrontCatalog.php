<?php

namespace App\Support;

use App\Services\CatalogQueryService;

final class StorefrontCatalog
{
    public static function products(): array
    {
        return app(CatalogQueryService::class)->products([], 1000)->getCollection()->all();
    }

    public static function categories(): array
    {
        return app(CatalogQueryService::class)->categoryOptions();
    }

    public static function brands(): array
    {
        return app(CatalogQueryService::class)->brandOptions();
    }

    public static function product(string $slug): ?array
    {
        return app(CatalogQueryService::class)->product($slug);
    }
}
