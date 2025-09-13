<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('house_accounts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('starting_balance_cents');
            $table->bigInteger('balance_cents');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('house_accounts');
    }
};
