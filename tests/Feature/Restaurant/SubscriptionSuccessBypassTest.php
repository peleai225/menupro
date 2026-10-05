<?php

namespace Tests\Feature\Restaurant;

use App\Enums\RestaurantStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionSuccessBypassTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscription_without_payment_reference_is_not_silently_activated(): void
    {
        $restaurant = Restaurant::factory()->create(['status' => RestaurantStatus::ACTIVE]);
        $owner = User::factory()->restaurantAdmin($restaurant)->create();
        $plan = Plan::factory()->create(['is_active' => true]);

        // Simule une session de paiement jamais créée avec succès (gateway down,
        // payment_reference resté null) : c'est l'état laissé par convertTrial()
        // quand createSubscriptionPaymentSession() échoue.
        $subscription = Subscription::factory()->create([
            'restaurant_id' => $restaurant->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::PENDING,
            'payment_reference' => null,
        ]);

        $this->actingAs($owner)
            ->get(route('restaurant.subscription.success', $subscription));

        $this->assertNotSame(SubscriptionStatus::ACTIVE, $subscription->refresh()->status);
    }
}
