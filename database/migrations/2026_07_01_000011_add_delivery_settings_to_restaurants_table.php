<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            if (!Schema::hasColumn('restaurants', 'delivery_enabled')) {
                $table->boolean('delivery_enabled')->default(false)->after('estimated_prep_time');
            }
            if (!Schema::hasColumn('restaurants', 'delivery_zones')) {
                $table->text('delivery_zones')->nullable()->after('delivery_enabled');
            }
            if (!Schema::hasColumn('restaurants', 'cash_on_delivery')) {
                $table->boolean('cash_on_delivery')->default(true)->after('delivery_zones');
            }
            if (!Schema::hasColumn('restaurants', 'wave_business_enabled')) {
                $table->boolean('wave_business_enabled')->default(false)->after('cash_on_delivery');
            }
            if (!Schema::hasColumn('restaurants', 'wave_business_phone')) {
                $table->string('wave_business_phone', 20)->nullable()->after('wave_business_enabled');
            }
            if (!Schema::hasColumn('restaurants', 'tagline')) {
                $table->string('tagline', 200)->nullable()->after('description');
            }
            if (!Schema::hasColumn('restaurants', 'website')) {
                $table->string('website', 255)->nullable()->after('email');
            }
            if (!Schema::hasColumn('restaurants', 'company_name')) {
                $table->string('company_name', 255)->nullable()->after('wave_business_phone');
            }
            if (!Schema::hasColumn('restaurants', 'rccm')) {
                $table->string('rccm', 50)->nullable()->unique()->after('company_name');
            }
            if (!Schema::hasColumn('restaurants', 'rccm_document_path')) {
                $table->string('rccm_document_path')->nullable()->after('rccm');
            }
            if (!Schema::hasColumn('restaurants', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('rccm_document_path');
            }
            if (!Schema::hasColumn('restaurants', 'verified_by')) {
                $table->foreignId('verified_by')->nullable()->after('verified_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_enabled', 'delivery_zones', 'cash_on_delivery',
                'wave_business_enabled', 'wave_business_phone',
                'tagline', 'website', 'company_name', 'rccm',
                'rccm_document_path', 'verified_at', 'verified_by',
            ]);
        });
    }
};
