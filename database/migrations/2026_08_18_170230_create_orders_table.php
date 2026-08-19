<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // Ex: ORD-2026-000123

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // status: pending | awaiting_confirmation | paid | rejected | cancelled
            $table->string('status')->default('pending');

            // payment_method: stripe | paypal | manual
            $table->string('payment_method');

            // Snapshot dos dados usados para decidir moeda/país no momento da compra
            $table->string('whatsapp')->nullable();
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();

            // Moeda cobrada do cliente (AOA se Angola, ou moeda convertida/fallback)
            $table->string('currency', 3);

            // Taxa de câmbio usada (null se AOA, sem conversão)
            $table->decimal('exchange_rate_used', 20, 8)->nullable();

            // Totais: sempre guardamos o valor-base em AOA e o valor efetivamente cobrado
            $table->decimal('total_base_aoa', 12, 2);
            $table->decimal('total_charged', 12, 2);

            $table->timestamps();

            $table->index('status');
            $table->index('payment_method');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};