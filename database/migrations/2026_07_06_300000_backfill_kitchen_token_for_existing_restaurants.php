<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        // Tous les restaurants sans kitchen_token en reçoivent un maintenant
        DB::table('restaurants')
            ->whereNull('kitchen_token')
            ->orWhere('kitchen_token', '')
            ->get(['id'])
            ->each(function ($restaurant) {
                DB::table('restaurants')
                    ->where('id', $restaurant->id)
                    ->update(['kitchen_token' => Str::random(48)]);
            });
    }

    public function down(): void {}
};
