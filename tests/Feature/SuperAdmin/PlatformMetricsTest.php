<?php

namespace Tests\Feature\SuperAdmin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\RestaurantStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\User;
use App\Services\PlatformMetrics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Définitions des indicateurs du back-office (PlatformMetrics) : GMV, MRR,
 * compteurs de restaurants, activation exclusive d'un abonnement.
 */
class PlatformMetricsTest extends TestCase
{
    use RefreshDatabase;

    private PlatformMetrics $metrics;
    private Plan $plan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->metrics = app(PlatformMetrics::class);
        $this->plan = Plan::factory()->create();
    }

    private function order(Restaurant $restaurant, array $attributes): Order
    {
        return Order::factory()->create(array_merge(['restaurant_id' => $restaurant->id], $attributes));
    }

    private function paidSub(Restaurant $restaurant, array $attributes = []): Subscription
    {
        return Subscription::factory()->create(array_merge([
            'restaurant_id' => $restaurant->id,
            'plan_id' => $this->plan->id,
            'status' => SubscriptionStatus::ACTIVE,
            'is_trial' => false,
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
            'amount_paid' => 15000,
            'payment_reference' => 'REF-' . uniqid(),
            'paid_at' => now(),
        ], $attributes));
    }

    public function test_gmv_excludes_unpaid_cancelled_and_demo_orders(): void
    {
        $real = Restaurant::factory()->create();
        $demo = Restaurant::factory()->create(['is_demo' => true]);

        // Compte : payée et terminée
        $this->order($real, ['status' => OrderStatus::COMPLETED, 'payment_status' => PaymentStatus::COMPLETED, 'total' => 10000, 'paid_at' => now()]);
        // Exclues du CA : annulée après paiement, attente de paiement, brouillon, démo
        $this->order($real, ['status' => OrderStatus::CANCELLED, 'payment_status' => PaymentStatus::COMPLETED, 'total' => 5000, 'paid_at' => now()]);
        $this->order($real, ['status' => OrderStatus::PENDING_PAYMENT, 'payment_status' => PaymentStatus::PENDING, 'total' => 3000]);
        $this->order($real, ['status' => OrderStatus::DRAFT, 'payment_status' => PaymentStatus::PENDING, 'total' => 2000]);
        $this->order($demo, ['status' => OrderStatus::COMPLETED, 'payment_status' => PaymentStatus::COMPLETED, 'total' => 50000, 'paid_at' => now()]);

        $this->assertSame(10000, $this->metrics->gmv());
        $this->assertSame(10000, $this->metrics->gmv(now()->startOfDay()));
        // Hors période (paid_at hier exclu d'une borne « aujourd'hui »)
        $this->order($real, ['status' => OrderStatus::COMPLETED, 'payment_status' => PaymentStatus::COMPLETED, 'total' => 7000, 'paid_at' => now()->subDays(2)]);
        $this->assertSame(10000, $this->metrics->gmv(now()->startOfDay()));

        // Volume de commandes : brouillon / attente de paiement / démo exclus, annulée incluse
        $this->assertSame(3, $this->metrics->placedOrders()->count());
    }

    public function test_mrr_uses_latest_valid_paid_subscription_without_duplicates(): void
    {
        $a = Restaurant::factory()->create();
        $b = Restaurant::factory()->create();
        $demo = Restaurant::factory()->create(['is_demo' => true]);
        $deleted = Restaurant::factory()->create();

        // Doublon : ancien abonnement encore « active » + renouvellement → seul le dernier compte
        $this->paidSub($a, ['starts_at' => now()->subDays(20), 'ends_at' => now()->addDays(10), 'amount_paid' => 9000]);
        $this->paidSub($a, ['amount_paid' => 15000]);
        // Annuel ramené à 30 jours : 120000 × 30 / 365
        $this->paidSub($b, ['ends_at' => now()->addDays(365), 'amount_paid' => 120000]);
        // Exclus : démo, supprimé, essai, prolongation gratuite, échu
        $this->paidSub($demo, ['amount_paid' => 99000]);
        $this->paidSub($deleted, ['amount_paid' => 99000]);
        $deleted->delete();
        $c = Restaurant::factory()->create();
        $this->paidSub($c, ['status' => SubscriptionStatus::TRIAL, 'is_trial' => true, 'amount_paid' => 0]);
        $this->paidSub($c, ['status' => SubscriptionStatus::ACTIVE, 'amount_paid' => 0, 'payment_reference' => null]);
        $this->paidSub(Restaurant::factory()->create(), ['starts_at' => now()->subDays(40), 'ends_at' => now()->subDays(10)]);

        $this->assertSame((int) round(15000 + 120000 * 30 / 365), $this->metrics->mrr());
    }

    public function test_restaurant_counters_are_distinct_and_based_on_valid_subscriptions(): void
    {
        $paying = Restaurant::factory()->create();
        $this->paidSub($paying);
        $this->paidSub($paying); // doublon : ne doit compter qu'une fois

        $trial = Restaurant::factory()->create();
        $this->paidSub($trial, ['status' => SubscriptionStatus::TRIAL, 'is_trial' => true, 'amount_paid' => 0]);

        // Essai terminé mais restaurant resté « active » : n'est PAS actif
        $expiredTrial = Restaurant::factory()->create(['status' => RestaurantStatus::ACTIVE]);
        $this->paidSub($expiredTrial, ['status' => SubscriptionStatus::TRIAL, 'is_trial' => true, 'amount_paid' => 0,
            'starts_at' => now()->subDays(20), 'ends_at' => now()->subDays(6)]);

        // Restaurant « active » sans aucun abonnement : pas actif
        Restaurant::factory()->create(['status' => RestaurantStatus::ACTIVE]);

        Restaurant::factory()->create(['status' => RestaurantStatus::PENDING]);

        // Exclus du périmètre : démo et supprimé (même avec un abonnement valide)
        $demo = Restaurant::factory()->create(['is_demo' => true]);
        $this->paidSub($demo);
        $deleted = Restaurant::factory()->create();
        $this->paidSub($deleted);
        $deleted->delete();

        $counts = $this->metrics->restaurantSubscriptionCounts();

        $this->assertSame(5, $counts['total']);
        $this->assertSame(1, $counts['paying']);
        $this->assertSame(1, $counts['trial']);
        $this->assertSame(1, $counts['pending']);
        $this->assertSame(2, $counts['expired']);
        $this->assertSame(2, $this->metrics->activeRestaurantsCount());

        // Le Dashboard affiche bien ces « Restaurants actifs »
        $admin = User::factory()->superAdmin()->create(['admin_permissions' => null]);
        $this->actingAs($admin)->get(route('super-admin.dashboard'))
            ->assertOk()
            ->assertViewHas('stats', fn ($stats) => $stats['restaurants']['active'] === 2
                && $stats['restaurants']['paying'] === 1
                && $stats['restaurants']['trial'] === 1);
    }

    public function test_collected_subscription_revenue_ignores_pending_and_free_subscriptions(): void
    {
        $r = Restaurant::factory()->create();
        $this->paidSub($r, ['amount_paid' => 15000]);
        // Expiré mais payé : compte dans l'encaissé
        $this->paidSub($r, ['status' => SubscriptionStatus::EXPIRED, 'amount_paid' => 10000,
            'starts_at' => now()->subDays(60), 'ends_at' => now()->subDays(30), 'paid_at' => now()->subDays(60)]);
        // Paiement abandonné (PENDING) et prolongation gratuite : exclus
        $this->paidSub($r, ['status' => SubscriptionStatus::PENDING, 'amount_paid' => 50000, 'paid_at' => null]);
        $this->paidSub($r, ['amount_paid' => 0, 'payment_reference' => null, 'paid_at' => null]);

        $this->assertSame(25000, $this->metrics->subscriptionRevenue());
        $this->assertSame(15000, $this->metrics->subscriptionRevenue(now()->subDays(7), now()));
    }

    public function test_expiring_soon_uses_latest_subscription_and_includes_trials(): void
    {
        // A renouvelé : l'ancien abonnement finissant dans 3 jours ne doit pas apparaître
        $renewed = Restaurant::factory()->create();
        $this->paidSub($renewed, ['ends_at' => now()->addDays(3)]);
        $this->paidSub($renewed, ['ends_at' => now()->addDays(40)]);

        $trial = Restaurant::factory()->create();
        $trialSub = $this->paidSub($trial, ['status' => SubscriptionStatus::TRIAL, 'is_trial' => true, 'amount_paid' => 0, 'ends_at' => now()->addDays(2)]);

        $expiring = $this->metrics->expiringSubscriptions(7);

        $this->assertCount(1, $expiring);
        $this->assertSame($trialSub->id, $expiring->first()->id);
    }

    public function test_activate_exclusively_closes_other_subscriptions_and_unblocks_restaurant(): void
    {
        $restaurant = Restaurant::factory()->create(['status' => RestaurantStatus::EXPIRED, 'orders_blocked' => true]);
        $old = $this->paidSub($restaurant, ['starts_at' => now()->subDays(25), 'ends_at' => now()->addDays(5)]);
        $trial = $this->paidSub($restaurant, ['status' => SubscriptionStatus::TRIAL, 'is_trial' => true, 'amount_paid' => 0]);
        $new = $this->paidSub($restaurant, ['status' => SubscriptionStatus::PENDING, 'paid_at' => null, 'ends_at' => now()->addDays(30)]);

        $new->activateExclusively(['status' => SubscriptionStatus::ACTIVE, 'is_trial' => false, 'paid_at' => now()]);

        $this->assertSame(SubscriptionStatus::ACTIVE, $new->fresh()->status);
        $this->assertNotNull($new->fresh()->paid_at);
        foreach ([$old, $trial] as $other) {
            $other = $other->fresh();
            $this->assertSame(SubscriptionStatus::EXPIRED, $other->status);
            $this->assertNotNull($other->expired_notification_sent_at);
        }

        $restaurant = $restaurant->fresh();
        $this->assertSame(RestaurantStatus::ACTIVE, $restaurant->status);
        $this->assertFalse($restaurant->orders_blocked);
        $this->assertSame($this->plan->id, $restaurant->current_plan_id);

        // Plus qu'un seul abonnement valide → MRR = ce seul abonnement
        $this->assertSame(15000, $this->metrics->mrr());
    }

    public function test_activate_exclusively_does_not_lift_an_admin_suspension(): void
    {
        $restaurant = Restaurant::factory()->create(['status' => RestaurantStatus::SUSPENDED, 'orders_blocked' => true]);
        $sub = $this->paidSub($restaurant, ['status' => SubscriptionStatus::PENDING]);

        $sub->activateExclusively(['paid_at' => now()]);

        $this->assertSame(SubscriptionStatus::ACTIVE, $sub->fresh()->status);
        $this->assertSame(RestaurantStatus::SUSPENDED, $restaurant->fresh()->status);
        $this->assertTrue($restaurant->fresh()->orders_blocked);
    }

    public function test_extend_subscription_by_admin_is_exclusive(): void
    {
        $admin = User::factory()->superAdmin()->create(['admin_permissions' => null]);
        $restaurant = Restaurant::factory()->create(['status' => RestaurantStatus::EXPIRED, 'orders_blocked' => true]);
        $old = $this->paidSub($restaurant, ['ends_at' => now()->addDays(2)]);

        $this->actingAs($admin)
            ->post(route('super-admin.restaurants.extend-subscription', $restaurant), [
                'plan_id' => $this->plan->id, 'days' => 30, 'reason' => 'Geste commercial',
            ])
            ->assertRedirect();

        $this->assertSame(SubscriptionStatus::EXPIRED, $old->fresh()->status);
        $this->assertSame(1, Subscription::withoutGlobalScopes()->where('restaurant_id', $restaurant->id)
            ->where('status', SubscriptionStatus::ACTIVE)->count());
        $this->assertSame(RestaurantStatus::ACTIVE, $restaurant->fresh()->status);
        $this->assertFalse($restaurant->fresh()->orders_blocked);
    }

    public function test_super_admin_queries_are_never_scoped_to_a_restaurant(): void
    {
        $r1 = Restaurant::factory()->create();
        $r2 = Restaurant::factory()->create();
        $this->paidSub($r1);
        $this->paidSub($r2);

        $admin = User::factory()->superAdmin()->create(['admin_permissions' => null]);
        $this->actingAs($admin);
        // current_restaurant_id resté en session (ex. après une impersonation)
        session(['current_restaurant_id' => $r1->id]);

        $this->assertSame(2, Subscription::count());
    }
}
