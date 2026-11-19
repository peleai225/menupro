<?php

namespace Tests\Feature\Parcels;

use App\Enums\ParcelStatus;
use App\Livewire\Restaurant\Parcels as RestaurantParcels;
use App\Models\DeliveryPartner;
use App\Models\LedgerEntry;
use App\Models\Parcel;
use App\Models\Restaurant;
use App\Models\User;
use App\Notifications\Parcels\ParcelAdminAlertNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

class ParcelAdminAndDispatchTest extends ParcelTestCase
{
    public function test_watch_command_relaunches_then_alerts_admin_once(): void
    {
        Notification::fake();
        $admin = User::factory()->superAdmin()->create();
        [$user] = $this->customer();
        $parcel = $this->createCashParcel($user);
        $this->assertSame(1, $parcel->fresh()->dispatch_attempts);

        // Trop tôt : rien
        $this->artisan('parcels:surveiller')->assertSuccessful();
        $this->assertSame(1, $parcel->fresh()->dispatch_attempts);

        $this->travel(3)->minutes();
        $this->artisan('parcels:surveiller')->assertSuccessful();
        $this->assertSame(2, $parcel->fresh()->dispatch_attempts);
        Notification::assertNotSentTo($admin, ParcelAdminAlertNotification::class);

        $this->travel(10)->minutes();
        $this->artisan('parcels:surveiller')->assertSuccessful();
        $this->artisan('parcels:surveiller')->assertSuccessful();
        Notification::assertSentToTimes($admin, ParcelAdminAlertNotification::class, 1);
        $this->assertNotNull($parcel->fresh()->no_driver_alerted_at);
    }

    public function test_watch_command_expires_unpaid_jeko_parcels(): void
    {
        $this->mockJeko();
        [$user] = $this->customer();
        $this->actingAs($user, 'sanctum')->postJson('/api/v1/client/parcels', $this->payload(['payment_method' => 'jeko']))->assertCreated();

        $this->travel(61)->minutes();
        $this->artisan('parcels:surveiller')->assertSuccessful();

        $parcel = Parcel::first();
        $this->assertSame(ParcelStatus::CANCELLED, $parcel->status);
        $this->assertSame('system', $parcel->cancelled_by);
    }

    public function test_admin_pages_render_and_tariff_update(): void
    {
        $admin = User::factory()->superAdmin()->create();
        [$user] = $this->customer();
        $parcel = $this->createCashParcel($user);
        $partner = DeliveryPartner::create(['name' => 'Vendeuses CI', 'is_active' => true]);

        $this->actingAs($admin)->get(route('super-admin.parcels.index', ['status' => 'searching_driver', 'city' => 'Daloa']))
            ->assertOk()->assertSee($parcel->reference);
        $this->actingAs($admin)->get(route('super-admin.parcels.show', $parcel))->assertOk()->assertSee('Étapes');
        $this->actingAs($admin)->get(route('super-admin.parcels.tariffs'))->assertOk()->assertSee('Daloa');
        $this->actingAs($admin)->get(route('super-admin.parcels.billing'))->assertOk();
        $this->actingAs($admin)->get(route('super-admin.partners.index'))->assertOk()->assertSee('Vendeuses CI');
        $this->actingAs($admin)->get(route('super-admin.partners.show', $partner))->assertOk();
        $this->actingAs($admin)->get(route('super-admin.partners.create'))->assertOk();

        $this->actingAs($admin)->put(route('super-admin.parcels.tariffs.update', $this->city), [
            'enabled' => 1, 'base_fee' => 600, 'per_km_fee' => '', 'large' => 700,
        ])->assertRedirect();
        $this->assertSame(['enabled' => true, 'base_fee' => 600, 'size_supplements' => ['large' => 700]], $this->city->fresh()->parcel_pricing);

        $this->actingAs($admin)->post(route('super-admin.partners.store'), ['name' => 'Nouvelle', 'is_active' => 1])
            ->assertRedirect()->assertSessionHas('plain_api_key');
    }

    public function test_admin_cancel_and_refund_flow_updates_ledger(): void
    {
        $admin = User::factory()->superAdmin()->create();
        [, $driver] = $this->driver();
        $parcel = $this->parcelAt(ParcelStatus::ASSIGNED, $driver, 'jeko');
        app(\App\Services\LedgerService::class)->write(app(\App\Services\LedgerService::class)->parcelPaymentEntry($parcel));

        $this->actingAs($admin)->post(route('super-admin.parcels.cancel', $parcel), ['reason' => 'Doublon'])->assertRedirect();
        $this->assertSame('pending', $parcel->fresh()->refund_status);

        $this->actingAs($admin)->post(route('super-admin.parcels.refunded', $parcel))->assertRedirect();
        $parcel->refresh();
        $this->assertSame('refunded', $parcel->payment_status);
        $this->assertSame(LedgerEntry::STATUS_REFUNDED, LedgerEntry::where('idempotency_key', "parcel:{$parcel->id}:payment")->value('status'));
        $this->assertSame(-180, (int) LedgerEntry::where('idempotency_key', "parcel:{$parcel->id}:refund")->value('menupro_commission'));
    }

    public function test_non_admin_cannot_access_admin_pages(): void
    {
        [$user] = $this->customer();
        $this->actingAs($user)->get(route('super-admin.parcels.index'))->assertStatus(403);
    }

    public function test_restaurant_page_creates_cash_parcel(): void
    {
        $restaurant = Restaurant::factory()->create(['latitude' => self::LAT, 'longitude' => self::LNG, 'phone' => '+225 0101010101', 'address' => 'Rue du commerce']);
        $owner = User::factory()->restaurantAdmin($restaurant)->create();
        // vendor partagé (jonction) : l'autoload « files » peut ne pas inclure ce fichier
        require_once base_path('app/Support/helpers.php');

        $this->actingAs($owner)->get(route('restaurant.parcels'))->assertOk()->assertSee('Envoyer un colis');

        Livewire::actingAs($owner)->test(RestaurantParcels::class)
            ->set('dropoff_coords', $this->northKm(3) . ', ' . self::LNG)
            ->assertSet('quote.price', 900)
            ->set('recipient_name', 'Fatou Bamba')
            ->set('recipient_phone', '+225 0505050505')
            ->set('description', 'Commande de pagnes')
            ->call('send')
            ->assertHasNoErrors();

        $parcel = Parcel::firstOrFail();
        $this->assertSame(Parcel::SENDER_RESTAURANT, $parcel->sender_type);
        $this->assertSame($restaurant->id, $parcel->restaurant_id);
        $this->assertSame(ParcelStatus::SEARCHING_DRIVER, $parcel->status);
        $this->assertSame($restaurant->name, $parcel->sender_name);
    }
}
