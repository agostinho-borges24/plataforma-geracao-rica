<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Jobs\SendPaymentRejectedEmailJob;
use App\Jobs\SendPurchaseConfirmationEmailJob;
use App\Models\Order;
use App\Models\OrderAccessGrant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderFulfillmentService
{
    /**
     * Marca o pedido como pago, libera o acesso a cada produto comprado e
     * dispara o e-mail de confirmação. É o ÚNICO ponto que deve fazer isso —
     * tanto a aprovação manual quanto os futuros webhooks de Stripe/PayPal
     * devem chamar este método, nunca duplicar a lógica.
     *
     * Idempotente: se o pedido já estiver "paid", não faz nada (evita
     * liberar acesso duas vezes ou reenviar o e-mail).
     */
    public function fulfill(Order $order): void
    {
        $alreadyPaid = DB::transaction(function () use ($order) {
            $order->refresh();

            if ($order->status === OrderStatus::Paid) {
                return true;
            }

            $order->update(['status' => OrderStatus::Paid]);

            foreach ($order->items as $item) {
                OrderAccessGrant::firstOrCreate(
                    ['order_id' => $order->id, 'product_id' => $item->product_id],
                    ['user_id' => $order->user_id, 'granted_at' => now()],
                );
            }

            return false;
        });

        if ($alreadyPaid) {
            return;
        }

        // Disparado depois da transação já ter commitado — evita o job
        // rodar antes dos dados estarem realmente gravados.
        SendPurchaseConfirmationEmailJob::dispatch($order);
    }

    public function approveManualPayment(Order $order, User $admin): void
    {
        abort_unless($order->isManualPayment(), 400, 'Este pedido não usa pagamento manual.');

        $payment = $order->payments()->latest()->firstOrFail();

        $payment->update([
            'status' => PaymentStatus::Completed,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        $this->fulfill($order);
    }

    public function rejectManualPayment(Order $order, User $admin, ?string $reason = null): void
    {
        abort_unless($order->isManualPayment(), 400, 'Este pedido não usa pagamento manual.');

        $payment = $order->payments()->latest()->firstOrFail();

        $payment->update([
            'status' => PaymentStatus::Rejected,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        $order->update(['status' => OrderStatus::Rejected]);

        SendPaymentRejectedEmailJob::dispatch($order, $reason);
    }
}