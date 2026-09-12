<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            if (!Schema::hasColumn('deliveries', 'proof_photo_path')) {
                $table->string('proof_photo_path')->nullable()->after('delivered_at');
            }
            if (!Schema::hasColumn('deliveries', 'cancelled_by')) {
                $table->string('cancelled_by')->nullable()->after('cancellation_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $columns = array_filter(['proof_photo_path', 'cancelled_by'], fn($c) => Schema::hasColumn('deliveries', $c));
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
