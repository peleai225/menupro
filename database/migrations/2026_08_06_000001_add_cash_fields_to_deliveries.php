<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->boolean('cash_collected')->default(false)->after('delivered_at');
            $table->unsignedInteger('cash_collected_amount_xof')->nullable()->after('cash_collected');
            $table->unsignedInteger('cash_owed_to_restaurant_xof')->nullable()->after('cash_collected_amount_xof');
        });
    }

    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropColumn(['cash_collected', 'cash_collected_amount_xof', 'cash_owed_to_restaurant_xof']);
        });
    }
};
