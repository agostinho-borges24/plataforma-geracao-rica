<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyAdminNewManualPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Order $order,
    ) {}

    public function handle(): void
    {
        // TODO: trocar por notificação real (e-mail/Slack) quando definirmos
        // o canal preferido do admin. Por enquanto só regista no log para
        // não bloquear o fluxo de checkout.
        Log::info('Novo comprovativo de pagamento manual aguardando revisão.', [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'total_charged' => $this->order->total_charged,
            'currency' => $this->order->currency,
        ]);
    }
}