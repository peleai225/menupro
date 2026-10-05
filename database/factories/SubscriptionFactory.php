<?php

namespace Database\Factories;

use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'restaurant_id' => Restaurant::factory(),
            'plan_id' => Plan::factory(),
            'status' => SubscriptionStatus::ACTIVE,
            'is_trial' => false,
            'trial_days' => 0,
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
            'amount_paid' => 5000,
            'billing_period' => 'monthly',
            'discount_percentage' => 0,
        ];
    }
}
