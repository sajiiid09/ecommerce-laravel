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
        return 'catalog:product:v2:'.$product;
    }

    public function reviewSummary(string|int $product): string
    {
        return 'catalog:product:'.$product.':review-summary';
    }

    public function approvedReviews(string|int $product): string
    {
        return 'catalog:product:'.$product.':approved-reviews:v2';
    }

    public function forgetProduct(string|int $product, string|int|null $reviewProduct = null): void
    {
        Cache::forget($this->product($product));
        $reviewKey = $reviewProduct ?? $product;
        Cache::forget($this->reviewSummary($reviewKey));
        Cache::forget($this->approvedReviews($reviewKey));
    }

    public function forgetAll(): void
    {
        Cache::forget($this->categoryOptions());
        Cache::forget($this->brandOptions());
        Cache::forget($this->homepage());
    }
}
