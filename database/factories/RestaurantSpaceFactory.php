<?php

namespace Database\Factories;

use App\Models\RestaurantSpace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RestaurantSpace>
 */
class RestaurantSpaceFactory extends Factory
{
    protected $model = RestaurantSpace::class;

    public function definition(): array
    {
        return [
            'restaurant_id' => \App\Models\Restaurant::factory(),
            'name'          => fake()->randomElement(['VIP', 'VVIP', 'Salle', 'Bar', 'Terrasse']),
            'color'         => fake()->hexColor(),
            'is_active'     => true,
            'sort_order'    => fake()->numberBetween(0, 10),
        ];
    }
}
