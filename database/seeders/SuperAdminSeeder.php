<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create Super Admin user
        User::updateOrCreate(
            ['email' => 'admin@menupro.ci'],
            [
                'name' => 'Super Admin',
                'email' => 'admin@menupro.ci',
                'phone' => '+225 0700000000',
                'password' => Hash::make('password'),
                'role' => UserRole::SUPER_ADMIN,
                'restaurant_id' => null,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Super Admin user created: admin@menupro.ci / password');
    }
}

