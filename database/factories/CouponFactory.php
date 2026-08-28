<?php

namespace Database\Factories;

use App\Enums\CouponDiscountType;
use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'STOREZ-'.$this->faker->unique()->bothify('######??'),
            'discount_type' => CouponDiscountType::Percentage,
            'percentage' => 10,
            'amount_minor' => null,
            'minimum_subtotal_minor' => 0,
            'starts_at' => null,
            'ends_at' => null,
            'usage_limit' => null,
            'per_customer_limit' => null,
            'is_active' => true,
        ];
    }
}
