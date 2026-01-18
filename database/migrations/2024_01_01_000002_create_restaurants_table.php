<?php

use App\Enums\RestaurantStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('email')->unique();
            $table->string('phone', 20);
            $table->text('description')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Statut
            $table->string('status')->default(RestaurantStatus::PENDING->value);
            $table->timestamp('validated_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->text('suspension_reason')->nullable();
            
            // Abonnement
            $table->foreignId('current_plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->timestamp('subscription_ends_at')->nullable();
            $table->boolean('orders_blocked')->default(false);
            
            // Branding
            $table->string('logo_path')->nullable();
            $table->string('banner_path')->nullable();
            $table->string('primary_color', 7)->default('#f97316');
            $table->string('secondary_color', 7)->default('#1c1917');
            
            // Paramètres
            $table->string('currency', 3)->default('XOF');
            $table->string('timezone')->default('Africa/Abidjan');
            $table->json('opening_hours')->nullable();
            $table->unsignedInteger('min_order_amount')->default(0);
            $table->unsignedInteger('delivery_fee')->default(0);
            $table->unsignedInteger('delivery_radius_km')->nullable();
            $table->unsignedInteger('estimated_prep_time')->default(30); // minutes
            
            // Paiement Lygos
            $table->text('lygos_api_key')->nullable(); // encrypted
            $table->text('lygos_api_secret')->nullable(); // encrypted
            $table->boolean('lygos_enabled')->default(false);
            
            // Meta
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Index
            $table->index('status');
            $table->index('subscription_ends_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};

