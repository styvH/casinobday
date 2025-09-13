<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reward_configs', function (Blueprint $table) {
            $table->id();
            // Store percent in basis points (1% = 100)
            $table->unsignedInteger('top3_percent_bp')->default(100);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reward_configs');
    }
};
