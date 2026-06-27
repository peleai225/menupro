<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->string('wave_business_phone', 20)->nullable()->after('wave_merchant_id');
            $table->boolean('wave_business_enabled')->default(false)->after('wave_business_phone');
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn([
                'wave_business_phone',
                'wave_business_enabled',
            ]);
        });
    }
};
