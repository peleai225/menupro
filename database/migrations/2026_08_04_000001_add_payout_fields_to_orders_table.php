<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payout_status')->nullable()->after('payment_status')
                  ->comment('null=non concerné, pending=à reverser, completed=reversé, failed=échoué, manual=fait manuellement');
            $table->timestamp('payout_at')->nullable()->after('payout_status');
            $table->string('payout_reference')->nullable()->after('payout_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payout_status', 'payout_at', 'payout_reference']);
        });
    }
};
