<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_wallets', function (Blueprint $table) {
            if (!Schema::hasColumn('restaurant_wallets', 'recipient_name')) {
                $table->string('recipient_name')->nullable()->after('auto_payout_enabled');
            }
            if (!Schema::hasColumn('restaurant_wallets', 'total_collected')) {
                $table->decimal('total_collected', 15, 2)->default(0)->after('balance');
            }
            if (!Schema::hasColumn('restaurant_wallets', 'total_withdrawn')) {
                $table->decimal('total_withdrawn', 15, 2)->default(0)->after('total_collected');
            }
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_wallets', function (Blueprint $table) {
            $table->dropColumn(['recipient_name', 'total_collected', 'total_withdrawn']);
        });
    }
};
