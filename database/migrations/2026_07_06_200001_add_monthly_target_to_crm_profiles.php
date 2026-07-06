<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('crm_commercial_profiles', function (Blueprint $table) {
            $table->unsignedInteger('monthly_target')->default(5)->after('specialty')
                ->comment('Objectif mensuel en nombre de conversions');
        });
        Schema::table('crm_technician_profiles', function (Blueprint $table) {
            $table->unsignedInteger('monthly_target')->default(8)->after('specialty')
                ->comment('Objectif mensuel en nombre d\'installations');
        });
    }
    public function down(): void
    {
        Schema::table('crm_commercial_profiles', fn($t) => $t->dropColumn('monthly_target'));
        Schema::table('crm_technician_profiles', fn($t) => $t->dropColumn('monthly_target'));
    }
};
