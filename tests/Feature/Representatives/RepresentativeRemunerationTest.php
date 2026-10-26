<?php

namespace Tests\Feature\Representatives;

use App\Enums\Crm\AgentStatus;
use App\Enums\Crm\CommissionType;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\RestaurantStatus;
use App\Enums\UserRole;
use App\Models\Crm\Commission;
use App\Models\Crm\RepresentativeProfile;
use App\Models\Crm\RepresentativeStatement;
use App\Models\Crm\Wallet;
use App\Models\LedgerEntry;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\Crm\RepresentativeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Attribution des restaurants (lien de parrainage, ville exclusive) et calcul mensuel
 * de la part des représentants depuis le journal des transactions.
 */
class RepresentativeRemunerationTest extends TestCase
{
    use RefreshDatabase;

    private const MONTH = '2026-09';

    private int $seq = 0;

    private function representative(array $attributes = []): RepresentativeProfile
    {
        $user = User::factory()->create(['agent_status' => AgentStatus::ACTIF]);
        $user->role = UserRole::REPRESENTATIVE;
        $user->save();

        return RepresentativeProfile::create(array_merge([
            'user_id' => $user->id,
            'city' => 'Daloa',
            'subscription_rate' => 20,
            'delivery_rate' => 20,
            'trial_ends_at' => now()->addMonths(3),
            'quarterly_target' => 4,
        ], $attributes));
    }

    private function subscriptionLine(Restaurant $restaurant, int $amount, array $attributes = []): LedgerEntry
    {
        $this->seq++;

        return LedgerEntry::create(array_merge([
            'idempotency_key' => "test:sub:{$this->seq}",
            'occurred_at' => self::MONTH . '-10 12:00:00',
            'type' => LedgerEntry::TYPE_SUBSCRIPTION_RESTAURANT,
            'status' => LedgerEntry::STATUS_SUCCEEDED,
            'restaurant_id' => $restaurant->id,
            'source_type' => 'subscription',
            'source_id' => $this->seq,
            'gross_amount' => $amount,
            'menupro_commission' => $amount,
            'net_menupro' => $amount,
        ], $attributes));
    }

    private function deliveryLine(Restaurant $restaurant, int $commission, bool $delivered = true, array $attributes = []): LedgerEntry
    {
        $this->seq++;
        $order = Order::factory()->create([
            'restaurant_id' => $restaurant->id,
            'reference' => 'CMD-T' . $this->seq,
            'type' => OrderType::DELIVERY,
        ]);
        // Statut forcé sans déclencher les observers (le journal est alimenté à la main ici)
        DB::table('orders')->where('id', $order->id)->update([
            'status' => $delivered ? OrderStatus::COMPLETED->value : OrderStatus::DELIVERING->value,
        ]);

        return LedgerEntry::create(array_merge([
            'idempotency_key' => "test:order:{$this->seq}",
            'occurred_at' => self::MONTH . '-15 20:00:00',
            'type' => LedgerEntry::TYPE_ORDER_APP_DELIVERY,
            'status' => LedgerEntry::STATUS_PAID_OUT,
            'restaurant_id' => $restaurant->id,
            'source_type' => 'order',
            'source_id' => $order->id,
            'order_id' => $order->id,
            'gross_amount' => 12000,
            'menupro_commission' => $commission,
        ], $attributes));
    }

    // =========================================================================
    // ATTRIBUTION
    // =========================================================================

    public function test_restaurant_registered_with_referral_link_is_attributed(): void
    {
        $profile = $this->representative();
        Plan::firstOrCreate(['slug' => 'stand'], ['name' => 'Stand', 'price' => 5000, 'duration_days' => 30, 'is_active' => true]);

        $this->get(route('register', ['ref' => $profile->uuid]))->assertOk()->assertSessionHas('register_ref_agent', $profile->uuid);

        $this->post(route('register.post'), [
            'name' => 'Koné',
            'restaurant_name' => 'Maquis du Rail',
            'restaurant_type' => 'restaurant',
            'phone' => '0700000001',
            'email' => 'rail@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'restaurant_city' => 'Daloa',
            'plan' => 'stand',
            'terms' => '1',
        ])->assertRedirect(route('restaurant.pending'));

        $restaurant = Restaurant::where('name', 'Maquis du Rail')->firstOrFail();
        $this->assertSame($profile->user_id, $restaurant->referred_by_user_id);
        $this->assertNull($restaurant->referred_by_agent_id);

        $attributed = app(RepresentativeService::class)->attributedRestaurants($profile);
        $this->assertSame(['lien'], $attributed->pluck('attribution_source')->all());
    }

    public function test_inactive_representative_link_is_ignored(): void
    {
        $profile = $this->representative();
        $profile->user->update(['agent_status' => AgentStatus::SUSPENDU]);

        $this->get(route('register', ['ref' => $profile->uuid]))->assertOk()->assertSessionMissing('register_ref_agent');
    }

    public function test_restaurants_of_exclusive_city_are_attributed_only_with_exclusivity(): void
    {
        $profile = $this->representative();
        Restaurant::factory()->create(['city' => ' daloa ', 'name' => 'Chez Awa']);
        Restaurant::factory()->create(['city' => 'Bouaké', 'name' => 'Ailleurs']);
        $service = app(RepresentativeService::class);

        $this->assertCount(0, $service->attributedRestaurants($profile));

        $service->updateSettings($profile, ['exclusivity_active' => true]);
        $attributed = $service->attributedRestaurants($profile->fresh());

        $this->assertSame(['Chez Awa'], $attributed->pluck('name')->all());
        $this->assertSame('ville', $attributed->first()->attribution_source);
        $this->assertNotNull($profile->fresh()->exclusivity_granted_at);

        $service->updateSettings($profile->fresh(), ['exclusivity_active' => false]);
        $this->assertCount(0, $service->attributedRestaurants($profile->fresh()));
        $this->assertNull($profile->fresh()->exclusivity_granted_at);
    }

    // =========================================================================
    // CALCUL MENSUEL
    // =========================================================================

    public function test_monthly_share_is_20_percent_of_subscriptions_and_delivery_commission(): void
    {
        $profile = $this->representative();
        $referred = Restaurant::factory()->create(['city' => 'Daloa', 'referred_by_user_id' => $profile->user_id]);
        $other = Restaurant::factory()->create(['city' => 'Daloa']); // non attribué (pas d'exclusivité)
        $demo = Restaurant::factory()->create(['referred_by_user_id' => $profile->user_id, 'is_demo' => true]);

        $this->subscriptionLine($referred, 10000);
        $this->subscriptionLine($referred, 5000);
        $this->subscriptionLine($referred, 7000, ['status' => LedgerEntry::STATUS_REFUNDED]);   // exclu
        $this->subscriptionLine($referred, 9000, ['status' => LedgerEntry::STATUS_FAILED]);     // exclu
        $this->subscriptionLine($referred, 4000, ['occurred_at' => '2026-08-31 23:00:00']);     // autre mois
        $this->subscriptionLine($other, 50000);                                                  // non attribué
        $this->subscriptionLine($demo, 50000, ['is_demo' => true]);                              // démo
        $this->deliveryLine($referred, 2000);
        $this->deliveryLine($referred, 1000, delivered: false);                                 // non livrée
        $this->deliveryLine($referred, 500, true, ['type' => LedgerEntry::TYPE_ORDER_APP_PICKUP]); // pas une livraison

        $this->artisan('representants:calculer-mois', ['mois' => self::MONTH])->assertSuccessful();

        $statement = RepresentativeStatement::where('representative_profile_id', $profile->id)->where('month', self::MONTH)->firstOrFail();
        $this->assertSame(15000, $statement->subscription_collected);
        $this->assertSame(2000, $statement->delivery_commission);
        $this->assertSame(3000, $statement->subscription_share);  // 20 % de 15 000
        $this->assertSame(400, $statement->delivery_share);       // 20 % de 2 000
        $this->assertSame(3400, $statement->total_share);
        $this->assertSame(340000, $statement->credited_cents);
        $this->assertSame(2, $statement->restaurants_attributed);
        $this->assertSame(1, $statement->restaurants_paying);

        $wallet = Wallet::where('user_id', $profile->user_id)->firstOrFail();
        $this->assertSame(340000, $wallet->balance_cents);

        $commission = Commission::where('user_id', $profile->user_id)->firstOrFail();
        $this->assertSame(CommissionType::REPRESENTATIVE_SHARE, $commission->type);
        $this->assertSame(RepresentativeStatement::class, $commission->source_type);
    }

    public function test_custom_rates_are_applied(): void
    {
        $profile = $this->representative(['subscription_rate' => 25, 'delivery_rate' => 10]);
        $r = Restaurant::factory()->create(['referred_by_user_id' => $profile->user_id]);
        $this->subscriptionLine($r, 10000);
        $this->deliveryLine($r, 3000);

        $data = app(RepresentativeService::class)->calculate($profile, self::MONTH);

        $this->assertSame(2500, $data['subscription_share']);
        $this->assertSame(300, $data['delivery_share']);
        $this->assertSame(2800, $data['total_share']);
    }

    public function test_monthly_computation_is_idempotent_and_credits_only_the_difference(): void
    {
        $profile = $this->representative();
        $r = Restaurant::factory()->create(['referred_by_user_id' => $profile->user_id]);
        $this->subscriptionLine($r, 10000);

        $this->artisan('representants:calculer-mois', ['mois' => self::MONTH])->assertSuccessful();
        $this->artisan('representants:calculer-mois', ['mois' => self::MONTH])->assertSuccessful();

        $this->assertSame(1, RepresentativeStatement::count());
        $this->assertSame(1, Commission::where('user_id', $profile->user_id)->count());
        $this->assertSame(200000, Wallet::where('user_id', $profile->user_id)->value('balance_cents'));

        // Une ligne arrivée après coup : seul le complément est versé
        $this->subscriptionLine($r, 5000);
        $this->artisan('representants:calculer-mois', ['mois' => self::MONTH])->assertSuccessful();

        $this->assertSame(2, Commission::where('user_id', $profile->user_id)->count());
        $this->assertSame(300000, Wallet::where('user_id', $profile->user_id)->value('balance_cents'));
        $this->assertSame(300000, RepresentativeStatement::first()->credited_cents);
    }

    public function test_dry_run_writes_nothing(): void
    {
        $profile = $this->representative();
        $r = Restaurant::factory()->create(['referred_by_user_id' => $profile->user_id]);
        $this->subscriptionLine($r, 10000);

        $this->artisan('representants:calculer-mois', ['mois' => self::MONTH, '--dry-run' => true])
            ->expectsOutputToContain('DRY-RUN')
            ->assertSuccessful();

        $this->assertSame(0, RepresentativeStatement::count());
        $this->assertSame(0, Commission::count());
    }

    public function test_invalid_or_future_month_is_rejected(): void
    {
        $this->artisan('representants:calculer-mois', ['mois' => '2026-13'])->assertFailed();
        $this->artisan('representants:calculer-mois', ['mois' => now()->addMonths(2)->format('Y-m')])->assertFailed();
    }

    public function test_exclusive_city_lines_count_only_after_exclusivity_is_granted(): void
    {
        $profile = $this->representative([
            'exclusivity_active' => true,
            'exclusivity_granted_at' => self::MONTH . '-12 00:00:00',
        ]);
        $cityRestaurant = Restaurant::factory()->create(['city' => 'Daloa']);
        $this->subscriptionLine($cityRestaurant, 10000, ['occurred_at' => self::MONTH . '-05 10:00:00']); // avant octroi
        $this->subscriptionLine($cityRestaurant, 6000, ['occurred_at' => self::MONTH . '-20 10:00:00']);  // après

        $data = app(RepresentativeService::class)->calculate($profile, self::MONTH);

        $this->assertSame(6000, $data['subscription_collected']);
        $this->assertSame(1200, $data['total_share']);
    }

    public function test_suspended_restaurant_earns_nothing(): void
    {
        $profile = $this->representative();
        $r = Restaurant::factory()->create(['referred_by_user_id' => $profile->user_id, 'status' => RestaurantStatus::SUSPENDED]);
        $this->subscriptionLine($r, 10000);

        $data = app(RepresentativeService::class)->calculate($profile, self::MONTH);

        $this->assertSame(1, $data['restaurants_attributed']);
        $this->assertSame(0, $data['restaurants_active']);
        $this->assertSame(0, $data['total_share']);
    }

    public function test_quarter_progress_counts_paying_restaurants(): void
    {
        $profile = $this->representative(['quarterly_target' => 4]);
        $a = Restaurant::factory()->create(['referred_by_user_id' => $profile->user_id]);
        $b = Restaurant::factory()->create(['referred_by_user_id' => $profile->user_id]);
        Restaurant::factory()->create(['referred_by_user_id' => $profile->user_id]); // n'a pas payé
        $this->subscriptionLine($a, 10000, ['occurred_at' => '2026-07-03 10:00:00']);
        $this->subscriptionLine($a, 10000, ['occurred_at' => '2026-08-03 10:00:00']);
        $this->subscriptionLine($b, 10000, ['occurred_at' => '2026-09-30 10:00:00']);
        $this->subscriptionLine($b, 10000, ['occurred_at' => '2026-10-01 10:00:00']); // trimestre suivant

        $progress = app(RepresentativeService::class)->quarterProgress($profile, new \DateTime('2026-08-15'));

        $this->assertSame('T3 2026', $progress['label']);
        $this->assertSame(2, $progress['achieved']);
        $this->assertSame(4, $progress['target']);
        $this->assertSame(50, $progress['percent']);
    }

    // =========================================================================
    // ESPACES ET EXPORTS
    // =========================================================================

    public function test_representative_space_and_admin_pages_show_figures_and_export(): void
    {
        $profile = $this->representative();
        $r = Restaurant::factory()->create(['referred_by_user_id' => $profile->user_id, 'name' => 'Maquis Central']);
        $this->subscriptionLine($r, 10000);
        app(RepresentativeService::class)->computeMonth($profile, self::MONTH);

        $this->actingAs($profile->user)->get(route('crm.dashboard'))
            ->assertOk()->assertSee('Maquis Central')->assertSee('Objectif');

        $csv = $this->actingAs($profile->user)->get(route('crm.representative.export'));
        $csv->assertOk();
        $this->assertStringContainsString('2026-09', $csv->streamedContent());
        $this->assertStringContainsString('2000', $csv->streamedContent());

        $admin = User::factory()->superAdmin()->create();
        $this->actingAs($admin)->get(route('super-admin.representatives.index'))->assertOk()->assertSee($profile->user->name);
        $this->actingAs($admin)->get(route('super-admin.representatives.show', $profile))->assertOk()->assertSee('Maquis Central');
        $adminCsv = $this->actingAs($admin)->get(route('super-admin.representatives.export'));
        $adminCsv->assertOk();
        $this->assertStringContainsString($profile->user->name, $adminCsv->streamedContent());

        // Recalcul depuis la fiche : idempotent
        $this->actingAs($admin)->post(route('super-admin.representatives.compute', $profile), ['month' => self::MONTH])->assertRedirect();
        $this->assertSame(1, Commission::where('user_id', $profile->user_id)->count());

        // Un autre utilisateur CRM n'accède pas à l'export représentant
        $commercial = User::factory()->create();
        $commercial->role = UserRole::COMMERCIAL;
        $commercial->save();
        $this->actingAs($commercial)->get(route('crm.representative.export'))->assertForbidden();
    }
}
