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

    public function test_dish_can_be_assigned_to_space(): void
    {
        $this->seed(\Database\Seeders\PlanSeeder::class);
        $restaurant = \App\Models\Restaurant::factory()->create();
        $space = \App\Models\RestaurantSpace::factory()->create(['restaurant_id' => $restaurant->id]);
        $dish = \App\Models\Dish::factory()->create([
            'restaurant_id' => $restaurant->id,
            'space_id'      => $space->id,
        ]);
        $this->assertEquals($space->id, $dish->space_id);
        $this->assertEquals(1, \App\Models\Dish::forSpace($space->id)->count());
    }

    public function test_order_scope_for_space_filters_correctly(): void
    {
        $this->seed(\Database\Seeders\PlanSeeder::class);
        $restaurant = \App\Models\Restaurant::factory()->create();
        $space1 = \App\Models\RestaurantSpace::factory()->create(['restaurant_id' => $restaurant->id]);
        $space2 = \App\Models\RestaurantSpace::factory()->create(['restaurant_id' => $restaurant->id]);

        \App\Models\Order::factory()->create(['restaurant_id' => $restaurant->id, 'space_id' => $space1->id]);
        \App\Models\Order::factory()->create(['restaurant_id' => $restaurant->id, 'space_id' => $space2->id]);
        \App\Models\Order::factory()->create(['restaurant_id' => $restaurant->id, 'space_id' => null]);

        $this->assertEquals(1, \App\Models\Order::forSpace($space1->id)->count());
        $this->assertEquals(3, \App\Models\Order::forSpace(null)->count()); // null = pas de filtre
    }
}
