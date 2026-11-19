<?php

namespace Tests\Feature\Parcels;

use App\Services\Parcels\ParcelException;
use App\Services\Parcels\ParcelPricingService;

class ParcelPricingTest extends ParcelTestCase
{
    private function quote(float $km, string $size = 'small'): array
    {
        return app(ParcelPricingService::class)->quote(self::LAT, self::LNG, $this->northKm($km), self::LNG, $size);
    }

    public function test_price_is_base_plus_km_times_road_factor_rounded_up_to_50(): void
    {
        // 3 km à vol d'oiseau → 3,9 km route → 500 + 390 = 890 → 900
        $q = $this->quote(3);
        $this->assertEqualsWithDelta(3.9, $q['distance_km'], 0.01);
        $this->assertSame(900, $q['price']);

        // 1 km → 1,3 km → 630 → 650
        $this->assertSame(650, $this->quote(1)['price']);

        // 5 km → 6,5 km → 1150 (déjà multiple de 50)
        $this->assertSame(1150, $this->quote(5)['price']);
    }

    public function test_size_supplements_are_added_after_rounding(): void
    {
        $this->assertSame(900, $this->quote(3, 'small')['price']);
        $this->assertSame(1100, $this->quote(3, 'medium')['price']);
        $this->assertSame(1400, $this->quote(3, 'large')['price']);
    }

    public function test_driver_share_is_80_percent_by_default_and_commission_is_the_rest(): void
    {
        $q = $this->quote(3, 'medium'); // 1100
        $this->assertSame(880, $q['driver_share']);
        $this->assertSame(220, $q['platform_commission']);
    }

    public function test_distance_cap_refuses_with_clear_message(): void
    {
        // 11,5 km → 14,95 km route : accepté
        $this->assertSame(2000, $this->quote(11.5)['price']); // 500 + 1495 = 1995 → 2000

        try {
            $this->quote(12); // 15,6 km route > 15
            $this->fail('Le plafond doit refuser la course');
        } catch (ParcelException $e) {
            $this->assertSame('distance_too_long', $e->errorCode);
            $this->assertStringContainsString('maximum', $e->getMessage());
            $this->assertStringContainsString('15 km', $e->getMessage());
        }
    }

    public function test_city_tariff_overrides_defaults(): void
    {
        $this->city->update(['parcel_pricing' => [
            'base_fee' => 700,
            'per_km_fee' => 150,
            'size_supplements' => ['medium' => 300],
            'max_distance_km' => 5,
            'driver_share_percent' => 75,
        ]]);

        // 3 km → 3,9 km : 700 + 585 = 1285 → 1300, + 300 moyen = 1600
        $q = $this->quote(3, 'medium');
        $this->assertSame(1600, $q['price']);
        $this->assertSame(1200, $q['driver_share']);

        // grand : défaut conservé (+500)
        $this->assertSame(1800, $this->quote(3, 'large')['price']);

        $this->expectException(ParcelException::class);
        $this->quote(4); // 5,2 km > 5
    }

    public function test_out_of_city_pickup_is_refused(): void
    {
        $this->expectExceptionMessage('pas encore disponible');
        app(ParcelPricingService::class)->quote(5.35, -4.0, 5.36, -4.0, 'small'); // Abidjan
    }

    public function test_disabled_city_refuses(): void
    {
        $this->city->update(['parcel_pricing' => ['enabled' => false]]);
        $this->expectExceptionMessage('suspendue');
        $this->quote(2);
    }

    public function test_quote_endpoint(): void
    {
        [$user] = $this->customer();

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/client/parcels/quote', [
            'pickup_lat' => self::LAT, 'pickup_lng' => self::LNG,
            'dropoff_lat' => $this->northKm(3), 'dropoff_lng' => self::LNG,
            'size' => 'large',
        ])->assertOk()->assertJson(['price' => 1400, 'city' => 'Daloa', 'currency' => 'XOF']);

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/client/parcels/quote', [
            'pickup_lat' => self::LAT, 'pickup_lng' => self::LNG,
            'dropoff_lat' => $this->northKm(20), 'dropoff_lng' => self::LNG,
            'size' => 'small',
        ])->assertStatus(422)->assertJson(['error_code' => 'distance_too_long']);

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/client/parcels/quote', ['size' => 'huge'])
            ->assertStatus(422)->assertJsonValidationErrors(['pickup_lat', 'size']);
    }
}
