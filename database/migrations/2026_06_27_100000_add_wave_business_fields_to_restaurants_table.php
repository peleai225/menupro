<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->text('wave_api_key')->nullable()->after('wave_merchant_id');
            $table->text('wave_webhook_secret')->nullable()->after('wave_api_key');
            $table->boolean('wave_business_enabled')->default(false)->after('wave_webhook_secret');
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn([
                'wave_api_key',
                'wave_webhook_secret',
                'wave_business_enabled',
            ]);
        });
    }
};
