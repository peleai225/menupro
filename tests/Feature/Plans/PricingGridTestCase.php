<?php

namespace Tests\Feature\Plans;

use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Base des tests de la grille d'abonnements 2026-10 : plans du seeder + aides de création.
 */
abstract class PricingGridTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
    }

    protected function plan(string $slug): Plan
    {
        return Plan::where('slug', $slug)->firstOrFail();
    }

    /**
     * Restaurant actif avec un abonnement payant en cours sur le plan donné.
     */
    protected function subscribedRestaurant(string $slug, array $restaurantAttributes = [], array $subscriptionAttributes = []): Restaurant
    {
        $plan = $this->plan($slug);
        $restaurant = Restaurant::factory()->create(array_merge([
            'current_plan_id' => $plan->id,
            'subscription_ends_at' => now()->addDays(20),
        ], $restaurantAttributes));

        Subscription::factory()->create(array_merge([
            'restaurant_id' => $restaurant->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::ACTIVE,
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->addDays(20),
            'amount_paid' => $plan->price,
        ], $subscriptionAttributes));

        return $restaurant->fresh();
    }

    protected function adminOf(Restaurant $restaurant): User
    {
        return User::factory()->restaurantAdmin($restaurant)->create();
    }
}
