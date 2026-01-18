<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Notifications in-app pour les utilisateurs
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->index(['notifiable_type', 'notifiable_id', 'read_at']);
        });

        // Settings de notifications par restaurant
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            
            // Email
            $table->boolean('email_new_order')->default(true);
            $table->boolean('email_order_cancelled')->default(true);
            $table->boolean('email_low_stock')->default(true);
            $table->boolean('email_subscription_reminder')->default(true);
            
            // SMS (pour plus tard)
            $table->boolean('sms_new_order')->default(false);
            $table->boolean('sms_order_cancelled')->default(false);
            
            // Push (pour plus tard)
            $table->boolean('push_new_order')->default(true);
            $table->boolean('push_order_status')->default(true);
            
            $table->timestamps();
            
            $table->unique('restaurant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
        Schema::dropIfExists('notifications');
    }
};

