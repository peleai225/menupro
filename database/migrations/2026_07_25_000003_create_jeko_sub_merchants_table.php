<?php

use App\Enums\JekoSubMerchantStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jeko_sub_merchants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->unique()->constrained()->cascadeOnDelete();

            $table->string('status')->default(JekoSubMerchantStatus::PENDING->value);

            $table->string('legal_name');
            $table->string('business_type', 100)->nullable();
            $table->string('mobile_money', 20);
            $table->string('mobile_money_operator');
            $table->string('email')->nullable();

            $table->string('jeko_merchant_id')->unique()->nullable();
            $table->string('jeko_store_id')->nullable();
            $table->string('jeko_wallet_id')->nullable();
            $table->text('jeko_api_key')->nullable();

            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejected_reason')->nullable();

            $table->json('integration_metadata')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('jeko_merchant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jeko_sub_merchants');
    }
};
