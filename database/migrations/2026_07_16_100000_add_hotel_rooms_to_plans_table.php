<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->boolean('has_hotel_rooms')->default(false)->after('has_priority_support');
        });

        DB::table('plans')->whereIn('slug', ['pro', 'business'])->update(['has_hotel_rooms' => true]);
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('has_hotel_rooms');
        });
    }
};
