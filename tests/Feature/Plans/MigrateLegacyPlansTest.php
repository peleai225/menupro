<?php

namespace Tests\Feature\Plans;

use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;

class MigrateLegacyPlansTest extends PricingGridTestCase
{
    private Plan $menupro;

    protected function setUp(): void
    {
        parent::setUp();

        // Ancien plan, inactif mais encore utilisé (situation de production)
        $this->menupro = Plan::factory()->create([
            'name' => 'MenuPro', 'slug' => 'menupro', 'price' => 25000, 'is_active' => false,
            'max_dishes' => 100, 'max_orders_per_month' => 2000,
            'has_delivery' => true, 'has_stock_management' => true,
        ]);
    }

    private function legacyRestaurant(): Restaurant
    {
        $restaurant = Restaurant::factory()->create([
            'current_plan_id' => $this->menupro->id,
            'subscription_ends_at' => now()->addDays(12),
        ]);
        Subscription::factory()->create([
            'restaurant_id' => $restaurant->id,
            'plan_id' => $this->menupro->id,
            'status' => SubscriptionStatus::ACTIVE,
            'starts_at' => now()->subDays(18),
            'ends_at' => now()->addDays(12),
            'amount_paid' => 25000,
        ]);

        return $restaurant;
    }

    public function test_dry_run_lists_without_modifying_anything(): void
    {
        $restaurant = $this->legacyRestaurant();

        $this->artisan('plans:migrer-anciens', ['--dry-run' => true])
            ->expectsOutputToContain($restaurant->name)
            ->assertSuccessful();

        $restaurant->refresh();
        $this->assertSame($this->menupro->id, $restaurant->current_plan_id);
        $this->assertNull($restaurant->locked_price);
        $this->assertSame($this->menupro->id, Subscription::where('restaurant_id', $restaurant->id)->value('plan_id'));
    }

    public function test_migration_moves_to_pro_keeps_subscription_and_locks_price_idempotently(): void
    {
        $restaurant = $this->legacyRestaurant();
        $subscription = Subscription::where('restaurant_id', $restaurant->id)->first();
        $pro = $this->plan('pro');

        $this->artisan('plans:migrer-anciens')->assertSuccessful();

        $restaurant->refresh();
        $subscription->refresh();
        $this->assertSame($pro->id, $restaurant->current_plan_id);
        $this->assertSame(25000, $restaurant->locked_price);
        $this->assertSame($pro->id, $subscription->plan_id);
        $this->assertSame(25000, $subscription->amount_paid, 'Montant de l\'abonnement en cours inchangé');
        $this->assertTrue($subscription->ends_at->isSameDay(now()->addDays(12)), 'Échéance inchangée');

        // Deuxième exécution : rien ne bouge
        $before = [$restaurant->fresh()->toArray(), $subscription->fresh()->toArray()];
        $this->artisan('plans:migrer-anciens')
            ->expectsOutputToContain('0 restaurant(s) migré(s)')
            ->assertSuccessful();
        $this->assertEquals($before, [$restaurant->fresh()->toArray(), $subscription->fresh()->toArray()]);
    }

    public function test_locked_price_is_used_on_renewal(): void
    {
        $restaurant = $this->legacyRestaurant();
        $this->artisan('plans:migrer-anciens')->assertSuccessful();
        $restaurant = Restaurant::find($restaurant->id);
        $pro = $this->plan('pro');

        $this->assertSame(30000, $pro->price);
        $this->assertSame(25000, Subscription::quote($pro, 'monthly', $restaurant)['final_price']);
        $this->assertSame(67500, Subscription::quote($pro, 'quarterly', $restaurant)['final_price']); // 75 000 - 10 %
        $this->assertTrue(Subscription::quote($pro, 'monthly', $restaurant)['locked_price_applied']);

        // Renouvellement effectif
        $renewed = Subscription::where('restaurant_id', $restaurant->id)->first()->renew($pro, ['billing_period' => 'monthly']);
        $this->assertSame(25000, $renewed->amount_paid);

        // Autre plan : tarif public
        $this->assertSame(15000, Subscription::quote($this->plan('essentiel'), 'monthly', $restaurant)['final_price']);
    }

    public function test_locked_price_is_lost_when_no_longer_subscribed(): void
    {
        $restaurant = $this->legacyRestaurant();
        $this->artisan('plans:migrer-anciens')->assertSuccessful();

        Restaurant::where('id', $restaurant->id)->update(['subscription_ends_at' => now()->subDays(30)]);

        $this->assertSame(30000, Subscription::quote($this->plan('pro'), 'monthly', Restaurant::find($restaurant->id))['final_price']);
    }

    public function test_current_pro_subscribers_keep_their_old_price(): void
    {
        $proSubscriber = $this->subscribedRestaurant('pro', [], ['amount_paid' => 25000]);

        $this->artisan('plans:migrer-anciens')->assertSuccessful();
        $this->assertSame(25000, $proSubscriber->fresh()->locked_price);

        $this->artisan('plans:migrer-anciens', ['--sans-pro' => true])->assertSuccessful();
        $other = $this->subscribedRestaurant('pro');
        $this->artisan('plans:migrer-anciens', ['--sans-pro' => true])->assertSuccessful();
        $this->assertNull($other->fresh()->locked_price);
    }
}
