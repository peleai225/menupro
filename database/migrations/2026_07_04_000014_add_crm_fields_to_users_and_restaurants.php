<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'city')) {
                $table->string('city')->nullable()->after('phone');
            }
        });

        Schema::table('restaurants', function (Blueprint $table) {
            if (!Schema::hasColumn('restaurants', 'referred_by_user_id')) {
                $table->foreignId('referred_by_user_id')->nullable()->after('referred_by_agent_id')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('city');
        });
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropForeign(['referred_by_user_id']);
            $table->dropColumn('referred_by_user_id');
        });
    }
};
