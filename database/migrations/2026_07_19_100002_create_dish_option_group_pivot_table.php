<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table pivot : un plat peut avoir plusieurs groupes d'options,
        // un groupe peut être partagé entre plusieurs plats du même restaurant.
        Schema::create('dish_option_group', function (Blueprint $table) {
            $table->foreignId('dish_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->foreignId('option_group_id')
                  ->constrained('dish_option_groups')
                  ->cascadeOnDelete();

            $table->primary(['dish_id', 'option_group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dish_option_group');
    }
};
