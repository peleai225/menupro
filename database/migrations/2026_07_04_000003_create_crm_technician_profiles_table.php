<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_technician_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('speciality')->nullable();
            $table->string('zone_geographique')->nullable();
            $table->boolean('disponible')->default(true);
            $table->foreignId('team_id')->nullable()->constrained('crm_teams')->nullOnDelete();
            $table->json('certifications')->nullable();
            $table->timestamps();

            $table->index('team_id');
            $table->index('disponible');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_technician_profiles');
    }
};
