<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('orders', 'verification_code')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('verification_code', 4)->nullable()->after('delivery_instructions');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'verification_code')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('verification_code');
            });
        }
    }
};
