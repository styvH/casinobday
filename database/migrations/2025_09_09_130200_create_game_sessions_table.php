<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('game_type'); // blackjack, pari, roulette, etc.
            $table->string('status')->default('active'); // active, closed, canceled
            $table->bigInteger('stake_cents')->default(0); // mise engagée
            $table->bigInteger('potential_win_cents')->default(0);
            $table->json('state')->nullable(); // snapshot JSON du jeu
            $table->timestamps();
            $table->index(['user_id','status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_sessions');
    }
};
