<?php

namespace App\Models;

use Database\Factories\DistrictFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    /** @use HasFactory<DistrictFactory> */
    use HasFactory;

    protected $fillable = ['name', 'delivery_fee_minor', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['delivery_fee_minor' => 'integer', 'is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function userAddresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }
}
