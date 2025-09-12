<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bet_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            // disponible, annonce, en_cours, ferme
            $table->string('status')->default('disponible');
            $table->decimal('margin', 5, 2)->default(0.90); // 0.00 - 1.00
            $table->bigInteger('min_bet_cents')->default(100000); // 1 000 € par défaut
            $table->bigInteger('max_bet_cents')->default(10000000); // 100 000 € par défaut
            $table->timestamps();
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bet_events');
    }
};
