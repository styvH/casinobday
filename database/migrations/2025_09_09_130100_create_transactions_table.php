<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // deposit, bet_place, bet_win, bet_loss, transfer_out, transfer_in, adjustment, blackjack_win, blackjack_loss
            $table->bigInteger('amount_cents'); // positive or negative
            $table->bigInteger('balance_after_cents');
            $table->string('reference')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['user_id','created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
