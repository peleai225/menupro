<?php

namespace Database\Factories;

use App\Enums\DeliveryStatus;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Delivery>
 */
class DeliveryFactory extends Factory
{
    protected $model = Delivery::class;

    public function definition(): array
    {
        return [
            'order_id'         => Order::factory(),
            'restaurant_id'    => Restaurant::factory(),
            'status'           => DeliveryStatus::PENDING,
            'delivery_address' => fake()->address(),
        ];
    }
}
