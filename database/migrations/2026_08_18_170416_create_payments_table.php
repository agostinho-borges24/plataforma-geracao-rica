<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            // gateway: stripe | paypal | manual
            $table->string('gateway');

            // status: pending | completed | failed | rejected
            $table->string('status')->default('pending');

            // transaction_id: id retornado pelo Stripe/PayPal. Null no caso manual.
            $table->string('transaction_id')->nullable();

            $table->decimal('amount', 12, 2);
            $table->string('currency', 3);

            // Payload bruto do webhook, útil pra auditoria/debug
            $table->json('raw_payload')->nullable();

            // Campos exclusivos do pagamento manual (Multicaixa Express/transferência)
            $table->string('proof_file_path')->nullable(); // comprovativo enviado pelo usuário
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->index('gateway');
            $table->index('status');
            $table->index('transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};