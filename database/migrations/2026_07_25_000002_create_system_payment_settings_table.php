<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_payment_settings', function (Blueprint $table) {
            $table->id();
            $table->string('gateway', 50)->unique();
            $table->boolean('is_active')->default(false);
            $table->enum('mode', ['sandbox', 'production'])->default('sandbox');

            // Credentials chiffrés
            $table->text('api_key')->nullable();
            $table->text('webhook_secret')->nullable();
            $table->string('merchant_id')->nullable();

            // Config JSON (timeouts, URLs custom, etc.)
            $table->json('config')->nullable();

            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_payment_settings');
    }
};
