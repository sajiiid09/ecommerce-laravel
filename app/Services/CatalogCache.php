<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CatalogCache
{
    public function categoryOptions(): string
    {
        return 'catalog:category-options';
    }

    public function brandOptions(): string
    {
        return 'catalog:brand-options';
    }

    public function homepage(): string
    {
        return 'cms:homepage';
    }

    public function product(string|int $product): string
    {
        return 'catalog:product:'.$product;
    }

    public function reviewSummary(string|int $product): string
    {
        return 'catalog:product:'.$product.':review-summary';
    }

    public function forgetProduct(string|int $product): void
    {
        Cache::forget($this->product($product));
        Cache::forget($this->reviewSummary($product));
    }

    public function forgetAll(): void
    {
        Cache::forget($this->categoryOptions());
        Cache::forget($this->brandOptions());
        Cache::forget($this->homepage());
    }
}
