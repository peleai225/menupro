<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commando_agents', function (Blueprint $table) {
            $table->string('badge_id', 20)->nullable()->unique()->after('uuid');
            $table->unsignedBigInteger('balance_cents')->default(0)->after('approved_at');
        });

        foreach (DB::table('commando_agents')->whereNull('badge_id')->get() as $agent) {
            DB::table('commando_agents')->where('id', $agent->id)->update([
                'badge_id' => 'MP-' . $agent->id . '-' . strtoupper(Str::random(4)),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('commando_agents', function (Blueprint $table) {
            $table->dropColumn(['badge_id', 'balance_cents']);
        });
    }
};
