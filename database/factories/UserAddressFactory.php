<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserAddress>
 */
class UserAddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'label' => fake()->randomElement(['Home', 'Office', 'Other']),
            'recipient_name' => fake()->name(),
            'phone' => fake()->numerify('01#########'),
            'address_line' => fake()->streetAddress(),
            'city' => 'Dhaka',
            'district' => 'Dhaka',
            'postal_code' => fake()->numerify('12##'),
            'country' => 'BD',
            'is_default' => false,
        ];
    }
}
