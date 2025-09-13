<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reward_cycles', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('pending'); // pending|active|completed|canceled
            $table->unsignedSmallInteger('interval_minutes')->default(60);
            $table->unsignedInteger('repeat_total')->default(1);
            $table->unsignedInteger('repeat_remaining')->default(1);
            $table->timestamp('next_run_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reward_cycles');
    }
};
