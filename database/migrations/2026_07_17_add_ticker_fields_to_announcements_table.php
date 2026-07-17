<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->boolean('show_on_ticker')->default(false)->after('show_on_dashboard');
            $table->string('link_url', 500)->nullable()->after('content');
            $table->string('link_label', 100)->nullable()->after('link_url');
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['show_on_ticker', 'link_url', 'link_label']);
        });
    }
};
