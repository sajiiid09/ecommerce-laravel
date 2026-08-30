<?php

namespace App\Models;

use Database\Factories\UserAddressFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'user_id', 'label', 'recipient_name', 'phone', 'address_line', 'city', 'district', 'district_id',
    'postal_code', 'country', 'is_default',
])]
class UserAddress extends Model
{
    /** @use HasFactory<UserAddressFactory> */
    use HasFactory;

    protected $attributes = [
        'country' => 'BD',
        'is_default' => false,
    ];

    protected function casts(): array
    {
        return ['district_id' => 'integer', 'is_default' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::creating(function (UserAddress $address): void {
            if (! $address->is_default && ! static::query()->where('user_id', $address->user_id)->exists()) {
                $address->is_default = true;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function districtName(): string
    {
        if ($this->district_id) {
            $managedDistrict = $this->relationLoaded('district')
                ? $this->getRelation('district')
                : District::query()->find($this->district_id);

            if ($managedDistrict instanceof District) {
                return $managedDistrict->name;
            }
        }

        $district = json_decode((string) $this->getRawOriginal('district'), true);

        if (is_array($district) && filled($district['name'] ?? null)) {
            return trim((string) $district['name']);
        }

        return trim((string) $this->getRawOriginal('district'));
    }

    public function legacyDistrictId(): ?int
    {
        $district = json_decode((string) $this->getRawOriginal('district'), true);
        $districtId = is_array($district) ? filter_var($district['id'] ?? null, FILTER_VALIDATE_INT) : false;

        return $districtId !== false && $districtId !== null && $districtId > 0 ? $districtId : null;
    }

    public function makeDefault(): void
    {
        DB::transaction(function (): void {
            User::query()->whereKey($this->user_id)->lockForUpdate()->firstOrFail();
            $address = static::query()->whereKey($this->getKey())->lockForUpdate()->firstOrFail();

            static::query()
                ->where('user_id', $address->user_id)
                ->where('id', '!=', $address->getKey())
                ->update(['is_default' => false]);

            $address->forceFill(['is_default' => true])->save();
            $this->setAttribute('is_default', true);
        });
    }

    public function delete(): ?bool
    {
        return DB::transaction(function (): ?bool {
            $userId = $this->user_id;
            $wasDefault = $this->is_default;
            User::query()->whereKey($userId)->lockForUpdate()->firstOrFail();
            $deleted = parent::delete();

            if ($deleted && $wasDefault) {
                $replacement = static::query()
                    ->where('user_id', $userId)
                    ->orderByDesc('created_at')
                    ->orderByDesc('id')
                    ->lockForUpdate()
                    ->first();

                $replacement?->forceFill(['is_default' => true])->save();
            }

            return $deleted;
        });
    }
}
