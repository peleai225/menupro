<?php

namespace Tests\Feature\Plans;

use App\Enums\RestaurantStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\User;

class ProTrialTest extends PricingGridTestCase
{
    private function trialRestaurant(string $chosenPlan, ?\Carbon\Carbon $endsAt = null): Restaurant
    {
        $plan = $this->plan($chosenPlan);
        $restaurant = Restaurant::factory()->create([
            'current_plan_id' => $plan->id,
            'subscription_ends_at' => $endsAt ?? now()->addDays(25),
        ]);
        Subscription::factory()->create([
            'restaurant_id' => $restaurant->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::TRIAL,
            'is_trial' => true,
            'trial_days' => config('plans.trial_days'),
            'starts_at' => now()->subDays(5),
            'ends_at' => $endsAt ?? now()->addDays(25),
            'amount_paid' => 0,
        ]);

        return $restaurant->fresh();
    }

    public function test_trial_duration_is_configured_in_one_place(): void
    {
        $this->assertSame(30, config('plans.trial_days'));
    }

    public function test_registration_creates_a_30_day_trial(): void
    {
        $this->post(route('register.post'), [
            'name' => 'Awa',
            'restaurant_name' => 'Stand Awa',
            'restaurant_type' => 'stand',
            'phone' => '0700000002',
            'password' => 'password123',
            'plan' => 'stand',
            'billing_period' => 'weekly',
            'terms' => '1',
        ])->assertRedirect(route('restaurant.pending'));

        $subscription = Subscription::firstOrFail();
        $this->assertSame(30, $subscription->trial_days);
        $this->assertSame('weekly', $subscription->billing_period);
        $this->assertSame($this->plan('stand')->id, $subscription->plan_id, 'Le plan choisi reste celui payé après l\'essai');
    }

    public function test_approval_starts_the_30_day_trial(): void
    {
        $restaurant = Restaurant::factory()->create(['status' => RestaurantStatus::PENDING, 'current_plan_id' => $this->plan('stand')->id]);
        $trial = Subscription::factory()->create([
            'restaurant_id' => $restaurant->id,
            'plan_id' => $this->plan('stand')->id,
            'status' => SubscriptionStatus::TRIAL,
            'is_trial' => true,
            'trial_days' => null,
            'starts_at' => null,
            'ends_at' => null,
        ]);

        $this->actingAs(User::factory()->superAdmin()->create())
            ->post(route('super-admin.restaurants.approve', $restaurant));

        $trial->refresh();
        $this->assertSame(30, (int) round($trial->starts_at->diffInDays($trial->ends_at)));
    }

    public function test_trial_gives_access_to_every_pro_feature_and_limit(): void
    {
        $restaurant = $this->trialRestaurant('stand');

        $this->assertTrue($restaurant->isOnTrial());
        $this->assertSame('pro', $restaurant->effectivePlan()->slug);
        foreach (['delivery', 'stock', 'ingredient_stock', 'analytics', 'hotel_rooms', 'priority_support'] as $feature) {
            $this->assertTrue($restaurant->hasFeature($feature), "Fonction Pro « {$feature} » attendue pendant l'essai");
        }
        $this->assertNull(app(\App\Services\PlanLimiter::class)->forRestaurant($restaurant)->getLimit('dishes'), 'Plats illimités pendant l\'essai');
        $this->assertFalse($restaurant->hasFeature('multi_spaces'), 'Multi-espaces reste réservé à Gold');
    }

    public function test_trial_restaurant_can_open_pro_only_pages(): void
    {
        $restaurant = $this->trialRestaurant('stand');

        $this->actingAs($this->adminOf($restaurant))
            ->get(route('restaurant.stock.ingredients.index'))
            ->assertOk();
    }

    public function test_after_trial_the_chosen_plan_applies(): void
    {
        $restaurant = $this->trialRestaurant('essentiel');
        Subscription::where('restaurant_id', $restaurant->id)->update(['status' => SubscriptionStatus::EXPIRED->value]);
        $restaurant = Restaurant::find($restaurant->id);

        $this->assertFalse($restaurant->isOnTrial());
        $this->assertSame('essentiel', $restaurant->effectivePlan()->slug);
        $this->assertTrue($restaurant->hasFeature('stock'), 'Restaurant : stock simple');
        $this->assertFalse($restaurant->hasFeature('ingredient_stock'), 'Restaurant : pas de stock par ingrédient');
        $this->assertSame(12.0, $restaurant->platformCommissionRate());
    }
}
