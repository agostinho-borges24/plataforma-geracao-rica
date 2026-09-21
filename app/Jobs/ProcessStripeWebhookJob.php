<?php

namespace App\Jobs;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\OrderFulfillmentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessStripeWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public int $backoff = 30;

    /**
     * @param  array  $event  payload completo do evento Stripe (já com assinatura validada)
     */
    public function __construct(
        public array $event,
    ) {}

    public function handle(OrderFulfillmentService $fulfillment): void
    {
        $type = $this->event['type'] ?? null;
        $session = $this->event['data']['object'] ?? [];

        // Outros eventos (payment_intent.*, charge.*, etc.) são ignorados por
        // agora — só nos importa a sessão de checkout ter sido paga.
        if (! in_array($type, ['checkout.session.completed', 'checkout.session.async_payment_succeeded'], true)) {
            return;
        }

        $orderNumber = $session['metadata']['order_number'] ?? $session['client_reference_id'] ?? null;

        if (! $orderNumber) {
            Log::warning('ProcessStripeWebhookJob: evento sem order_number.', ['event_type' => $type]);

            return;
        }

        $order = Order::where('order_number', $orderNumber)->first();

        if (! $order) {
            Log::warning('ProcessStripeWebhookJob: pedido não encontrado.', ['order_number' => $orderNumber]);

            return;
        }

        // Métodos de pagamento assíncronos (ex: boleto) podem disparar o evento
        // antes de o dinheiro cair — só prosseguimos se realmente estiver pago.
        if (($session['payment_status'] ?? null) !== 'paid') {
            return;
        }

        $order->payments()->latest()->first()?->update([
            'transaction_id' => $session['payment_intent'] ?? $session['id'] ?? null,
            'status' => PaymentStatus::Completed,
            'raw_payload' => $this->event,
        ]);

        $fulfillment->fulfill($order);
    }
}