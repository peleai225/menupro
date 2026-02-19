<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('welcome_token', 64)->nullable()->unique()->after('remember_token');
        });

        Schema::table('restaurants', function (Blueprint $table) {
            $table->foreignId('referred_by_agent_id')->nullable()->after('id')->constrained('commando_agents')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('welcome_token');
        });
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropForeign(['referred_by_agent_id']);
        });
    }
};
