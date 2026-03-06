<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->text('geniuspay_api_key')->nullable()->after('geniuspay_enabled');
            $table->text('geniuspay_api_secret')->nullable()->after('geniuspay_api_key');
            $table->string('geniuspay_webhook_secret', 255)->nullable()->after('geniuspay_api_secret');
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn(['geniuspay_api_key', 'geniuspay_api_secret', 'geniuspay_webhook_secret']);
        });
    }
};
