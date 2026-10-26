<?php

namespace Tests\Feature\SuperAdmin;

use App\Enums\RestaurantStatus;
use App\Enums\SubscriptionStatus;
use App\Models\LedgerEntry;
use App\Models\Subscription;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\LedgerService;
use App\Services\PlatformMetrics;
use Tests\Feature\Plans\PricingGridTestCase;

/**
 * Prolongation manuelle par le super-admin avec paiement reçu hors plateforme (journal + MRR)
 * et recalcul des frais Jeko des lignes existantes (ledger:reconstruire --recalculer-frais).
 */
class ManualPaymentAndFeeRecalculationTest extends PricingGridTestCase
{
    private function superAdmin(): User
    {
        return User::factory()->superAdmin()->create();
    }

    // ─── Prolongation manuelle ──────────────────────────────────────────────

    public function test_off_platform_payment_is_recorded_in_ledger_and_mrr(): void
    {
        $stand = $this->subscribedRestaurant('stand', [
            'status' => RestaurantStatus::EXPIRED, 'orders_blocked' => true, 'subscription_ends_at' => now()->subDay(),
        ], ['status' => SubscriptionStatus::EXPIRED, 'ends_at' => now()->subDay(), 'amount_paid' => 0]);

        $this->actingAs($this->superAdmin())
            ->post(route('super-admin.restaurants.extend-subscription', $stand), [
                'plan_id' => $this->plan('stand')->id,
                'days' => 30,
                'reason' => 'Dépôt de Prunel',
                'amount_received' => 5000,
                'received_via' => 'jeko_deposit',
                'payment_reference' => 'DEP-JK-778',
                'paid_on' => now()->subDay()->toDateString(),
            ])
            ->assertSessionHas('success', fn ($m) => str_contains($m, '5 000 F reçus par Dépôt Jeko'));

        $subscription = Subscription::withoutGlobalScopes()->where('restaurant_id', $stand->id)
            ->where('status', SubscriptionStatus::ACTIVE)->firstOrFail();
        $this->assertSame(5000, (int) $subscription->amount_paid);
        $this->assertSame('DEP-JK-778', $subscription->payment_reference);
        $this->assertSame(now()->subDay()->toDateString(), $subscription->paid_at->toDateString());

        $entry = LedgerEntry::where('subscription_id', $subscription->id)->firstOrFail();
        $this->assertSame(LedgerEntry::TYPE_SUBSCRIPTION_STAND, $entry->type);
        $this->assertSame('back_office', $entry->channel);
        $this->assertSame('jeko', $entry->method);
        $this->assertSame(5000, $entry->gross_amount);
        $this->assertSame(0, $entry->gateway_fee); // dépôt hors plateforme : aucun frais
        $this->assertSame(5000, $entry->net_menupro);
        $this->assertSame(LedgerEntry::STATUS_SUCCEEDED, $entry->status);
        $this->assertStringContainsString('hors plateforme', $entry->description);
        $this->assertStringContainsString('DEP-JK-778', $entry->description);
        $this->assertSame(now()->subDay()->toDateString(), $entry->occurred_at->toDateString());

        $this->assertSame(5000, app(PlatformMetrics::class)->mrr());
        $this->assertSame(RestaurantStatus::ACTIVE, $stand->fresh()->status);
    }

    public function test_zero_amount_extension_is_clearly_marked_as_offered(): void
    {
        $restaurant = $this->subscribedRestaurant('essentiel', [], ['amount_paid' => 0]);

        $this->actingAs($this->superAdmin())
            ->post(route('super-admin.restaurants.extend-subscription', $restaurant), [
                'plan_id' => $this->plan('essentiel')->id,
                'days' => 15,
                'reason' => 'Geste commercial : panne réseau',
                'amount_received' => 0,
            ])
            ->assertSessionHas('success', fn ($m) => str_contains($m, 'OFFERT'));

        $subscription = Subscription::withoutGlobalScopes()->where('restaurant_id', $restaurant->id)
            ->where('payment_method', 'admin_manual')->firstOrFail();
        $this->assertTrue($subscription->payment_metadata['offered']);
        $this->assertNull($subscription->paid_at);

        $entry = LedgerEntry::where('subscription_id', $subscription->id)->firstOrFail();
        $this->assertSame(0, $entry->gross_amount);
        $this->assertSame(0, $entry->net_menupro);
        $this->assertSame('Offert — geste commercial', $entry->method_detail);
        $this->assertStringContainsString('OFFERT', $entry->description);
        $this->assertSame(0, app(PlatformMetrics::class)->mrr());
    }

    public function test_received_amount_requires_a_payment_method(): void
    {
        $restaurant = $this->subscribedRestaurant('stand');

        $this->actingAs($this->superAdmin())
            ->post(route('super-admin.restaurants.extend-subscription', $restaurant), [
                'plan_id' => $this->plan('stand')->id, 'days' => 7, 'reason' => 'x', 'amount_received' => 1500,
            ])
            ->assertSessionHasErrors('received_via');

        $this->assertSame(0, Subscription::where('payment_method', 'admin_manual')->count());
    }

    public function test_extension_keeps_the_remaining_days(): void
    {
        $restaurant = $this->subscribedRestaurant('stand', ['subscription_ends_at' => now()->addDays(10)], [
            'ends_at' => now()->addDays(10), 'payment_reference' => 'tx-1',
        ]);

        $this->actingAs($this->superAdmin())
            ->post(route('super-admin.restaurants.extend-subscription', $restaurant), [
                'plan_id' => $this->plan('stand')->id, 'days' => 30, 'reason' => 'Espèces au bureau',
                'amount_received' => 5000, 'received_via' => 'cash',
            ]);

        $this->assertEqualsWithDelta(now()->addDays(40)->getTimestamp(), $restaurant->fresh()->subscription_ends_at->getTimestamp(), 10);
        $this->assertSame('cash', LedgerEntry::where('channel', 'back_office')->value('method'));
    }

    // ─── Recalcul des frais ─────────────────────────────────────────────────

    private function ledgerRow(array $attributes): LedgerEntry
    {
        return LedgerEntry::create(array_merge([
            'idempotency_key' => 'test:' . uniqid('', true),
            'occurred_at' => now()->subDays(3),
            'type' => LedgerEntry::TYPE_ORDER_ONLINE_MENU,
            'direction' => 'in',
            'status' => LedgerEntry::STATUS_SUCCEEDED,
            'channel' => 'public_menu',
            'recorded_via' => 'reconstruction',
            'method' => 'jeko',
            'source_type' => 'order',
            'source_id' => 1,
            'gross_amount' => 10000,
            'gateway_fee' => 150,
            'gateway_fee_rate' => 1.5,
            'menupro_commission' => 1200,
            'restaurant_share' => 8800,
            'driver_share' => 0,
            'net_menupro' => 1050,
            'description' => 'Commande #A1',
        ], $attributes));
    }

    public function test_recalculate_fees_with_current_rates_dry_run_then_for_real(): void
    {
        SystemSetting::set(LedgerService::SETTING_COLLECTION_FEE, '0', 'string');
        SystemSetting::set(LedgerService::SETTING_PAYOUT_FEE, '1.5', 'string');

        $orderPayment = $this->ledgerRow([]);
        $subscriptionPayment = $this->ledgerRow([
            'type' => LedgerEntry::TYPE_SUBSCRIPTION_STAND, 'channel' => 'restaurant_space', 'source_type' => 'subscription',
            'gross_amount' => 5000, 'gateway_fee' => 75, 'menupro_commission' => 5000, 'restaurant_share' => 0, 'net_menupro' => 4925,
            'description' => 'Abonnement Stand — mensuel',
        ]);
        $payout = $this->ledgerRow([
            'type' => LedgerEntry::TYPE_PAYOUT_RESTAURANT, 'direction' => 'out', 'channel' => 'system', 'source_type' => 'order',
            'gross_amount' => 9000, 'gateway_fee' => 135, 'menupro_commission' => 0, 'restaurant_share' => 9000, 'net_menupro' => -135,
        ]);
        $failed = $this->ledgerRow(['status' => LedgerEntry::STATUS_FAILED, 'gateway_fee' => 0, 'net_menupro' => 0]);
        $cash = $this->ledgerRow(['method' => 'cash', 'gateway_fee' => 0, 'gateway_fee_rate' => 0, 'net_menupro' => 1200]);
        $before = LedgerEntry::count();

        $this->artisan('ledger:reconstruire', ['--recalculer-frais' => true, '--dry-run' => true])
            ->expectsOutputToContain('2 ligne(s) seraient recalculées')
            ->assertSuccessful();
        $this->assertSame(150, $orderPayment->fresh()->gateway_fee);

        $this->artisan('ledger:reconstruire', ['--recalculer-frais' => true])
            ->expectsOutputToContain('2 ligne(s) recalculée(s)')
            ->assertSuccessful();

        $orderPayment->refresh();
        $this->assertSame(0, $orderPayment->gateway_fee);
        $this->assertSame(0.0, $orderPayment->gateway_fee_rate);
        $this->assertSame(1200, $orderPayment->net_menupro);
        $this->assertSame(10000, $orderPayment->gross_amount);
        $this->assertSame(8800, $orderPayment->restaurant_share);
        $this->assertSame('Commande #A1', $orderPayment->description);

        $subscriptionPayment->refresh();
        $this->assertSame(0, $subscriptionPayment->gateway_fee);
        $this->assertSame(5000, $subscriptionPayment->net_menupro);

        // Reversements : 1,5 % conservé ; échecs et espèces inchangés ; aucune ligne créée
        $this->assertSame(135, $payout->fresh()->gateway_fee);
        $this->assertSame(-135, $payout->fresh()->net_menupro);
        $this->assertSame(0, $failed->fresh()->net_menupro);
        $this->assertSame(1200, $cash->fresh()->net_menupro);
        $this->assertSame($before, LedgerEntry::count());

        // Idempotent
        $this->artisan('ledger:reconstruire', ['--recalculer-frais' => true])
            ->expectsOutputToContain('0 ligne(s) recalculée(s)')
            ->assertSuccessful();
    }
}
