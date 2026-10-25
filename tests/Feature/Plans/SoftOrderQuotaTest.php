<?php

namespace Tests\Feature\Plans;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Restaurant;
use App\Notifications\OrderQuotaReachedNotification;

/**
 * Quota mensuel « souple » : une commande client n'est JAMAIS bloquée ; le restaurant est
 * prévenu une seule fois par palier (80 % / 100 %) et par mois.
 *
 * Remplace l'ancien comportement (QuotaExceededException levée par Order::boot) : aucun test
 * existant ne vérifiait ce blocage, il n'y avait donc rien à adapter.
 */
class SoftOrderQuotaTest extends PricingGridTestCase
{
    private function restaurantWithQuota(int $quota): Restaurant
    {
        $restaurant = $this->subscribedRestaurant('stand');
        $restaurant->currentPlan->update(['max_orders_per_month' => $quota]);

        return $restaurant->fresh();
    }

    private function paidOrder(Restaurant $restaurant, array $attributes = []): Order
    {
        return Order::factory()->create(array_merge([
            'restaurant_id' => $restaurant->id,
            'status' => OrderStatus::PAID,
        ], $attributes));
    }

    private function quotaNotifications($admin)
    {
        return $admin->notifications()->where('type', OrderQuotaReachedNotification::class)->get();
    }

    public function test_orders_beyond_quota_are_never_blocked_and_each_threshold_is_notified_once(): void
    {
        $restaurant = $this->restaurantWithQuota(5);
        $admin = $this->adminOf($restaurant);

        for ($i = 1; $i <= 3; $i++) {
            $this->paidOrder($restaurant);
        }
        $this->assertCount(0, $this->quotaNotifications($admin), '60 % : pas encore de notification');

        $this->paidOrder($restaurant); // 4/5 = 80 %
        $this->assertSame([80], $this->quotaNotifications($admin)->pluck('data.threshold')->all());

        $this->paidOrder($restaurant); // 5/5 = 100 %
        // Bien au-delà du quota : les commandes sont créées normalement, sans nouvelle notification
        for ($i = 0; $i < 4; $i++) {
            $this->paidOrder($restaurant);
        }

        $this->assertSame(9, Order::where('restaurant_id', $restaurant->id)->count());
        $thresholds = $this->quotaNotifications($admin)->pluck('data.threshold')->sort()->values()->all();
        $this->assertSame([80, 100], $thresholds, 'Une seule notification par palier');
        $this->assertDatabaseCount('order_quota_alerts', 2);
    }

    public function test_unpaid_cancelled_and_pos_orders_do_not_count(): void
    {
        $restaurant = $this->restaurantWithQuota(2);
        $admin = $this->adminOf($restaurant);

        Order::factory()->create(['restaurant_id' => $restaurant->id, 'status' => OrderStatus::PENDING_PAYMENT]);
        Order::factory()->create(['restaurant_id' => $restaurant->id, 'status' => OrderStatus::DRAFT]);
        Order::factory()->create(['restaurant_id' => $restaurant->id, 'status' => OrderStatus::CANCELLED]);
        $this->paidOrder($restaurant, ['reference' => 'POS-261025-ABCD', 'status' => OrderStatus::CONFIRMED]);
        $this->paidOrder($restaurant, ['reference' => 'POS-261025-EFGH', 'status' => OrderStatus::CONFIRMED]);

        $this->assertCount(0, $this->quotaNotifications($admin));
        $this->assertSame(0, app(\App\Services\PlanLimiter::class)->forRestaurant($restaurant)->getCurrentCount('orders_this_month'));
    }

    public function test_order_counts_once_when_paid_then_accepted(): void
    {
        $restaurant = $this->restaurantWithQuota(10);
        $order = Order::factory()->create(['restaurant_id' => $restaurant->id, 'status' => OrderStatus::PENDING_PAYMENT]);

        $order->update(['status' => OrderStatus::PAID]);
        $order->update(['status' => OrderStatus::CONFIRMED]);

        $this->assertSame(1, app(\App\Services\PlanLimiter::class)->forRestaurant($restaurant)->getCurrentCount('orders_this_month'));
    }

    public function test_unlimited_plans_never_trigger_quota_notifications(): void
    {
        $restaurant = $this->subscribedRestaurant('pro');
        $admin = $this->adminOf($restaurant);

        for ($i = 0; $i < 3; $i++) {
            $this->paidOrder($restaurant);
        }

        $this->assertCount(0, $this->quotaNotifications($admin));
    }

    public function test_dashboard_shows_upgrade_banner_after_threshold(): void
    {
        $restaurant = $this->restaurantWithQuota(2);
        $admin = $this->adminOf($restaurant);
        $this->paidOrder($restaurant);
        $this->paidOrder($restaurant);
        $this->paidOrder($restaurant);

        // Le rendu complet du tableau de bord utilise des fonctions SQL MySQL (HOUR) absentes de
        // SQLite : on vérifie la donnée du bandeau (propriété calculée orderQuota).
        $this->actingAs($admin);
        $quota = (new \App\Livewire\Restaurant\Dashboard())->orderQuota();

        $this->assertSame(100, $quota['threshold']);
        $this->assertSame(3, $quota['current']);
        $this->assertSame(2, $quota['limit']);
    }

    public function test_dashboard_has_no_banner_below_threshold(): void
    {
        $restaurant = $this->restaurantWithQuota(10);
        $admin = $this->adminOf($restaurant);
        $this->paidOrder($restaurant);

        $this->actingAs($admin);
        $this->assertNull((new \App\Livewire\Restaurant\Dashboard())->orderQuota());
    }
}
