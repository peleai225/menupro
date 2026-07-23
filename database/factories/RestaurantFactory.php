<?php

namespace Database\Factories;

use App\Enums\RestaurantStatus;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Restaurant>
 */
class RestaurantFactory extends Factory
{
    protected $model = Restaurant::class;

    public function definition(): array
    {
        return [
            'name'    => fake()->company(),
            'email'   => fake()->unique()->safeEmail(),
            'phone'   => fake()->numerify('+225 07########'),
            'status'  => RestaurantStatus::ACTIVE,
            'city'    => fake()->city(),
            'address' => fake()->address(),
        ];
    }
}
