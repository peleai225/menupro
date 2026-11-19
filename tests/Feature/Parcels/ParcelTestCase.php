<?php

namespace Tests\Feature\Parcels;

use App\Enums\ParcelStatus;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\DeliveryCity;
use App\Models\DeliveryDriver;
use App\Models\Parcel;
use App\Models\User;
use App\Services\JekoGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

abstract class ParcelTestCase extends TestCase
{
    use RefreshDatabase;

    /** Centre de Daloa */
    protected const LAT = 6.8774;
    protected const LNG = -6.4502;

    /** km par degré de latitude (R = 6371 km) */
    protected const KM_PER_DEG = 111.19492664;

    protected DeliveryCity $city;

    private int $customerCount = 0;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake(); // aucun appel réseau (FCM, Jeko, webhooks)

        $this->city = DeliveryCity::create([
            'name' => 'Daloa',
            'slug' => 'daloa',
            'center_latitude' => self::LAT,
            'center_longitude' => self::LNG,
            'coverage_radius_km' => 20,
            'is_active' => true,
        ]);
    }

    /** Latitude à $km au nord du centre (distance à vol d'oiseau exacte). */
    protected function northKm(float $km): float
    {
        return self::LAT + $km / self::KM_PER_DEG;
    }

    protected function customer(): array
    {
        $n = ++$this->customerCount;
        $user = User::factory()->create(['name' => 'Awa Koné']);
        $phone = $n === 1 ? '+225 0701020304' : '+225 07010203' . str_pad((string) $n, 2, '0', STR_PAD_LEFT);
        $customer = Customer::create(['user_id' => $user->id, 'phone' => $phone, 'is_active' => true]);

        return [$user, $customer];
    }

    protected function driver(array $attrs = []): array
    {
        $user = User::factory()->create();
        $user->role = UserRole::DELIVERY_DRIVER;
        $user->save();

        $driver = DeliveryDriver::factory()->create(array_merge([
            'user_id' => $user->id,
            'name' => 'Koffi Yao',
            'city' => 'Daloa',
            'is_available' => true,
            'latitude' => self::LAT,
            'longitude' => self::LNG,
        ], $attrs));

        return [$user, $driver];
    }

    protected function payload(array $override = []): array
    {
        return array_merge([
            'pickup_address' => 'Marché central, Daloa',
            'pickup_lat' => self::LAT,
            'pickup_lng' => self::LNG,
            'dropoff_address' => 'Quartier Tazibouo',
            'dropoff_lat' => $this->northKm(3),
            'dropoff_lng' => self::LNG,
            'recipient_name' => 'Fatou Bamba',
            'recipient_phone' => '+225 0505050505',
            'size' => 'small',
            'description' => 'Deux pagnes',
            'payment_method' => 'cash',
        ], $override);
    }

    protected function createCashParcel(User $user, array $override = []): Parcel
    {
        $res = $this->actingAs($user, 'sanctum')->postJson('/api/v1/client/parcels', $this->payload($override));
        $res->assertCreated();

        return Parcel::where('reference', $res->json('parcel.reference'))->firstOrFail();
    }

    protected function mockJeko(): void
    {
        $mock = $this->mock(JekoGateway::class);
        $mock->shouldReceive('forMarketplacePlatform')->andReturnSelf();
        $mock->shouldReceive('isConfigured')->andReturn(true);
        $mock->shouldReceive('createPayment')->andReturn([
            'success' => true,
            'payment_id' => 'jeko-link-123',
            'payment_url' => 'https://pay.jeko.africa/l/jeko-link-123',
            'reference' => 'PARCEL-1-1',
            'status' => 'pending',
        ]);
        $mock->shouldReceive('verifyWebhookSignature')->andReturn(true);
    }

    /** Colis assigné au livreur, dans l'état voulu. */
    protected function parcelAt(ParcelStatus $status, DeliveryDriver $driver, string $payment = 'cash'): Parcel
    {
        [$user] = $this->customer();
        $parcel = $this->createCashParcel($user, ['payment_method' => 'cash']);
        $parcel->forceFill([
            'status' => $status,
            'driver_id' => $driver->id,
            'payment_method' => $payment,
            'payment_status' => $payment === 'jeko' ? 'paid' : 'pending',
        ])->save();
        $driver->update(['is_available' => false]);

        return $parcel->fresh();
    }
}
