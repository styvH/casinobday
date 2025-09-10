<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            // Store balance in cents to avoid float issues. 10 000 € default => 1 000 000 cents
            $table->bigInteger('balance_cents')->default(1000000);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_accounts');
    }
};
