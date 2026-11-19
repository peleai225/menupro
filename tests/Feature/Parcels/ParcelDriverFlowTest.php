<?php

namespace Tests\Feature\Parcels;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\ParcelStatus;
use App\Models\Delivery;
use App\Models\DriverCashDebt;
use App\Models\DriverEarning;
use App\Models\LedgerEntry;
use App\Models\Order;
use App\Models\Parcel;
use App\Models\Restaurant;
use App\Models\User;
use App\Notifications\Parcels\ParcelAdminAlertNotification;
use App\Notifications\Parcels\ParcelSenderNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class ParcelDriverFlowTest extends ParcelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function photo(): UploadedFile
    {
        return UploadedFile::fake()->image('colis.jpg', 800, 600)->size(300);
    }

    public function test_full_cash_flow_with_ledger_earnings_and_cash_debt(): void
    {
        Notification::fake();
        [$driverUser, $driver] = $this->driver();
        [$user, $customer] = $this->customer();
        $parcel = $this->createCashParcel($user, ['size' => 'medium']); // 1100 F

        // Disponible dans l'app livreur
        $this->actingAs($driverUser, 'sanctum')->getJson('/api/v1/driver/parcels/available')
            ->assertOk()
            ->assertJsonPath('data.0.kind', 'parcel')
            ->assertJsonPath('data.0.reference', $parcel->reference)
            ->assertJsonPath('data.0.cash_to_collect', 1100)
            ->assertJsonPath('data.0.pickup.contact_phone', null) // pas avant acceptation
            ->assertJsonMissingPath('data.0.handover_code');

        $this->actingAs($driverUser, 'sanctum')->postJson("/api/v1/driver/parcels/{$parcel->reference}/accept")
            ->assertOk()
            ->assertJsonPath('data.status', 'assigned')
            ->assertJsonPath('data.pickup.contact_phone', '+225 0701020304');
        $this->assertFalse($driver->fresh()->is_available);
        Notification::assertSentTo($customer, ParcelSenderNotification::class, fn ($n) => $n->event === 'driver_found');

        // Espèces : confirmation obligatoire
        $this->actingAs($driverUser, 'sanctum')->post("/api/v1/driver/parcels/{$parcel->reference}/pickup", ['photo' => $this->photo()], ['Accept' => 'application/json'])
            ->assertStatus(422)->assertJson(['error_code' => 'cash_not_confirmed']);
        $this->assertSame([], Storage::disk('public')->allFiles('parcel-pickups'), 'Photo supprimée si refus');

        $this->actingAs($driverUser, 'sanctum')->post("/api/v1/driver/parcels/{$parcel->reference}/pickup", ['photo' => $this->photo(), 'cash_received' => '1'], ['Accept' => 'application/json'])
            ->assertOk()->assertJsonPath('data.status', 'picked_up');

        $parcel->refresh();
        $this->assertSame('paid', $parcel->payment_status);
        $this->assertTrue($parcel->cash_collected);
        Storage::disk('public')->assertExists($parcel->pickup_photo_path);

        // Dette espèces envers MenuPro = commission
        $debt = DriverCashDebt::where('parcel_id', $parcel->id)->firstOrFail();
        $this->assertSame(220, $debt->amount_xof);
        $this->assertNull($debt->restaurant_id);
        $this->actingAs($driverUser, 'sanctum')->getJson('/api/v1/driver/cash-balance')
            ->assertOk()->assertJsonPath('total_owed_xof', 220)->assertJsonPath('debts.0.restaurant_name', 'MenuPro');

        $payment = LedgerEntry::where('idempotency_key', "parcel:{$parcel->id}:payment")->firstOrFail();
        $this->assertSame('cash', $payment->method);
        $this->assertSame(1100, $payment->gross_amount);
        $this->assertSame(220, $payment->net_menupro);
        $this->assertSame(0, $payment->gateway_fee);

        $this->actingAs($driverUser, 'sanctum')->postJson("/api/v1/driver/parcels/{$parcel->reference}/start")
            ->assertOk()->assertJsonPath('data.status', 'delivering');

        $this->actingAs($driverUser, 'sanctum')->postJson("/api/v1/driver/parcels/{$parcel->reference}/deliver", ['code' => $parcel->handover_code])
            ->assertOk()->assertJsonPath('data.status', 'delivered')->assertJsonPath('earning', 880);

        $driver->refresh();
        $this->assertTrue($driver->is_available);
        $this->assertSame(1, $driver->total_deliveries);

        $earning = DriverEarning::where('parcel_id', $parcel->id)->firstOrFail();
        $this->assertSame([1100, 220, 880, 'paid'], [$earning->gross_amount, $earning->platform_cut, $earning->net_amount, $earning->status]);
        $commission = LedgerEntry::where('idempotency_key', 'driver_earning:' . $earning->id)->firstOrFail();
        $this->assertSame(LedgerEntry::TYPE_DRIVER_COMMISSION, $commission->type);
        $this->assertSame(880, $commission->driver_share);
        $this->assertSame($parcel->reference, $commission->order_reference);

        // L'historique des gains affiche la référence du colis (pas de commande)
        $this->actingAs($driverUser, 'sanctum')->getJson('/api/v1/driver/earnings/history')
            ->assertOk()->assertJsonPath('data.0.order_ref', $parcel->reference)->assertJsonPath('data.0.kind', 'parcel');

        Notification::assertSentTo($customer, ParcelSenderNotification::class, fn ($n) => $n->event === 'delivered');
    }

    public function test_jeko_parcel_earning_is_withdrawable(): void
    {
        [$driverUser, $driver] = $this->driver();
        $parcel = $this->parcelAt(ParcelStatus::DELIVERING, $driver, 'jeko');

        $this->actingAs($driverUser, 'sanctum')->postJson("/api/v1/driver/parcels/{$parcel->reference}/deliver", ['code' => $parcel->handover_code])->assertOk();

        $this->assertSame('available', DriverEarning::where('parcel_id', $parcel->id)->value('status'));
        $this->assertSame(0, DriverCashDebt::count());
    }

    public function test_only_one_driver_can_accept(): void
    {
        [$u1] = $this->driver();
        [$u2] = $this->driver();
        [$user] = $this->customer();
        $parcel = $this->createCashParcel($user);

        $this->actingAs($u1, 'sanctum')->postJson("/api/v1/driver/parcels/{$parcel->reference}/accept")->assertOk();
        $this->actingAs($u2, 'sanctum')->postJson("/api/v1/driver/parcels/{$parcel->reference}/accept")
            ->assertStatus(409)->assertJson(['error_code' => 'already_taken']);
    }

    public function test_busy_driver_cannot_accept_a_parcel_nor_a_meal(): void
    {
        [$driverUser, $driver] = $this->driver();
        [$user] = $this->customer();
        $first = $this->createCashParcel($user);
        $second = $this->createCashParcel($user);

        $this->actingAs($driverUser, 'sanctum')->postJson("/api/v1/driver/parcels/{$first->reference}/accept")->assertOk();
        $driver->fresh()->update(['is_available' => true]); // même s'il se remet en ligne

        $this->actingAs($driverUser, 'sanctum')->postJson("/api/v1/driver/parcels/{$second->reference}/accept")
            ->assertStatus(422)->assertJson(['error_code' => 'driver_busy']);

        $restaurant = Restaurant::factory()->create();
        $order = Order::factory()->create(['restaurant_id' => $restaurant->id, 'status' => OrderStatus::READY]);
        $delivery = Delivery::factory()->create(['order_id' => $order->id, 'restaurant_id' => $restaurant->id, 'status' => DeliveryStatus::PENDING]);

        $this->actingAs($driverUser, 'sanctum')->postJson("/api/v1/driver/deliveries/{$delivery->id}/accept")
            ->assertStatus(422)->assertJson(['message' => 'Vous avez déjà une course en cours.']);
    }

    public function test_unassigned_driver_cannot_act(): void
    {
        [, $driver] = $this->driver();
        [$otherUser] = $this->driver();
        $parcel = $this->parcelAt(ParcelStatus::ASSIGNED, $driver);

        $this->actingAs($otherUser, 'sanctum')->post("/api/v1/driver/parcels/{$parcel->reference}/pickup", ['photo' => $this->photo(), 'cash_received' => '1'], ['Accept' => 'application/json'])
            ->assertStatus(403)->assertJson(['error_code' => 'not_your_parcel']);
        $this->actingAs($otherUser, 'sanctum')->postJson("/api/v1/driver/parcels/{$parcel->reference}/deliver", ['code' => '0000'])
            ->assertStatus(403);
        $this->actingAs($otherUser, 'sanctum')->postJson("/api/v1/driver/parcels/{$parcel->reference}/fail", ['reason' => 'Test'])
            ->assertStatus(403);
        $this->actingAs($otherUser, 'sanctum')->getJson("/api/v1/driver/parcels/{$parcel->reference}")->assertStatus(403);

        // Un client n'accède pas à l'API livreur
        [$customerUser] = $this->customer();
        $this->actingAs($customerUser, 'sanctum')->getJson('/api/v1/driver/parcels/available')->assertStatus(403);
    }

    public function test_pickup_photo_is_mandatory_and_validated(): void
    {
        [$driverUser, $driver] = $this->driver();
        $parcel = $this->parcelAt(ParcelStatus::ASSIGNED, $driver);
        $url = "/api/v1/driver/parcels/{$parcel->reference}/pickup";

        $this->actingAs($driverUser, 'sanctum')->postJson($url, ['cash_received' => true])
            ->assertStatus(422)->assertJsonValidationErrors('photo');

        $this->actingAs($driverUser, 'sanctum')->post($url, ['photo' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'), 'cash_received' => '1'], ['Accept' => 'application/json'])
            ->assertStatus(422)->assertJsonValidationErrors('photo');

        $this->actingAs($driverUser, 'sanctum')->post($url, ['photo' => UploadedFile::fake()->image('big.jpg')->size(9000), 'cash_received' => '1'], ['Accept' => 'application/json'])
            ->assertStatus(422)->assertJsonValidationErrors('photo');

        $this->assertSame(ParcelStatus::ASSIGNED, $parcel->fresh()->status);
    }

    public function test_forbidden_transitions_are_refused(): void
    {
        [$driverUser, $driver] = $this->driver();
        $parcel = $this->parcelAt(ParcelStatus::ASSIGNED, $driver);

        // assigned → delivering (sans ramassage)
        $this->actingAs($driverUser, 'sanctum')->postJson("/api/v1/driver/parcels/{$parcel->reference}/start")
            ->assertStatus(422)->assertJson(['error_code' => 'invalid_transition', 'current_status' => 'assigned']);

        // assigned → delivered
        $this->actingAs($driverUser, 'sanctum')->postJson("/api/v1/driver/parcels/{$parcel->reference}/deliver", ['code' => $parcel->handover_code])
            ->assertStatus(422)->assertJson(['error_code' => 'invalid_transition']);

        // Livré : plus rien n'est possible
        $parcel->forceFill(['status' => ParcelStatus::DELIVERED])->save();
        $this->actingAs($driverUser, 'sanctum')->post("/api/v1/driver/parcels/{$parcel->reference}/pickup", ['photo' => $this->photo(), 'cash_received' => '1'], ['Accept' => 'application/json'])
            ->assertStatus(422);

        // Machine d'états du modèle
        $this->assertFalse(ParcelStatus::PENDING_PAYMENT->canTransitionTo(ParcelStatus::ASSIGNED));
        $this->assertFalse(ParcelStatus::PICKED_UP->canTransitionTo(ParcelStatus::SEARCHING_DRIVER));
        $this->assertFalse(ParcelStatus::CANCELLED->canTransitionTo(ParcelStatus::SEARCHING_DRIVER));
        $p = new Parcel(['status' => ParcelStatus::SEARCHING_DRIVER]);
        $p->status = ParcelStatus::SEARCHING_DRIVER;
        $this->assertFalse($p->transitionTo(ParcelStatus::DELIVERED));
        $this->assertTrue($p->transitionTo(ParcelStatus::ASSIGNED));
        $this->assertNotNull($p->assigned_at);
    }

    public function test_wrong_code_counts_attempts_then_locks_and_alerts_admin(): void
    {
        Notification::fake();
        $admin = User::factory()->superAdmin()->create();
        [$driverUser, $driver] = $this->driver();
        $parcel = $this->parcelAt(ParcelStatus::DELIVERING, $driver);
        $parcel->forceFill(['handover_code' => '4821'])->save();
        $url = "/api/v1/driver/parcels/{$parcel->reference}/deliver";

        for ($i = 1; $i <= 4; $i++) {
            $this->actingAs($driverUser, 'sanctum')->postJson($url, ['code' => '0000'])
                ->assertStatus(422)
                ->assertJson(['error_code' => 'invalid_code', 'attempts_left' => 5 - $i]);
        }

        // 5e essai raté : blocage
        $this->actingAs($driverUser, 'sanctum')->postJson($url, ['code' => '1111'])
            ->assertStatus(423)->assertJson(['error_code' => 'code_locked']);
        Notification::assertSentTo($admin, ParcelAdminAlertNotification::class, fn ($n) => $n->alert === 'code_locked');

        // Même le bon code est refusé une fois bloqué
        $this->actingAs($driverUser, 'sanctum')->postJson($url, ['code' => '4821'])->assertStatus(423);
        $this->assertSame(ParcelStatus::DELIVERING, $parcel->fresh()->status);

        // Débloqué par l'admin : le bon code passe
        $this->actingAs($admin)->post(route('super-admin.parcels.unlock', $parcel))->assertRedirect();
        $this->actingAs($driverUser, 'sanctum')->postJson($url, ['code' => '4821'])->assertOk();
        $this->assertSame(ParcelStatus::DELIVERED, $parcel->fresh()->status);
    }

    public function test_code_must_be_four_digits(): void
    {
        [$driverUser, $driver] = $this->driver();
        $parcel = $this->parcelAt(ParcelStatus::DELIVERING, $driver);

        $this->actingAs($driverUser, 'sanctum')->postJson("/api/v1/driver/parcels/{$parcel->reference}/deliver", ['code' => '12a'])
            ->assertStatus(422)->assertJsonValidationErrors('code');
        $this->assertSame(0, $parcel->fresh()->handover_attempts);
    }

    public function test_failure_after_pickup_alerts_admin_and_frees_driver(): void
    {
        Notification::fake();
        $admin = User::factory()->superAdmin()->create();
        [$driverUser, $driver] = $this->driver();
        $parcel = $this->parcelAt(ParcelStatus::DELIVERING, $driver);

        $this->actingAs($driverUser, 'sanctum')->postJson("/api/v1/driver/parcels/{$parcel->reference}/fail", ['reason' => 'Destinataire injoignable'])
            ->assertOk()->assertJson(['status' => 'failed']);

        $parcel->refresh();
        $this->assertSame(ParcelStatus::FAILED, $parcel->status);
        $this->assertSame('Destinataire injoignable', $parcel->failure_reason);
        $this->assertTrue($driver->fresh()->is_available);
        Notification::assertSentTo($admin, ParcelAdminAlertNotification::class, fn ($n) => $n->alert === 'failed');
    }

    public function test_release_before_pickup_puts_parcel_back_in_search(): void
    {
        [$driverUser, $driver] = $this->driver();
        $parcel = $this->parcelAt(ParcelStatus::ASSIGNED, $driver);

        $this->actingAs($driverUser, 'sanctum')->postJson("/api/v1/driver/parcels/{$parcel->reference}/fail", ['reason' => 'Panne de moto'])
            ->assertOk()->assertJson(['status' => 'searching_driver']);

        $parcel->refresh();
        $this->assertSame(ParcelStatus::SEARCHING_DRIVER, $parcel->status);
        $this->assertNull($parcel->driver_id);
        $this->assertTrue($driver->fresh()->is_available);
    }

    public function test_active_parcel_endpoint(): void
    {
        [$driverUser, $driver] = $this->driver();
        $this->actingAs($driverUser, 'sanctum')->getJson('/api/v1/driver/parcels/active')->assertOk()->assertJson(['data' => null]);

        $parcel = $this->parcelAt(ParcelStatus::PICKED_UP, $driver);
        $this->actingAs($driverUser, 'sanctum')->getJson('/api/v1/driver/parcels/active')
            ->assertOk()->assertJsonPath('data.reference', $parcel->reference)->assertJsonPath('data.handover_attempts_left', 5);
    }
}
