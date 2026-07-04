<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_commissions', function (Blueprint $table) {
            $table->index(['user_id', 'source_type', 'source_id', 'type'], 'crm_comm_idempotency_idx');
        });

        Schema::table('crm_teams', function (Blueprint $table) {
            $table->index('is_active', 'crm_teams_active_idx');
        });

        Schema::table('crm_verify_scans', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'crm_verify_user_time_idx');
        });
    }

    public function down(): void
    {
        Schema::table('crm_commissions', function (Blueprint $table) {
            $table->dropIndex('crm_comm_idempotency_idx');
        });

        Schema::table('crm_teams', function (Blueprint $table) {
            $table->dropIndex('crm_teams_active_idx');
        });

        Schema::table('crm_verify_scans', function (Blueprint $table) {
            $table->dropIndex('crm_verify_user_time_idx');
        });
    }
};
