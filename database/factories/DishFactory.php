<?php

namespace Database\Factories;

use App\Models\Dish;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dish>
 */
class DishFactory extends Factory
{
    protected $model = Dish::class;

    public function definition(): array
    {
        return [
            'restaurant_id' => \App\Models\Restaurant::factory(),
            'name'          => fake()->words(3, true),
            'price'         => fake()->numberBetween(1000, 20000),
            'is_active'     => true,
            'sort_order'    => fake()->numberBetween(0, 100),
        ];
    }
}
