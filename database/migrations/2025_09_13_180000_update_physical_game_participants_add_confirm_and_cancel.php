<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('physical_game_participants', function (Blueprint $table) {
            if (!Schema::hasColumn('physical_game_participants','confirmed')) {
                $table->boolean('confirmed')->default(false)->after('status');
            }
            if (!Schema::hasColumn('physical_game_participants','picked_canceled')) {
                $table->boolean('picked_canceled')->default(false)->after('picked_winner_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('physical_game_participants', function (Blueprint $table) {
            if (Schema::hasColumn('physical_game_participants','picked_canceled')) {
                $table->dropColumn('picked_canceled');
            }
            if (Schema::hasColumn('physical_game_participants','confirmed')) {
                $table->dropColumn('confirmed');
            }
        });
    }
};
