<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bet_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bet_choice_id')->constrained()->cascadeOnDelete();
            $table->bigInteger('amount_cents');
            $table->decimal('odds', 8, 2)->default(1.00); // odds locked at placement time
            $table->string('status')->default('open'); // open, won, lost, refunded
            $table->bigInteger('potential_win_cents')->default(0);
            $table->string('reference')->nullable();
            $table->timestamps();
            $table->index(['user_id','status']);
            $table->index(['bet_event_id','status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bets');
    }
};
