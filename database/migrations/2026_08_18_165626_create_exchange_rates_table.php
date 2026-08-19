<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('base_currency', 3)->default('AOA');
            $table->string('target_currency', 3);
            $table->decimal('rate', 20, 8); // 1 AOA = X target_currency
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();

            // Guardamos sempre a taxa mais recente por par de moedas (upsert),
            // não um histórico. Se precisarmos de histórico depois, criamos
            // uma tabela separada exchange_rate_history.
            $table->unique(['base_currency', 'target_currency']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};