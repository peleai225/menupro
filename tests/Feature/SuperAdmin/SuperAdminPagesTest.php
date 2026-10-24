<?php

namespace Tests\Feature\SuperAdmin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Non-régression : chaque écran GET du back-office super-admin se charge (200) avec
 * des données réalistes (restaurants réels/démo, abonnements, commandes de tous statuts).
 */
class SuperAdminPagesTest extends TestCase
{
    use RefreshDatabase;

    /** Écrans non testés ici : appel réseau sortant vers l'API Jeko. */
    private const SKIPPED = ['super-admin.jeko.test'];

    public function test_every_super_admin_page_loads(): void
    {
        $admin = User::factory()->superAdmin()->create(['admin_permissions' => null]);
        [$restaurant, $subscription, $order] = $this->seedData();

        $params = [
            'super-admin.restaurants.show' => ['restaurant' => $restaurant],
            'super-admin.subscriptions.show' => ['subscription' => $subscription->id],
            'super-admin.orders.show' => ['id' => $order->id],
            'super-admin.utilisateurs.show' => ['user' => $admin->id],
        ];

        $failures = [];
        $checked = 0;

        foreach (Route::getRoutes() as $route) {
            $name = $route->getName();
            if (!$name || !str_starts_with($name, 'super-admin.') || !in_array('GET', $route->methods(), true)
                || in_array($name, self::SKIPPED, true)) {
                continue;
            }

            if (!empty($route->parameterNames()) && !isset($params[$name])) {
                continue; // écran de détail sans donnée de test dédiée
            }

            $urls = [route($name, $params[$name] ?? [])];
            // Les pages de statistiques sont aussi chargées avec chaque période + une valeur invalide
            if (in_array($name, ['super-admin.stats', 'super-admin.stats.revenue', 'super-admin.stats.growth'], true)) {
                foreach (['7', '90', '365', 'abc'] as $period) {
                    $urls[] = route($name, ['period' => $period]);
                }
            }

            foreach ($urls as $url) {
                $checked++;
                $response = $this->actingAs($admin)->get($url);
                if ($response->getStatusCode() !== 200) {
                    $failures[] = "{$name} ({$url}) → HTTP " . $response->getStatusCode() . " "
                        . mb_substr((string) ($response->exception?->getMessage() ?? ''), 0, 200);
                }
            }
        }

        $this->assertGreaterThan(40, $checked);
        $this->assertSame([], $failures, implode("\n", $failures));
    }

    private function seedData(): array
    {
        $plan = Plan::factory()->create();
        $restaurant = Restaurant::factory()->create(['is_on_platform' => true]);
        $demo = Restaurant::factory()->create(['is_demo' => true]);

        $subscription = Subscription::factory()->create([
            'restaurant_id' => $restaurant->id, 'plan_id' => $plan->id,
            'payment_reference' => 'REF-1', 'paid_at' => now(), 'amount_paid' => 15000,
        ]);
        Subscription::factory()->create([
            'restaurant_id' => $restaurant->id, 'plan_id' => $plan->id,
            'status' => SubscriptionStatus::EXPIRED, 'payment_reference' => 'REF-0',
            'starts_at' => now()->subDays(60), 'ends_at' => now()->subDays(30),
        ]);
        Subscription::factory()->create([
            'restaurant_id' => $demo->id, 'plan_id' => $plan->id,
            'status' => SubscriptionStatus::TRIAL, 'is_trial' => true, 'amount_paid' => 0, 'ends_at' => now()->addDays(3),
        ]);

        $order = null;
        foreach (OrderStatus::cases() as $i => $status) {
            $paid = !in_array($status, [OrderStatus::DRAFT, OrderStatus::PENDING_PAYMENT], true);
            $order = Order::factory()->create([
                'restaurant_id' => $i % 3 === 0 ? $demo->id : $restaurant->id,
                'status' => $status,
                'payment_status' => $paid ? PaymentStatus::COMPLETED : PaymentStatus::PENDING,
                'paid_at' => $paid ? now()->subDays($i) : null,
                'platform_commission' => 500,
                'payout_status' => $paid ? 'completed' : null,
                'payout_at' => $paid ? now() : null,
                'created_at' => now()->subDays($i * 3),
            ]);
        }

        return [$restaurant, $subscription, $order];
    }
}
