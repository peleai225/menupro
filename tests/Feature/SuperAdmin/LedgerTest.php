<?php

namespace Tests\Feature\SuperAdmin;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Delivery;
use App\Models\DeliveryDriver;
use App\Models\DriverEarning;
use App\Models\LedgerEntry;
use App\Models\Order;
use App\Models\OrderRefund;
use App\Models\PayoutTransaction;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\RestaurantWallet;
use App\Models\Subscription;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\LedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Journal des transactions : alimentation automatique par type, frais Jeko,
 * idempotence, reconstitution, page super-admin (accès, filtres, export CSV).
 */
class LedgerTest extends TestCase
{
    use RefreshDatabase;

    private Restaurant $restaurant;

    protected function setUp(): void
    {
        parent::setUp();
        $plan = Plan::where('slug', 'pro')->first() ?? Plan::factory()->create(['slug' => 'pro']);
        $plan->update(['name' => 'Pro']);
        $this->restaurant = Restaurant::factory()->create(['name' => 'Chez Awa', 'current_plan_id' => $plan->id]);
    }

    private function order(array $attributes = [], ?Restaurant $restaurant = null): Order
    {
        return Order::factory()->create(array_merge([
            'restaurant_id' => ($restaurant ?? $this->restaurant)->id,
            'reference' => 'CMD-' . uniqid(),
            'customer_name' => 'Kouamé Yao',
            'customer_phone' => '+225 07 01 02 03 04',
            'status' => OrderStatus::PENDING_PAYMENT,
            'subtotal' => 10000,
            'total' => 10000,
        ], $attributes));
    }

    private function entry(string $key): ?LedgerEntry
    {
        return LedgerEntry::where('idempotency_key', $key)->first();
    }

    // =========================================================================
    // ALIMENTATION PAR TYPE
    // =========================================================================

    public function test_jeko_payment_of_public_menu_order_is_recorded_with_default_fee(): void
    {
        $order = $this->order();
        $order->markAsPaid(['method' => 'jeko', 'reference' => 'JEKO-TX-123'], announce: false);

        $e = $this->entry(LedgerService::orderPaymentKey($order->id));
        $this->assertNotNull($e);
        $this->assertSame(LedgerEntry::TYPE_ORDER_ONLINE_MENU, $e->type);
        $this->assertSame('public_menu', $e->channel);
        $this->assertSame('jeko', $e->method);
        $this->assertSame('JEKO-TX-123', $e->gateway_reference);
        $this->assertSame('Kouamé Yao', $e->payer_name);
        $this->assertSame('+225 07 01 02 03 04', $e->payer_phone);
        $this->assertSame('Chez Awa', $e->restaurant_name);
        $this->assertSame('Pro', $e->restaurant_plan_label);
        $this->assertSame(10000, $e->gross_amount);
        $this->assertSame(150, $e->gateway_fee); // 1,5 %
        $this->assertSame(1.5, $e->gateway_fee_rate);
        $this->assertSame(10000, $e->restaurant_share);
        $this->assertSame(-150, $e->net_menupro);
        $this->assertSame(LedgerEntry::STATUS_SUCCEEDED, $e->status);
    }

    public function test_client_app_delivery_order_splits_commission_driver_and_restaurant(): void
    {
        $order = $this->order([
            'source' => 'platform_web',
            'type' => OrderType::DELIVERY,
            'subtotal' => 9000,
            'delivery_fee' => 1000,
            'total' => 10000,
            'platform_commission' => 1080,
        ]);
        $order->markAsPaid(['method' => 'jeko', 'reference' => 'TX-APP'], announce: false);

        $e = $this->entry(LedgerService::orderPaymentKey($order->id));
        $this->assertSame(LedgerEntry::TYPE_ORDER_APP_DELIVERY, $e->type);
        $this->assertSame('client_app', $e->channel);
        $this->assertSame(1080, $e->menupro_commission);
        $this->assertSame(1000, $e->driver_share);
        $this->assertSame(7920, $e->restaurant_share); // 10000 - 1080 - 1000
        $this->assertSame(150, $e->gateway_fee);
        $this->assertSame(930, $e->net_menupro);       // 1080 - 150
    }

    public function test_cash_pos_order_has_no_fee(): void
    {
        $order = $this->order([
            'reference' => 'POS-261025-ABCD',
            'status' => OrderStatus::CONFIRMED,
            'payment_method' => 'cash_on_delivery',
            'payment_status' => PaymentStatus::COMPLETED,
            'paid_at' => now(),
        ]);

        $e = $this->entry(LedgerService::orderPaymentKey($order->id));
        $this->assertSame(LedgerEntry::TYPE_ORDER_POS, $e->type);
        $this->assertSame('pos', $e->channel);
        $this->assertSame('cash', $e->method);
        $this->assertSame(0, $e->gateway_fee);
        $this->assertSame(0, $e->net_menupro);
    }

    public function test_completed_cash_order_without_explicit_payment_is_recorded(): void
    {
        $order = $this->order(['payment_method' => 'cash_on_delivery', 'status' => OrderStatus::READY]);
        $this->assertNull($this->entry(LedgerService::orderPaymentKey($order->id)));

        $order->update(['status' => OrderStatus::COMPLETED]);

        $this->assertSame('cash', $this->entry(LedgerService::orderPaymentKey($order->id))->method);
    }

    public function test_stand_order_and_stand_subscription_types(): void
    {
        $standPlan = Plan::where('slug', 'stand')->first() ?? Plan::factory()->create(['slug' => 'stand', 'name' => 'Stand']);
        $stand = Restaurant::factory()->create(['current_plan_id' => $standPlan->id]);

        $order = $this->order([], $stand);
        $order->markAsPaid(['method' => 'jeko', 'reference' => 'TX-S'], announce: false);
        $this->assertSame(LedgerEntry::TYPE_ORDER_STAND, $this->entry(LedgerService::orderPaymentKey($order->id))->type);

        $sub = Subscription::factory()->create([
            'restaurant_id' => $stand->id,
            'plan_id' => $standPlan->id,
            'status' => SubscriptionStatus::PENDING,
            'amount_paid' => 5000,
        ]);
        $sub->activateExclusively(['payment_method' => 'jeko', 'payment_reference' => 'TX-SUB-STAND', 'paid_at' => now(), 'is_trial' => false]);

        $e = $this->entry("subscription:{$sub->id}:payment");
        $this->assertSame(LedgerEntry::TYPE_SUBSCRIPTION_STAND, $e->type);
    }

    public function test_restaurant_subscription_payment_is_recorded(): void
    {
        $sub = Subscription::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'plan_id' => $this->restaurant->current_plan_id,
            'status' => SubscriptionStatus::PENDING,
            'amount_paid' => 20000,
            'billing_period' => 'monthly',
        ]);
        $this->assertNull($this->entry("subscription:{$sub->id}:payment"));

        $sub->activateExclusively([
            'status' => SubscriptionStatus::ACTIVE,
            'is_trial' => false,
            'payment_method' => 'jeko',
            'payment_reference' => 'JEKO-SUB-1',
            'paid_at' => now(),
        ]);

        $e = $this->entry("subscription:{$sub->id}:payment");
        $this->assertSame(LedgerEntry::TYPE_SUBSCRIPTION_RESTAURANT, $e->type);
        $this->assertSame('restaurant', $e->payer_type);
        $this->assertSame('Chez Awa', $e->payer_name);
        $this->assertSame('MenuPro', $e->beneficiary_name);
        $this->assertSame('JEKO-SUB-1', $e->gateway_reference);
        $this->assertSame(300, $e->gateway_fee);
        $this->assertSame(20000, $e->menupro_commission);
        $this->assertSame(19700, $e->net_menupro);
    }

    public function test_jeko_payout_is_recorded_and_payment_becomes_paid_out(): void
    {
        $order = $this->order(['platform_commission' => 1000]);
        $order->markAsPaid(['method' => 'jeko', 'reference' => 'TX-P'], announce: false);

        $order->update(['payout_status' => 'completed', 'payout_at' => now(), 'payout_reference' => 'TRANSFER-9']);

        $payout = $this->entry(LedgerService::orderPayoutKey($order->id));
        $this->assertSame(LedgerEntry::TYPE_PAYOUT_RESTAURANT, $payout->type);
        $this->assertSame('out', $payout->direction);
        $this->assertSame(9000, $payout->gross_amount);
        $this->assertSame(135, $payout->gateway_fee); // 1,5 % de 9000
        $this->assertSame(-135, $payout->net_menupro);
        $this->assertSame('TRANSFER-9', $payout->gateway_reference);
        $this->assertSame(LedgerEntry::STATUS_PAID_OUT, $this->entry(LedgerService::orderPaymentKey($order->id))->status);
    }

    public function test_failed_payout_and_failed_payment_are_recorded(): void
    {
        $order = $this->order();
        $order->markAsPaid(['method' => 'jeko', 'reference' => 'TX-F'], announce: false);
        $order->update(['payout_status' => 'failed']);
        $this->assertSame(LedgerEntry::STATUS_FAILED, $this->entry(LedgerService::orderPayoutFailedKey($order->id))->status);

        $other = $this->order(['payment_method' => 'jeko', 'payment_reference' => 'LINK-1']);
        $other->update(['payment_status' => PaymentStatus::FAILED]);
        $failed = $this->entry(LedgerService::orderPaymentFailedKey($other->id));
        $this->assertSame(LedgerEntry::STATUS_FAILED, $failed->status);
        $this->assertSame(0, $failed->gateway_fee);
    }

    public function test_refund_is_recorded_and_tracks_status(): void
    {
        $order = $this->order(['platform_commission' => 1000]);
        $order->markAsPaid(['method' => 'jeko', 'reference' => 'TX-R'], announce: false);

        $refund = OrderRefund::create([
            'order_id' => $order->id,
            'amount' => 10000,
            'reason' => 'Refus restaurant',
            'payment_reference' => 'TX-R',
            'status' => 'pending',
        ]);

        $e = $this->entry('refund:' . $refund->id);
        $this->assertSame(LedgerEntry::TYPE_REFUND_CUSTOMER, $e->type);
        $this->assertSame(LedgerEntry::STATUS_PENDING, $e->status);
        $this->assertSame(150, $e->gateway_fee);
        $this->assertSame(-1000, $e->menupro_commission);

        $refund->update(['status' => 'completed']);

        $this->assertSame(LedgerEntry::STATUS_SUCCEEDED, $e->fresh()->status);
        $this->assertSame(LedgerEntry::STATUS_REFUNDED, $this->entry(LedgerService::orderPaymentKey($order->id))->status);
    }

    public function test_driver_commission_and_wallet_payout_are_recorded(): void
    {
        $order = $this->order(['source' => 'platform_web', 'type' => OrderType::DELIVERY, 'delivery_fee' => 1000]);
        $driver = DeliveryDriver::factory()->create();
        $delivery = Delivery::factory()->create(['order_id' => $order->id, 'restaurant_id' => $this->restaurant->id]);

        $earning = DriverEarning::create([
            'driver_id' => $driver->id,
            'order_id' => $order->id,
            'delivery_id' => $delivery->id,
            'gross_amount' => 1000,
            'platform_cut' => 200,
            'net_amount' => 800,
            'status' => 'available',
        ]);
        $e = $this->entry('driver_earning:' . $earning->id);
        $this->assertSame(LedgerEntry::TYPE_DRIVER_COMMISSION, $e->type);
        $this->assertSame(200, $e->menupro_commission);
        $this->assertSame(800, $e->driver_share);

        $wallet = RestaurantWallet::create(['restaurant_id' => $this->restaurant->id, 'balance' => 50000]);
        $payout = PayoutTransaction::create([
            'restaurant_id' => $this->restaurant->id,
            'restaurant_wallet_id' => $wallet->id,
            'gateway' => 'wave',
            'amount' => 20000,
            'mobile' => '+2250700000000',
            'recipient_name' => 'Awa Koné',
            'idempotency_key' => 'AUTO-PAYOUT-TEST-1',
            'client_reference' => 'AUTO-PAYOUT-TEST-1',
            'status' => 'pending',
        ]);
        $this->assertNull($this->entry('payout_tx:' . $payout->id));

        $payout->update(['status' => 'succeeded', 'gateway_transaction_id' => 'WAVE-PO-1', 'fee' => 200]);
        $p = $this->entry('payout_tx:' . $payout->id);
        $this->assertSame('wave', $p->method);
        $this->assertSame(200, $p->gateway_fee);
        $this->assertSame('WAVE-PO-1', $p->gateway_reference);
    }

    // =========================================================================
    // FRAIS, IDEMPOTENCE, ROBUSTESSE
    // =========================================================================

    public function test_fee_rates_come_from_settings(): void
    {
        SystemSetting::set(LedgerService::SETTING_COLLECTION_FEE, '2', 'string');
        SystemSetting::set(LedgerService::SETTING_PAYOUT_FEE, '0', 'string');

        $order = $this->order();
        $order->markAsPaid(['method' => 'jeko', 'reference' => 'TX-2'], announce: false);
        $order->update(['payout_status' => 'completed', 'payout_at' => now()]);

        $this->assertSame(200, $this->entry(LedgerService::orderPaymentKey($order->id))->gateway_fee);
        $this->assertSame(0, $this->entry(LedgerService::orderPayoutKey($order->id))->gateway_fee);

        // Wave n'a pas de frais Jeko
        $wave = $this->order();
        $wave->markAsPaid(['method' => 'wave', 'reference' => 'W-1'], announce: false);
        $this->assertSame(0, $this->entry(LedgerService::orderPaymentKey($wave->id))->gateway_fee);
    }

    public function test_recording_is_idempotent(): void
    {
        $order = $this->order();
        $order->markAsPaid(['method' => 'jeko', 'reference' => 'TX-I'], announce: false);
        // Second passage (ex. webhook rejoué) : pas de doublon
        $order->update(['payment_status' => PaymentStatus::PENDING]);
        $order->update(['payment_status' => PaymentStatus::COMPLETED]);

        $ledger = app(LedgerService::class);
        $ledger->write($ledger->orderPaymentEntry($order->fresh()));

        $this->assertSame(1, LedgerEntry::where('idempotency_key', LedgerService::orderPaymentKey($order->id))->count());
    }

    public function test_ledger_failure_never_blocks_payment(): void
    {
        Schema::drop('ledger_entries');

        $order = $this->order();
        $this->assertTrue($order->markAsPaid(['method' => 'jeko', 'reference' => 'TX-X'], announce: false));
        $this->assertSame(PaymentStatus::COMPLETED, $order->fresh()->payment_status);
    }

    // =========================================================================
    // RECONSTITUTION
    // =========================================================================

    public function test_rebuild_dry_run_writes_nothing_then_rebuild_is_idempotent(): void
    {
        $order = $this->order(['platform_commission' => 500]);
        $order->markAsPaid(['method' => 'jeko', 'reference' => 'TX-RB'], announce: false);
        $order->update(['payout_status' => 'completed', 'payout_at' => now()]);
        Subscription::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'plan_id' => $this->restaurant->current_plan_id,
            'status' => SubscriptionStatus::ACTIVE,
            'is_trial' => false,
            'amount_paid' => 15000,
            'payment_reference' => 'REF-SUB',
            'paid_at' => now(),
        ]);

        LedgerEntry::query()->delete();

        $this->artisan('ledger:reconstruire', ['--dry-run' => true])->assertSuccessful();
        $this->assertSame(0, LedgerEntry::count());

        $this->artisan('ledger:reconstruire')->assertSuccessful();
        $this->assertSame(3, LedgerEntry::count()); // encaissement + reversement + abonnement
        $this->assertSame(LedgerEntry::STATUS_PAID_OUT, $this->entry(LedgerService::orderPaymentKey($order->id))->status);
        $this->assertSame('reconstruction', $this->entry(LedgerService::orderPaymentKey($order->id))->recorded_via);

        $this->artisan('ledger:reconstruire')->assertSuccessful();
        $this->assertSame(3, LedgerEntry::count());
    }

    public function test_rebuild_since_option_limits_history(): void
    {
        $old = $this->order();
        $old->markAsPaid(['method' => 'jeko', 'reference' => 'TX-OLD'], announce: false);
        Order::withoutGlobalScope('restaurant')->whereKey($old->id)
            ->update(['paid_at' => now()->subMonths(3), 'updated_at' => now()->subMonths(3)]);
        $recent = $this->order();
        $recent->markAsPaid(['method' => 'jeko', 'reference' => 'TX-NEW'], announce: false);

        LedgerEntry::query()->delete();

        $this->artisan('ledger:reconstruire', ['--depuis' => now()->subWeek()->format('Y-m-d')])->assertSuccessful();

        $this->assertNull($this->entry(LedgerService::orderPaymentKey($old->id)));
        $this->assertNotNull($this->entry(LedgerService::orderPaymentKey($recent->id)));
    }

    // =========================================================================
    // PAGE SUPER-ADMIN
    // =========================================================================

    public function test_page_is_reserved_to_finance_admins(): void
    {
        $order = $this->order();
        $order->markAsPaid(['method' => 'jeko', 'reference' => 'JEKO-VISIBLE'], announce: false);
        $entry = $this->entry(LedgerService::orderPaymentKey($order->id));

        $full = User::factory()->superAdmin()->create(['admin_permissions' => null]);
        $this->actingAs($full)->get(route('super-admin.finances.ledger.index'))
            ->assertOk()
            ->assertSee('Journal des transactions')
            ->assertSee('JEKO-VISIBLE');
        $this->actingAs($full)->get(route('super-admin.finances.ledger.show', $entry))
            ->assertOk()
            ->assertSee('Chaîne complète');

        $financeEmployee = User::factory()->superAdmin()->create(['admin_permissions' => ['finance']]);
        $this->actingAs($financeEmployee)->get(route('super-admin.finances.ledger.index'))->assertOk();

        $otherEmployee = User::factory()->superAdmin()->create(['admin_permissions' => ['restaurants']]);
        $this->actingAs($otherEmployee)->get(route('super-admin.finances.ledger.index'))->assertForbidden();
        $this->actingAs($otherEmployee)->get(route('super-admin.finances.ledger.export'))->assertForbidden();

        $owner = User::factory()->restaurantAdmin($this->restaurant)->create();
        $this->actingAs($owner)->get(route('super-admin.finances.ledger.index'))->assertStatus(403);
    }

    public function test_filters_search_and_demo_exclusion(): void
    {
        $demo = Restaurant::factory()->create(['is_demo' => true, 'name' => 'Resto Démo']);
        $a = $this->order(['customer_phone' => '+225 05 11 22 33 44']);
        $a->markAsPaid(['method' => 'jeko', 'reference' => 'JEKO-AAA'], announce: false);
        $b = $this->order(['payment_method' => 'cash_on_delivery', 'payment_status' => PaymentStatus::COMPLETED, 'paid_at' => now()]);
        $d = $this->order([], $demo);
        $d->markAsPaid(['method' => 'jeko', 'reference' => 'JEKO-DEMO'], announce: false);

        $admin = User::factory()->superAdmin()->create(['admin_permissions' => null]);
        $url = fn (array $p) => route('super-admin.finances.ledger.index', $p);

        $this->actingAs($admin)->get($url([]))->assertSee('JEKO-AAA')->assertDontSee('JEKO-DEMO');
        $this->actingAs($admin)->get($url(['demo' => 1]))->assertSee('JEKO-DEMO');
        $this->actingAs($admin)->get($url(['moyen' => 'cash']))->assertSee($b->reference)->assertDontSee('JEKO-AAA');
        $this->actingAs($admin)->get($url(['q' => 'JEKO-AAA']))->assertSee('JEKO-AAA')->assertDontSee($b->reference);
        $this->actingAs($admin)->get($url(['q' => '0511223344']))->assertSee('JEKO-AAA')->assertDontSee($b->reference);
        $this->actingAs($admin)->get($url(['q' => $b->reference]))->assertSee($b->reference)->assertDontSee('JEKO-AAA');
        $this->actingAs($admin)->get($url(['type' => LedgerEntry::FILTER_JEKO_FEES]))->assertSee('JEKO-AAA')->assertDontSee($b->reference);
        $this->actingAs($admin)->get($url(['type' => LedgerEntry::TYPE_SUBSCRIPTION_RESTAURANT]))->assertDontSee('JEKO-AAA');
        $this->actingAs($admin)->get($url(['du' => now()->subMonths(2)->format('Y-m-d'), 'au' => now()->subMonth()->format('Y-m-d')]))
            ->assertDontSee('JEKO-AAA');
    }

    public function test_csv_export_is_excel_friendly(): void
    {
        $order = $this->order();
        $order->markAsPaid(['method' => 'jeko', 'reference' => 'JEKO-CSV'], announce: false);

        $admin = User::factory()->superAdmin()->create(['admin_permissions' => null]);
        $response = $this->actingAs($admin)->get(route('super-admin.finances.ledger.export'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $csv = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('"Référence passerelle";', $csv);
        $this->assertStringContainsString('JEKO-CSV', $csv);
        $this->assertStringContainsString(';10000;150;', $csv);
    }

    public function test_fee_settings_can_be_updated_from_the_page(): void
    {
        $admin = User::factory()->superAdmin()->create(['admin_permissions' => ['finance']]);

        $this->actingAs($admin)
            ->post(route('super-admin.finances.ledger.fees'), ['collection_percent' => '1.8', 'payout_percent' => '0'])
            ->assertRedirect();

        $ledger = app(LedgerService::class);
        $this->assertSame(1.8, $ledger->collectionFeePercent());
        $this->assertSame(0.0, $ledger->payoutFeePercent());

        $this->actingAs($admin)
            ->post(route('super-admin.finances.ledger.fees'), ['collection_percent' => '-1', 'payout_percent' => '1.5'])
            ->assertSessionHasErrors('collection_percent');
    }
}
