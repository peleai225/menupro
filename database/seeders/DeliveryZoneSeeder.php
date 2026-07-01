<?php

namespace Database\Seeders;

use App\Models\DeliveryZone;
use Illuminate\Database\Seeder;

class DeliveryZoneSeeder extends Seeder
{
    public function run(): void
    {
        $zones = [
            // Abidjan
            ['name' => 'Plateau', 'city' => 'Abidjan', 'center_latitude' => 5.3211, 'center_longitude' => -4.0164, 'radius_km' => 3],
            ['name' => 'Cocody', 'city' => 'Abidjan', 'center_latitude' => 5.3542, 'center_longitude' => -3.9827, 'radius_km' => 5],
            ['name' => 'Yopougon', 'city' => 'Abidjan', 'center_latitude' => 5.3492, 'center_longitude' => -4.0682, 'radius_km' => 7],
            ['name' => 'Marcory', 'city' => 'Abidjan', 'center_latitude' => 5.2979, 'center_longitude' => -3.9860, 'radius_km' => 4],
            ['name' => 'Koumassi', 'city' => 'Abidjan', 'center_latitude' => 5.2881, 'center_longitude' => -3.9749, 'radius_km' => 4],
            ['name' => 'Adjamé', 'city' => 'Abidjan', 'center_latitude' => 5.3607, 'center_longitude' => -4.0186, 'radius_km' => 3],
            ['name' => 'Abobo', 'city' => 'Abidjan', 'center_latitude' => 5.4225, 'center_longitude' => -4.0217, 'radius_km' => 6],
            ['name' => 'Treichville', 'city' => 'Abidjan', 'center_latitude' => 5.2952, 'center_longitude' => -4.0063, 'radius_km' => 3],
            ['name' => 'Port-Bouët', 'city' => 'Abidjan', 'center_latitude' => 5.2548, 'center_longitude' => -3.9281, 'radius_km' => 5],
            ['name' => 'Bingerville', 'city' => 'Abidjan', 'center_latitude' => 5.3600, 'center_longitude' => -3.8900, 'radius_km' => 5],
            ['name' => 'Angré', 'city' => 'Abidjan', 'center_latitude' => 5.3812, 'center_longitude' => -3.9561, 'radius_km' => 4],
            ['name' => 'Riviera', 'city' => 'Abidjan', 'center_latitude' => 5.3700, 'center_longitude' => -3.9600, 'radius_km' => 4],

            // Autres villes
            ['name' => 'Centre', 'city' => 'Bouaké', 'center_latitude' => 7.6936, 'center_longitude' => -5.0309, 'radius_km' => 8],
            ['name' => 'Centre', 'city' => 'Yamoussoukro', 'center_latitude' => 6.8276, 'center_longitude' => -5.2893, 'radius_km' => 8],
            ['name' => 'Centre', 'city' => 'San-Pédro', 'center_latitude' => 4.7400, 'center_longitude' => -6.6363, 'radius_km' => 6],
            ['name' => 'Centre', 'city' => 'Daloa', 'center_latitude' => 6.8774, 'center_longitude' => -6.4502, 'radius_km' => 6],
        ];

        foreach ($zones as $i => $zone) {
            DeliveryZone::firstOrCreate(
                ['name' => $zone['name'], 'city' => $zone['city']],
                array_merge($zone, ['is_active' => true, 'sort_order' => $i])
            );
        }
    }
}
