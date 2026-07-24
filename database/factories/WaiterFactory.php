<?php
namespace Database\Factories;

use App\Models\Waiter;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class WaiterFactory extends Factory
{
    protected $model = Waiter::class;

    public function definition(): array
    {
        return [
            'restaurant_id'   => \App\Models\Restaurant::factory(),
            'space_id'        => null,
            'name'            => fake()->name(),
            'pin_hash'        => Hash::make('1234'),
            'is_active'       => true,
            'failed_attempts' => 0,
            'locked_until'    => null,
        ];
    }
}
