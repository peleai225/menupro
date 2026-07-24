<?php
// database/migrations/2026_07_24_100002_add_waiter_token_to_restaurants_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->string('waiter_token', 32)->nullable()->unique()->after('staff_token');
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn('waiter_token');
        });
    }
};
