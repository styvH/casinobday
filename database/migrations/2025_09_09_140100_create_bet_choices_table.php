<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bet_choices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bet_event_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('label');
            $table->unsignedBigInteger('participants_count')->default(0);
            $table->timestamps();

            $table->unique(['bet_event_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bet_choices');
    }
};
