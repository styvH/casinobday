<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('physical_game_participants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('physical_game_id');
            $table->unsignedBigInteger('user_id');
            $table->string('status')->default('joined'); // joined|left|paid|refunded
            $table->unsignedBigInteger('picked_winner_id')->nullable(); // selection by participant
            $table->timestamps();

            $table->unique(['physical_game_id','user_id']);
            $table->foreign('physical_game_id')->references('id')->on('physical_games')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('picked_winner_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('physical_game_participants');
    }
};
