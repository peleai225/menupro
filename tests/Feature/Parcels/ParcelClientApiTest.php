<?php

namespace Tests\Feature\Parcels;

use App\Enums\ParcelStatus;
use App\Jobs\DispatchParcelJob;
use App\Models\LedgerEntry;
use App\Models\Parcel;
use App\Models\User;
use App\Notifications\Parcels\ParcelAdminAlertNotification;
use App\Notifications\Parcels\ParcelDriverNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

class ParcelClientApiTest extends ParcelTestCase
{
    public function test_cash_parcel_is_created_and_searches_a_driver_immediately(): void
    {
        Queue::fake([DispatchParcelJob::class]);
        [$user, $customer] = $this->customer();

        $res = $this->actingAs($user, 'sanctum')->postJson('/api/v1/client/parcels', $this->payload());

        $res->assertCreated()
            ->assertJsonPath('next_step', 'track')
            ->assertJsonPath('parcel.status', 'searching_driver')
            ->assertJsonPath('parcel.price', 900)
            ->assertJsonPath('parcel.payment.method', 'cash')
            ->assertJsonPath('parcel.sender.name', 'Awa Koné')
            ->assertJsonPath('parcel.sender.phone', '+225 0701020304');

        $this->assertMatchesRegularExpression('/^COL-[A-Z0-9]{6}$/', $res->json('parcel.reference'));
        $this->assertMatchesRegularExpression('/^\d{4}$/', $res->json('parcel.handover_code'));
        $this->assertStringContainsString($res->json('parcel.tracking_token'), $res->json('parcel.tracking_url'));

        $parcel = Parcel::firstOrFail();
        $this->assertSame($customer->id, $parcel->customer_id);
        $this->assertSame(720, $parcel->driver_share);
        $this->assertSame(180, $parcel->platform_commission);
        // Code chiffré en base, jamais en clair
        $raw = \DB::table('parcels')->value('handover_code');
        $this->assertStringNotContainsString($res->json('parcel.handover_code'), $raw);

        Queue::assertPushed(DispatchParcelJob::class);
    }

    public function test_dispatch_notifies_nearby_drivers(): void
    {
        Notification::fake();
        [, $near] = $this->driver(['fcm_token' => 'tok-near']);
        [, $far] = $this->driver(['latitude' => $this->northKm(30), 'fcm_token' => 'tok-far']);
        [$user] = $this->customer();

        $parcel = $this->createCashParcel($user);

        Notification::assertSentTo($near, ParcelDriverNotification::class, fn ($n) => $n->event === 'new');
        Notification::assertNotSentTo($far, ParcelDriverNotification::class);
        $this->assertSame(1, $parcel->fresh()->dispatch_attempts);
    }

    public function test_jeko_parcel_returns_payment_url_and_waits_for_payment(): void
    {
        $this->mockJeko();
        [$user] = $this->customer();

        $res = $this->actingAs($user, 'sanctum')->postJson('/api/v1/client/parcels', $this->payload([
            'payment_method' => 'jeko', 'jeko_operator' => 'orange',
        ]));

        $res->assertCreated()
            ->assertJsonPath('next_step', 'payment')
            ->assertJsonPath('parcel.status', 'pending_payment')
            ->assertJsonPath('payment.payment_url', 'https://pay.jeko.africa/l/jeko-link-123');

        $parcel = Parcel::firstOrFail();
        $this->assertSame('jeko-link-123', $parcel->payment_link_id);
        $this->assertSame('orange', $parcel->jeko_operator);
    }

    public function test_jeko_webhook_marks_parcel_paid_starts_search_and_writes_ledger(): void
    {
        $this->mockJeko();
        [$user] = $this->customer();
        $this->actingAs($user, 'sanctum')->postJson('/api/v1/client/parcels', $this->payload(['payment_method' => 'jeko']))->assertCreated();
        $parcel = Parcel::firstOrFail();

        $this->postJson('/webhooks/jeko', [
            'id' => 'tx-999',
            'status' => 'success',
            'transactionType' => 'payment',
            'transactionDetails' => ['reference' => 'PARCEL-' . $parcel->id . '-1700000000123', 'paymentLinkId' => 'jeko-link-123'],
        ], ['Jeko-Signature' => 'ok'])->assertOk();

        $parcel->refresh();
        $this->assertSame(ParcelStatus::SEARCHING_DRIVER, $parcel->status);
        $this->assertSame('paid', $parcel->payment_status);
        $this->assertSame('tx-999', $parcel->payment_reference);

        $entry = LedgerEntry::where('idempotency_key', 'parcel:' . $parcel->id . ':payment')->firstOrFail();
        $this->assertSame(LedgerEntry::TYPE_PARCEL_DELIVERY, $entry->type);
        $this->assertSame('jeko', $entry->method);
        $this->assertSame(900, $entry->gross_amount);
        $this->assertSame(720, $entry->driver_share);
        $this->assertSame(180, $entry->menupro_commission);
        $fee = (int) round(900 * 1.5 / 100);
        $this->assertSame(900 - $fee - 720, $entry->net_menupro);

        // Rejouer le webhook : idempotent
        $this->postJson('/webhooks/jeko', [
            'id' => 'tx-999', 'status' => 'success', 'transactionType' => 'payment',
            'transactionDetails' => ['reference' => 'PARCEL-' . $parcel->id . '-1700000000123'],
        ], ['Jeko-Signature' => 'ok'])->assertOk();
        $this->assertSame(1, LedgerEntry::where('source_type', 'parcel')->count());
    }

    public function test_validation_errors_are_in_french(): void
    {
        [$user] = $this->customer();

        $res = $this->actingAs($user, 'sanctum')->postJson('/api/v1/client/parcels', $this->payload([
            'recipient_phone' => 'abc', 'description' => '', 'payment_method' => 'bitcoin',
        ]));

        $res->assertStatus(422)->assertJsonValidationErrors(['recipient_phone', 'description', 'payment_method']);
        $this->assertStringContainsString('téléphone du destinataire', $res->json('errors.recipient_phone.0'));
    }

    public function test_customer_sees_only_his_parcels(): void
    {
        [$alice] = $this->customer();
        [$bob] = $this->customer();
        $parcel = $this->createCashParcel($alice);

        $this->actingAs($bob, 'sanctum')->getJson('/api/v1/client/parcels/' . $parcel->reference)->assertNotFound();
        $this->actingAs($bob, 'sanctum')->postJson('/api/v1/client/parcels/' . $parcel->reference . '/cancel')->assertNotFound();
        $this->actingAs($bob, 'sanctum')->getJson('/api/v1/client/parcels')->assertOk()->assertJsonCount(0, 'data');

        $this->actingAs($alice, 'sanctum')->getJson('/api/v1/client/parcels')->assertOk()->assertJsonCount(1, 'data');
        $this->actingAs($alice, 'sanctum')->getJson('/api/v1/client/parcels/' . $parcel->reference)
            ->assertOk()->assertJsonPath('parcel.reference', $parcel->reference);
    }

    public function test_sender_can_cancel_before_pickup_and_driver_is_freed(): void
    {
        Notification::fake();
        [, $driver] = $this->driver();
        $parcel = $this->parcelAt(ParcelStatus::ASSIGNED, $driver);
        $user = User::find($parcel->customer->user_id);

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/client/parcels/' . $parcel->reference . '/cancel', ['reason' => 'Plus besoin'])
            ->assertOk()->assertJsonPath('parcel.status', 'cancelled');

        $this->assertTrue($driver->fresh()->is_available);
        $this->assertSame('sender', $parcel->fresh()->cancelled_by);
        Notification::assertSentTo($driver, ParcelDriverNotification::class, fn ($n) => $n->event === 'cancelled');
    }

    public function test_cancel_after_pickup_is_refused(): void
    {
        [, $driver] = $this->driver();
        $parcel = $this->parcelAt(ParcelStatus::PICKED_UP, $driver);
        $user = User::find($parcel->customer->user_id);

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/client/parcels/' . $parcel->reference . '/cancel')
            ->assertStatus(422)
            ->assertJson(['error_code' => 'cannot_cancel']);

        $this->assertSame(ParcelStatus::PICKED_UP, $parcel->fresh()->status);
    }

    public function test_cancelling_a_paid_jeko_parcel_flags_refund_and_alerts_admin(): void
    {
        Notification::fake();
        $admin = User::factory()->superAdmin()->create();
        [, $driver] = $this->driver();
        $parcel = $this->parcelAt(ParcelStatus::ASSIGNED, $driver, 'jeko');
        $user = User::find($parcel->customer->user_id);

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/client/parcels/' . $parcel->reference . '/cancel')
            ->assertOk()->assertJsonPath('parcel.payment.refund_status', 'pending');

        Notification::assertSentTo($admin, ParcelAdminAlertNotification::class, fn ($n) => $n->alert === 'refund_required');
    }

    public function test_public_tracking_exposes_no_phone_address_or_code(): void
    {
        [, $driver] = $this->driver();
        $parcel = $this->parcelAt(ParcelStatus::DELIVERING, $driver);
        $parcel->forceFill(['handover_code' => '7391'])->save();

        $res = $this->getJson('/api/v1/parcels/track/' . $parcel->tracking_token);

        $res->assertOk()
            ->assertJsonPath('parcel.status', 'delivering')
            ->assertJsonPath('parcel.driver.first_name', 'Koffi')
            ->assertJsonPath('parcel.recipient_first_name', 'Fatou');

        $body = $res->getContent();
        foreach (['0505050505', '0701020304', $driver->phone, 'Tazibouo', 'Marché', 'Yao', $parcel->handover_code] as $secret) {
            $this->assertStringNotContainsString($secret, $body, "Fuite : {$secret}");
        }
        $this->assertArrayNotHasKey('handover_code', $res->json('parcel'));
        $this->assertNotNull($res->json('parcel.eta_minutes'));

        $this->getJson('/api/v1/parcels/track/inconnu')->assertNotFound();
    }

    public function test_sender_sees_driver_position_and_phone_while_active(): void
    {
        [, $driver] = $this->driver();
        $parcel = $this->parcelAt(ParcelStatus::ASSIGNED, $driver);
        $user = User::find($parcel->customer->user_id);

        $this->actingAs($user, 'sanctum')->getJson('/api/v1/client/parcels/' . $parcel->reference)
            ->assertOk()
            ->assertJsonPath('parcel.driver.phone', $driver->phone)
            ->assertJsonPath('parcel.driver.latitude', self::LAT)
            ->assertJsonPath('parcel.can_cancel', true);
    }
}
