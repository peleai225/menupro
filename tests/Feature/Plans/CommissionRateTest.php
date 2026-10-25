<?php

namespace Tests\Feature\Plans;

use App\Enums\SubscriptionStatus;
use App\Models\Restaurant;
use App\Models\Subscription;

class CommissionRateTest extends PricingGridTestCase
{
    public function test_delivery_commission_is_12_percent_for_stand_and_restaurant(): void
    {
        $this->assertSame(12.0, $this->subscribedRestaurant('stand')->platformCommissionRate());
        $this->assertSame(12.0, $this->subscribedRestaurant('essentiel')->platformCommissionRate());
    }

    public function test_delivery_commission_is_10_percent_for_pro_and_gold(): void
    {
        $this->assertSame(10.0, $this->subscribedRestaurant('pro')->platformCommissionRate());
        $this->assertSame(10.0, $this->subscribedRestaurant('gold')->platformCommissionRate());
    }

    public function test_rate_follows_the_plan_at_order_time(): void
    {
        $restaurant = $this->subscribedRestaurant('essentiel');
        $this->assertSame(12.0, $restaurant->platformCommissionRate());

        // Passage au plan Pro : les commandes suivantes sont à 10 %
        $restaurant->update(['current_plan_id' => $this->plan('pro')->id]);
        $this->assertSame(10.0, Restaurant::find($restaurant->id)->platformCommissionRate());
    }

    public function test_negotiated_rate_set_by_admin_wins(): void
    {
        $restaurant = $this->subscribedRestaurant('pro', ['platform_commission_rate' => 8.00]);

        $this->assertSame(8.0, $restaurant->platformCommissionRate());
    }

    public function test_all_active_plans_include_delivery(): void
    {
        foreach (['stand', 'essentiel', 'pro', 'gold'] as $slug) {
            $this->assertTrue($this->plan($slug)->has_delivery, "Livraison attendue pour {$slug}");
            $this->assertTrue($this->subscribedRestaurant($slug)->hasFeature('delivery'));
        }
    }

    public function test_trial_restaurant_gets_pro_commission(): void
    {
        $restaurant = Restaurant::factory()->create(['current_plan_id' => $this->plan('stand')->id]);
        Subscription::factory()->create([
            'restaurant_id' => $restaurant->id,
            'plan_id' => $this->plan('stand')->id,
            'status' => SubscriptionStatus::TRIAL,
            'is_trial' => true,
            'ends_at' => now()->addDays(20),
            'amount_paid' => 0,
        ]);

        $this->assertSame(10.0, $restaurant->fresh()->platformCommissionRate());
    }
}
