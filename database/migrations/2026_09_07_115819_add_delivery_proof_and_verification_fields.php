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
        Schema::table('deliveries', function (Blueprint $table) {
            $table->string('proof_photo_path')->nullable()->after('delivered_at');
            $table->string('cancelled_by')->nullable()->after('proof_photo_path'); // 'driver', 'restaurant', 'admin', 'customer'
            $table->text('cancellation_reason')->nullable()->after('cancelled_by');
            $table->timestamp('cancelled_at')->nullable()->after('cancellation_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropColumn(['proof_photo_path', 'cancelled_by', 'cancellation_reason', 'cancelled_at']);
        });
    }
};
