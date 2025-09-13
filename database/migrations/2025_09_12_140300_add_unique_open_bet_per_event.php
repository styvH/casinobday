<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bets', function (Blueprint $table) {
            // Ensure one open bet per user per event
            $table->unique(['user_id','bet_event_id','status'], 'bets_user_event_status_unique');
        });
    }

    public function down(): void
    {
        Schema::table('bets', function (Blueprint $table) {
            $table->dropUnique('bets_user_event_status_unique');
        });
    }
};
