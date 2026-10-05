<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plan>
 */
class PlanFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'price' => 5000,
            'duration_days' => 30,
            'max_dishes' => 20,
            'max_categories' => 5,
            'max_employees' => 1,
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 0,
        ];
    }
}
