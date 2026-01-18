<?php

use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('role')->default(UserRole::RESTAURANT_ADMIN->value)->after('phone');
            $table->foreignId('restaurant_id')->nullable()->after('role')->constrained()->nullOnDelete();
            $table->string('avatar_path')->nullable()->after('restaurant_id');
            $table->boolean('is_active')->default(true)->after('avatar_path');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            
            // Index
            $table->index('role');
            $table->index('restaurant_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['restaurant_id']);
            $table->dropForeign(['restaurant_id']);
            $table->dropColumn(['phone', 'role', 'restaurant_id', 'avatar_path', 'is_active', 'last_login_at']);
        });
    }
};

