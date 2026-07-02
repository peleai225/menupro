<?php

namespace Database\Seeders;

use App\Models\DeliveryCity;
use App\Models\DeliveryZone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DeliveryCitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            [
                'name' => 'Abidjan',
                'center_latitude' => 5.3600,
                'center_longitude' => -4.0083,
                'coverage_radius_km' => 20,
                'delivery_base_fee' => 50000,
                'delivery_fee_per_km' => 15000,
                'max_delivery_distance_km' => 12,
            ],
            [
                'name' => 'Bouaké',
                'center_latitude' => 7.6936,
                'center_longitude' => -5.0309,
                'coverage_radius_km' => 10,
                'delivery_base_fee' => 40000,
                'delivery_fee_per_km' => 10000,
                'max_delivery_distance_km' => 8,
            ],
            [
                'name' => 'Yamoussoukro',
                'center_latitude' => 6.8276,
                'center_longitude' => -5.2893,
                'coverage_radius_km' => 10,
                'delivery_base_fee' => 40000,
                'delivery_fee_per_km' => 10000,
                'max_delivery_distance_km' => 8,
            ],
            [
                'name' => 'San-Pédro',
                'center_latitude' => 4.7400,
                'center_longitude' => -6.6363,
                'coverage_radius_km' => 8,
                'delivery_base_fee' => 40000,
                'delivery_fee_per_km' => 10000,
                'max_delivery_distance_km' => 7,
            ],
            [
                'name' => 'Daloa',
                'center_latitude' => 6.8774,
                'center_longitude' => -6.4502,
                'coverage_radius_km' => 8,
                'delivery_base_fee' => 40000,
                'delivery_fee_per_km' => 10000,
                'max_delivery_distance_km' => 7,
            ],
        ];

        foreach ($cities as $city) {
            DeliveryCity::firstOrCreate(
                ['slug' => Str::slug($city['name'])],
                array_merge($city, [
                    'slug' => Str::slug($city['name']),
                    'country' => 'CI',
                    'is_active' => true,
                    'peak_hour_surcharge_percent' => 20,
                    'min_order_amount' => 0,
                    'currency' => 'XOF',
                ])
            );
        }

        $this->backfillZones();
    }

    private function backfillZones(): void
    {
        $zones = DeliveryZone::whereNull('delivery_city_id')->get();

        foreach ($zones as $zone) {
            $city = DeliveryCity::where('name', $zone->city)->first();
            if ($city) {
                $zone->update(['delivery_city_id' => $city->id]);
            }
        }
    }
}
