<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('physical_games', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('betmaster_id')->nullable();
            $table->string('status')->default('active'); // active|completed|canceled
            $table->integer('stake_cents'); // per-player stake (euros * 100)
            $table->integer('commission_bp')->default(1000); // 10% = 1000 bp
            $table->integer('pot_cents')->default(0); // expected payout pot (after commission)
            $table->unsignedBigInteger('winner_id')->nullable();
            $table->json('state')->nullable(); // extra data if needed
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('betmaster_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('winner_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('physical_games');
    }
};
