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

class ProcessPaypalWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public int $backoff = 30;

    /**
     * @param  array  $event  payload completo do evento PayPal (já com assinatura validada)
     */
    public function __construct(
        public array $event,
    ) {}

    public function handle(OrderFulfillmentService $fulfillment): void
    {
        if (($this->event['event_type'] ?? null) !== 'PAYMENT.CAPTURE.COMPLETED') {
            return;
        }

        $resource = $this->event['resource'] ?? [];
        $orderNumber = $resource['custom_id'] ?? null;

        if (! $orderNumber) {
            Log::warning('ProcessPaypalWebhookJob: evento sem custom_id (order_number).');

            return;
        }

        $order = Order::where('order_number', $orderNumber)->first();

        if (! $order) {
            Log::warning('ProcessPaypalWebhookJob: pedido não encontrado.', ['order_number' => $orderNumber]);

            return;
        }

        $order->payments()->latest()->first()?->update([
            'status' => PaymentStatus::Completed,
            'raw_payload' => $this->event,
        ]);

        $fulfillment->fulfill($order);
    }
}