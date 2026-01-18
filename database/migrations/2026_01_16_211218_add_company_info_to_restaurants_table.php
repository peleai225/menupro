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
        Schema::table('restaurants', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('name');
            $table->string('rccm')->nullable()->unique()->after('company_name');
            $table->string('rccm_document_path')->nullable()->after('rccm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn(['company_name', 'rccm', 'rccm_document_path']);
        });
    }
};
