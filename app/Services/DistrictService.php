<?php

namespace App\Services;

use App\Models\District;
use Illuminate\Database\Eloquent\Collection;

class DistrictService
{
    public const EXPRESS_SURCHARGE_MINOR = 6000;

    /** @return Collection<int, District> */
    public function active(): Collection
    {
        return District::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
    }

    public function find(?int $districtId, bool $includeInactive = false): ?District
    {
        if ($districtId === null) {
            return null;
        }

        $query = District::query()->whereKey($districtId);
        if (! $includeInactive) {
            $query->where('is_active', true);
        }

        return $query->first();
    }

    public function shippingMinor(?District $district, string $deliveryMethod): int
    {
        return (int) ($district?->delivery_fee_minor ?? 0)
            + ($deliveryMethod === 'express' ? self::EXPRESS_SURCHARGE_MINOR : 0);
    }
}
