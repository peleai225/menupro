<?php

namespace Database\Seeders;

use App\Models\SystemPaymentSetting;
use Illuminate\Database\Seeder;

class SystemPaymentSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $gateways = [
            ['gateway' => 'jeko_marketplace', 'is_active' => false, 'mode' => 'production'],
            ['gateway' => 'jeko_normal', 'is_active' => false, 'mode' => 'production'],
            ['gateway' => 'wave', 'is_active' => false, 'mode' => 'production'],
            ['gateway' => 'cinetpay', 'is_active' => false, 'mode' => 'production'],
        ];

        foreach ($gateways as $gateway) {
            SystemPaymentSetting::firstOrCreate(
                ['gateway' => $gateway['gateway']],
                $gateway
            );
        }
    }
}
