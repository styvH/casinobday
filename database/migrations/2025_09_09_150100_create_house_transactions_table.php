<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('house_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('house_account_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // blackjack_bet_in, blackjack_payout_out, bet_commission_in, bet_payout_out, debt_increase, debt_repayment
            $table->bigInteger('amount_cents'); // positive in, negative out
            $table->bigInteger('balance_after_cents');
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('house_transactions');
    }
};
