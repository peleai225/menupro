<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            // Tax settings
            $table->decimal('tax_rate', 5, 2)->default(0)->after('delivery_fee'); // Pourcentage de taxe (ex: 18.00 pour 18%)
            $table->boolean('tax_included')->default(false)->after('tax_rate'); // Taxe incluse dans les prix ou ajoutée
            $table->string('tax_name')->default('TVA')->after('tax_included'); // Nom de la taxe (TVA, Tax, etc.)
            
            // Service fee settings
            $table->decimal('service_fee_rate', 5, 2)->default(0)->after('tax_name'); // Pourcentage de frais de service
            $table->unsignedInteger('service_fee_fixed')->default(0)->after('service_fee_rate'); // Frais de service fixe (en centimes)
            $table->boolean('service_fee_enabled')->default(false)->after('service_fee_fixed');
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn([
                'tax_rate',
                'tax_included',
                'tax_name',
                'service_fee_rate',
                'service_fee_fixed',
                'service_fee_enabled',
            ]);
        });
    }
};
