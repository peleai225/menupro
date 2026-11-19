<?php

namespace Tests\Feature\Parcels;

use App\Enums\ParcelStatus;
use App\Jobs\SendPartnerWebhookJob;
use App\Models\DeliveryPartner;
use App\Models\DeliveryPartnerWebhookLog;
use App\Models\LedgerEntry;
use App\Models\Parcel;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

class ParcelPartnerApiTest extends ParcelTestCase
{
    private function partner(array $attrs = []): array
    {
        $partner = DeliveryPartner::create(array_merge(['name' => 'Vendeuses CI', 'is_active' => true], $attrs));
        $key = $partner->generateApiKey();

        return [$partner, $key];
    }

    private function partnerPayload(array $override = []): array
    {
        return array_merge($this->payload(), [
            'sender_name' => 'Vendeuses CI',
            'sender_phone' => '+225 0102030405',
            'external_reference' => 'CMD-1001',
            'pickup_contact' => ['name' => 'Mariam (vendeuse)', 'phone' => '+225 0708091011'],
        ], $override);
    }

    public function test_api_key_is_hashed_and_auth_rejects_missing_invalid_or_disabled_keys(): void
    {
        [$partner, $key] = $this->partner();

        $this->assertStringStartsWith('mpk_', $key);
        $this->assertSame(hash('sha256', $key), $partner->fresh()->api_key_hash);
        $this->assertStringNotContainsString($key, json_encode($partner->fresh()->getAttributes()));

        $this->postJson('/api/v1/partner/parcels/quote', [])->assertStatus(401)->assertJson(['error_code' => 'invalid_api_key']);
        $this->withToken('mpk_faux')->postJson('/api/v1/partner/parcels/quote', [])->assertStatus(401);

        $quote = ['pickup_lat' => self::LAT, 'pickup_lng' => self::LNG, 'dropoff_lat' => $this->northKm(3), 'dropoff_lng' => self::LNG, 'size' => 'small'];
        $this->withToken($key)->postJson('/api/v1/partner/parcels/quote', $quote)->assertOk()->assertJson(['price' => 900]);

        $partner->update(['is_active' => false]);
        $this->withToken($key)->postJson('/api/v1/partner/parcels/quote', $quote)->assertStatus(403)->assertJson(['error_code' => 'partner_disabled']);

        // Clé régénérée : l'ancienne ne marche plus
        $partner->update(['is_active' => true]);
        $newKey = $partner->generateApiKey();
        $this->withToken($key)->postJson('/api/v1/partner/parcels/quote', $quote)->assertStatus(401);
        $this->withToken($newKey)->postJson('/api/v1/partner/parcels/quote', $quote)->assertOk();
    }

    public function test_partner_parcel_is_billed_and_external_reference_is_idempotent(): void
    {
        [$partner, $key] = $this->partner(['discount_percent' => 10]);

        $first = $this->withToken($key)->postJson('/api/v1/partner/parcels', $this->partnerPayload());
        $first->assertCreated()
            ->assertJsonPath('idempotent_replay', false)
            ->assertJsonPath('parcel.status', 'searching_driver')
            ->assertJsonPath('parcel.payment.method', 'partner_billing')
            ->assertJsonPath('parcel.payment.status', 'billed_to_partner')
            ->assertJsonPath('parcel.external_reference', 'CMD-1001')
            ->assertJsonPath('parcel.pickup_contact.name', 'Mariam (vendeuse)')
            ->assertJsonPath('parcel.price', 800); // 900 − 10 % = 810 → 800 (pas de 50)
        $this->assertMatchesRegularExpression('/^\d{4}$/', $first->json('parcel.handover_code'));

        $again = $this->withToken($key)->postJson('/api/v1/partner/parcels', $this->partnerPayload(['recipient_name' => 'Autre']));
        $again->assertOk()
            ->assertJsonPath('idempotent_replay', true)
            ->assertJsonPath('parcel.reference', $first->json('parcel.reference'));

        $this->assertSame(1, Parcel::count());
        $this->assertNull(Parcel::first()->cod_amount);

        // Référence externe obligatoire
        $this->withToken($key)->postJson('/api/v1/partner/parcels', $this->partnerPayload(['external_reference' => null]))
            ->assertStatus(422)->assertJsonValidationErrors('external_reference');
    }

    public function test_partners_are_isolated(): void
    {
        [, $keyA] = $this->partner();
        [, $keyB] = $this->partner(['name' => 'Autre plateforme']);

        $ref = $this->withToken($keyA)->postJson('/api/v1/partner/parcels', $this->partnerPayload())->json('parcel.reference');

        $this->withToken($keyB)->getJson("/api/v1/partner/parcels/{$ref}")->assertNotFound();
        $this->withToken($keyB)->getJson('/api/v1/partner/parcels/CMD-1001')->assertNotFound();
        $this->withToken($keyB)->postJson("/api/v1/partner/parcels/{$ref}/cancel")->assertNotFound();

        // Même référence externe chez B : nouveau colis (unicité par partenaire)
        $this->withToken($keyB)->postJson('/api/v1/partner/parcels', $this->partnerPayload())->assertCreated();
        $this->assertSame(2, Parcel::count());

        $this->withToken($keyA)->getJson('/api/v1/partner/parcels/CMD-1001')->assertOk()->assertJsonPath('parcel.reference', $ref);
        $this->withToken($keyA)->postJson("/api/v1/partner/parcels/{$ref}/cancel", ['reason' => 'Commande annulée'])
            ->assertOk()->assertJsonPath('parcel.status', 'cancelled');
    }

    public function test_partner_city_restriction(): void
    {
        [, $key] = $this->partner(['cities' => ['Bouaké']]);

        $this->withToken($key)->postJson('/api/v1/partner/parcels', $this->partnerPayload())
            ->assertStatus(422)->assertJson(['error_code' => 'city_not_allowed_for_partner']);
    }

    public function test_status_changes_send_signed_webhooks(): void
    {
        Http::fake(['https://partenaire.example/*' => Http::response(['ok' => true]), '*' => Http::response([])]);
        [$partner, $key] = $this->partner(['webhook_url' => 'https://partenaire.example/hooks/menupro']);
        $secret = $partner->generateWebhookSecret();

        $this->withToken($key)->postJson('/api/v1/partner/parcels', $this->partnerPayload())->assertCreated();

        $sent = collect(Http::recorded())->map(fn ($pair) => $pair[0])
            ->filter(fn (Request $r) => str_starts_with($r->url(), 'https://partenaire.example'));
        $this->assertCount(1, $sent);

        /** @var Request $request */
        $request = $sent->first();
        $this->assertSame(hash_hmac('sha256', $request->body(), $secret), $request->header('X-MenuPro-Signature')[0]);
        $this->assertSame('parcel.status_changed', $request->header('X-MenuPro-Event')[0]);
        $this->assertSame('searching_driver', $request['data']['status']);
        $this->assertSame('CMD-1001', $request['data']['external_reference']);
        $this->assertArrayNotHasKey('handover_code', $request['data']);

        $log = DeliveryPartnerWebhookLog::firstOrFail();
        $this->assertTrue($log->success);
        $this->assertSame(200, $log->status_code);
    }

    public function test_failed_webhook_is_retried_with_backoff_then_abandoned(): void
    {
        Http::swap(new HttpFactory());
        Http::fake(['https://partenaire.example/*' => Http::response('Erreur', 500)]);
        [$partner] = $this->partner(['webhook_url' => 'https://partenaire.example/hooks']);
        $partner->generateWebhookSecret();

        // File « sync » en test : chaque reprise s'exécute aussitôt (le délai est ignoré)
        SendPartnerWebhookJob::dispatch($partner->id, null, 'ping', ['event' => 'ping']);

        $logs = DeliveryPartnerWebhookLog::orderBy('id')->get();
        $this->assertCount(SendPartnerWebhookJob::MAX_ATTEMPTS, $logs);
        $this->assertSame(range(1, SendPartnerWebhookJob::MAX_ATTEMPTS), $logs->pluck('attempt')->all());
        $this->assertTrue($logs->every(fn ($l) => !$l->success && $l->status_code === 500));
    }

    public function test_webhook_succeeds_on_retry(): void
    {
        Http::swap(new HttpFactory());
        Http::fake(['https://partenaire.example/*' => Http::sequence()->push('down', 503)->push('ok', 200)]);
        [$partner] = $this->partner(['webhook_url' => 'https://partenaire.example/hooks']);
        $partner->generateWebhookSecret();

        SendPartnerWebhookJob::dispatch($partner->id, null, 'ping', ['event' => 'ping']);

        $this->assertSame([false, true], DeliveryPartnerWebhookLog::orderBy('id')->pluck('success')->all());
    }

    public function test_delivered_partner_parcel_is_recorded_as_receivable_in_ledger(): void
    {
        [$partner, $key] = $this->partner();
        [$driverUser, $driver] = $this->driver();
        $ref = $this->withToken($key)->postJson('/api/v1/partner/parcels', $this->partnerPayload())->json('parcel.reference');
        $parcel = Parcel::where('reference', $ref)->first();
        $parcel->forceFill(['status' => ParcelStatus::DELIVERING, 'driver_id' => $driver->id])->save();

        $this->actingAs($driverUser, 'sanctum')->postJson("/api/v1/driver/parcels/{$ref}/deliver", ['code' => $parcel->handover_code])->assertOk();

        $entry = LedgerEntry::where('idempotency_key', "parcel:{$parcel->id}:payment")->firstOrFail();
        $this->assertSame(LedgerEntry::STATUS_PENDING, $entry->status);
        $this->assertSame('partner_api', $entry->channel);
        $this->assertSame('partner_billing', $entry->method_detail);
    }
}
