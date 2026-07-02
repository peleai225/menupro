<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_zones', function (Blueprint $table) {
            $table->foreignId('delivery_city_id')->nullable()->after('id')->constrained('delivery_cities')->nullOnDelete();
            $table->index('delivery_city_id');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_zones', function (Blueprint $table) {
            $table->dropForeign(['delivery_city_id']);
            $table->dropIndex(['delivery_city_id']);
            $table->dropColumn('delivery_city_id');
        });
    }
};
