<?php

namespace Tests\Feature\Plans;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;

class PlanGridSeederTest extends PricingGridTestCase
{
    public function test_seeder_applies_the_validated_grid(): void
    {
        $stand = $this->plan('stand');
        $this->assertSame([5000, 1500, 30, 8, 0, 150], [$stand->price, $stand->weekly_price, $stand->max_dishes, $stand->max_categories, $stand->max_employees, $stand->max_orders_per_month]);
        $this->assertTrue($stand->has_delivery);
        $this->assertFalse($stand->has_stock_management);

        $restaurant = $this->plan('essentiel');
        $this->assertSame('Restaurant', $restaurant->name);
        $this->assertSame([15000, 100, 3, 600], [$restaurant->price, $restaurant->max_dishes, $restaurant->max_employees, $restaurant->max_orders_per_month]);
        $this->assertTrue($restaurant->has_stock_management && $restaurant->has_analytics && $restaurant->has_delivery);
        $this->assertFalse($restaurant->has_ingredient_stock);

        $pro = $this->plan('pro');
        $this->assertSame([30000, 10], [$pro->price, $pro->max_employees]);
        $this->assertTrue(Plan::isUnlimitedValue($pro->max_dishes) && Plan::isUnlimitedValue($pro->max_orders_per_month));
        $this->assertTrue($pro->has_ingredient_stock && $pro->has_hotel_rooms && $pro->has_priority_support);
        $this->assertSame(10.0, $pro->commissionRate());

        $gold = $this->plan('gold');
        $this->assertSame(75000, $gold->price);
        $this->assertTrue($gold->is_on_quote);
        $this->assertSame('Sur devis, à partir de 75 000 F', $gold->price_label);
        $this->assertTrue($gold->has_multi_spaces);

        $this->assertFalse($this->plan('business')->is_active);
        $this->assertSame(['stand', 'essentiel', 'pro', 'gold'], Plan::active()->ordered()->pluck('slug')->all());
    }

    public function test_seeder_is_idempotent(): void
    {
        $count = Plan::count();
        $this->seed(\Database\Seeders\PlanSeeder::class);
        $this->assertSame($count, Plan::count());
        $this->assertSame(1, Plan::where('slug', 'pro')->count());
    }

    public function test_apply_grid_command_dry_run_then_apply_without_touching_subscriptions(): void
    {
        // Simule l'état de production avant la nouvelle grille
        $this->plan('pro')->update(['price' => 25000, 'has_hotel_rooms' => false]);
        Plan::factory()->create(['slug' => 'menupro', 'name' => 'MenuPro', 'is_active' => true]);
        $restaurant = $this->subscribedRestaurant('pro', [], ['amount_paid' => 25000]);
        $subscriptionBefore = Subscription::where('restaurant_id', $restaurant->id)->first()->toArray();

        $this->artisan('plans:appliquer-grille', ['--dry-run' => true])
            ->expectsOutputToContain('price: 25000 → 30000')
            ->assertSuccessful();
        $this->assertSame(25000, $this->plan('pro')->price, 'Le dry-run ne modifie rien');

        $this->artisan('plans:appliquer-grille')->assertSuccessful();

        $this->assertSame(30000, $this->plan('pro')->price);
        $this->assertTrue($this->plan('pro')->has_hotel_rooms);
        $this->assertFalse(Plan::where('slug', 'menupro')->value('is_active'));
        $this->assertEquals($subscriptionBefore, Subscription::where('restaurant_id', $restaurant->id)->first()->toArray(), 'Abonnement en cours inchangé');

        $this->artisan('plans:appliquer-grille')
            ->expectsOutputToContain('déjà à jour')
            ->assertSuccessful();
    }

    public function test_super_admin_empty_limit_means_unlimited_and_zero_is_refused(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $plan = $this->plan('essentiel');
        $payload = [
            'name' => 'Restaurant', 'price' => 15000, 'duration_days' => 30,
            'max_dishes' => '', 'max_categories' => 30, 'max_employees' => 3, 'max_orders_per_month' => '',
            'has_delivery' => '1', 'is_active' => '1',
        ];

        $this->actingAs($admin)->put(route('super-admin.plans.update', $plan), $payload)->assertSessionHasNoErrors();
        $plan->refresh();
        $this->assertSame(9999, $plan->max_dishes);
        $this->assertSame(9999, $plan->max_orders_per_month);
        $this->assertFalse($plan->has_stock_management, 'Une case décochée est bien enregistrée à non');

        $this->actingAs($admin)->put(route('super-admin.plans.update', $plan), array_merge($payload, ['max_dishes' => 0]))
            ->assertSessionHasErrors('max_dishes');
        $this->assertSame(9999, $plan->fresh()->max_dishes);
    }
}
