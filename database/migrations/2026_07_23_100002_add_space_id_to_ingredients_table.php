<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->foreignId('space_id')->nullable()->after('restaurant_id')
                  ->constrained('restaurant_spaces')->nullOnDelete();
            $table->index('space_id');
        });
    }

    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropForeign(['space_id']);
            $table->dropColumn('space_id');
        });
    }
};
