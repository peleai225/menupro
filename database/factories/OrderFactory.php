<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = fake()->numberBetween(2000, 50000);

        return [
            'restaurant_id'  => \App\Models\Restaurant::factory(),
            'customer_name'  => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => fake()->numerify('+225 07########'),
            'type'           => OrderType::DINE_IN,
            'status'         => OrderStatus::DRAFT,
            'subtotal'       => $subtotal,
            'delivery_fee'   => 0,
            'discount_amount' => 0,
            'tax_amount'     => 0,
            'service_fee'    => 0,
            'total'          => $subtotal,
            'payment_status' => PaymentStatus::PENDING,
        ];
    }
}
