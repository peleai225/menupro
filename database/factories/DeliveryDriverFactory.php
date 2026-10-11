<?php

namespace Database\Factories;

use App\Models\DeliveryDriver;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DeliveryDriver>
 */
class DeliveryDriverFactory extends Factory
{
    protected $model = DeliveryDriver::class;

    public function definition(): array
    {
        return [
            'name'                 => fake()->name(),
            'phone'                => fake()->numerify('+225 07########'),
            'vehicle_type'         => 'moto',
            'token'                => Str::random(64),
            'verification_status'  => 'approved',
            'is_active'            => true,
            'is_available'         => false,
        ];
    }
}
