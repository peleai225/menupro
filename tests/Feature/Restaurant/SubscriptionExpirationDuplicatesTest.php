<?php

namespace Tests\Feature\Restaurant;

use App\Enums\SubscriptionStatus;
use App\Jobs\ProcessSubscriptionExpiration;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SubscriptionExpirationDuplicatesTest extends TestCase
{
    use RefreshDatabase;

    private function subscription(Restaurant $restaurant, Plan $plan, string $endsAt): Subscription
    {
        return Subscription::factory()->create([
            'restaurant_id' => $restaurant->id,
            'plan_id'       => $plan->id,
            'status'        => SubscriptionStatus::ACTIVE,
            'is_trial'      => false,
            'starts_at'     => now()->subMonths(3),
            'ends_at'       => $endsAt,
        ]);
    }

    public function test_old_duplicate_does_not_block_a_restaurant_that_renewed(): void
    {
        Notification::fake();
        $plan = Plan::factory()->create();
        $restaurant = Restaurant::factory()->create(['orders_blocked' => false]);
        User::factory()->restaurantAdmin($restaurant)->create();

        $old = $this->subscription($restaurant, $plan, now()->subDays(10));
        $this->subscription($restaurant, $plan, now()->addDays(20));

        (new ProcessSubscriptionExpiration())->handle();

        $this->assertSame(SubscriptionStatus::EXPIRED, $old->fresh()->status);
        $this->assertFalse((bool) $restaurant->fresh()->orders_blocked);
        Notification::assertNothingSent();
    }

    public function test_subscription_of_a_deleted_restaurant_is_expired_quietly(): void
    {
        Notification::fake();
        $plan = Plan::factory()->create();
        $deleted = Restaurant::factory()->create();
        User::factory()->restaurantAdmin($deleted)->create();
        $orphan = $this->subscription($deleted, $plan, now()->subDays(2));
        $deleted->delete(); // soft delete

        $alive = Restaurant::factory()->create(['orders_blocked' => false]);
        $this->subscription($alive, $plan, now()->subDay());

        // Avant : « Attempt to read property "id" on null » sur l'abonnement orphelin
        (new ProcessSubscriptionExpiration())->handle();

        $orphan->refresh();
        $this->assertSame(SubscriptionStatus::EXPIRED, $orphan->status);
        $this->assertNotNull($orphan->expired_notification_sent_at);
        Notification::assertNothingSent();
        // Les autres abonnements sont toujours traités
        $this->assertTrue((bool) $alive->fresh()->orders_blocked);
    }

    public function test_restaurant_without_valid_subscription_is_still_blocked(): void
    {
        $plan = Plan::factory()->create();
        $restaurant = Restaurant::factory()->create(['orders_blocked' => false]);

        $this->subscription($restaurant, $plan, now()->subDays(1));

        (new ProcessSubscriptionExpiration())->handle();

        $this->assertTrue((bool) $restaurant->fresh()->orders_blocked);
    }
}
