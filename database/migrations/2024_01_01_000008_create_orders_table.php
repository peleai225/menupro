<?php

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            
            // Référence unique
            $table->string('reference')->unique();
            
            // Client
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone', 20);
            
            // Type et statut
            $table->string('type')->default(OrderType::TAKEAWAY->value);
            $table->string('status')->default(OrderStatus::DRAFT->value);
            
            // Montants (en centimes)
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('delivery_fee')->default(0);
            $table->unsignedInteger('discount_amount')->default(0);
            $table->unsignedInteger('total');
            
            // Livraison
            $table->text('delivery_address')->nullable();
            $table->string('delivery_city')->nullable();
            $table->text('delivery_instructions')->nullable();
            $table->timestamp('scheduled_at')->nullable(); // Commande programmée
            
            // Sur place
            $table->string('table_number')->nullable();
            
            // Paiement
            $table->string('payment_status')->default(PaymentStatus::PENDING->value);
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->json('payment_metadata')->nullable();
            $table->timestamp('paid_at')->nullable();
            
            // Notes
            $table->text('customer_notes')->nullable();
            $table->text('internal_notes')->nullable();
            
            // Timing
            $table->unsignedInteger('estimated_prep_time')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('preparing_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            
            $table->timestamps();
            
            // Index
            $table->index(['restaurant_id', 'status']);
            $table->index(['restaurant_id', 'created_at']);
            $table->index('customer_email');
            $table->index('customer_phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

