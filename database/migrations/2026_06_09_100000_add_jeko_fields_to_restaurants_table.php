<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->text('jeko_api_key')->nullable()->after('lygos_enabled');
            $table->text('jeko_api_key_id')->nullable()->after('jeko_api_key');
            $table->text('jeko_webhook_secret')->nullable()->after('jeko_api_key_id');
            $table->string('jeko_store_id', 100)->nullable()->after('jeko_webhook_secret');
            $table->boolean('jeko_enabled')->default(false)->after('jeko_store_id');
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn([
                'jeko_api_key',
                'jeko_api_key_id',
                'jeko_webhook_secret',
                'jeko_store_id',
                'jeko_enabled',
            ]);
        });
    }
};
