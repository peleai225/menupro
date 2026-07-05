<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('role', 'commando_agent')
            ->update(['role' => 'commercial']);
    }

    public function down(): void
    {
        // Non-reversible: we cannot distinguish which commercials were previously commando_agents
    }
};
