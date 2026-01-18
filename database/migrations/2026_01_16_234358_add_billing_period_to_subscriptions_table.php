<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('billing_period')->default('monthly')->after('amount_paid'); // 'monthly', 'quarterly', 'semiannual', 'annual'
            $table->unsignedInteger('discount_percentage')->default(0)->after('billing_period'); // Pourcentage de réduction (0-100)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['billing_period', 'discount_percentage']);
        });
    }
};
