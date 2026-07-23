<?php

namespace Tests\Feature\Restaurant;

use Tests\TestCase;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SpacesTest extends TestCase
{
    use RefreshDatabase;

    public function test_gold_plan_has_multi_spaces_feature(): void
    {
        $this->seed(\Database\Seeders\PlanSeeder::class);
        $plan = Plan::where('slug', 'gold')->firstOrFail();
        $this->assertTrue((bool) $plan->has_multi_spaces);
    }

    public function test_non_gold_plans_do_not_have_multi_spaces(): void
    {
        $this->seed(\Database\Seeders\PlanSeeder::class);
        $plans = Plan::where('slug', '!=', 'gold')->get();
        foreach ($plans as $plan) {
            $this->assertFalse((bool) $plan->has_multi_spaces, "Plan {$plan->slug} should not have multi_spaces");
        }
    }

    public function test_restaurant_can_have_spaces(): void
    {
        $this->seed(\Database\Seeders\PlanSeeder::class);
        $restaurant = \App\Models\Restaurant::factory()->create([
            'current_plan_id' => \App\Models\Plan::where('slug', 'gold')->value('id'),
        ]);

        $space = \App\Models\RestaurantSpace::create([
            'restaurant_id' => $restaurant->id,
            'name'          => 'VIP',
            'color'         => '#f59e0b',
            'is_active'     => true,
            'sort_order'    => 1,
        ]);

        $this->assertDatabaseHas('restaurant_spaces', ['name' => 'VIP', 'restaurant_id' => $restaurant->id]);
        $this->assertEquals(1, $restaurant->spaces()->count());
    }

    public function test_restaurant_has_multi_spaces_returns_true_for_gold(): void
    {
        $this->seed(\Database\Seeders\PlanSeeder::class);
        $restaurant = \App\Models\Restaurant::factory()->create([
            'current_plan_id' => \App\Models\Plan::where('slug', 'gold')->value('id'),
        ]);
        $this->assertTrue($restaurant->hasMultiSpaces());
    }
}
