<?php

use App\Enums\SubscriptionStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->restrictOnDelete();
            
            $table->string('status')->default(SubscriptionStatus::PENDING->value);
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            
            // Paiement
            $table->unsignedInteger('amount_paid'); // en centimes
            $table->string('payment_reference')->nullable();
            $table->string('payment_method')->nullable();
            $table->json('payment_metadata')->nullable();
            
            // Notifications
            $table->timestamp('reminder_sent_at')->nullable();
            $table->timestamp('expired_notification_sent_at')->nullable();
            
            $table->timestamps();
            
            // Index
            $table->index(['restaurant_id', 'status']);
            $table->index('ends_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};

