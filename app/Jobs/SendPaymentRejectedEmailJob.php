<?php

namespace App\Jobs;

use App\Mail\PaymentRejectedMail;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendPaymentRejectedEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public Order $order,
        public ?string $reason = null,
    ) {}

    public function handle(): void
    {
        $this->order->loadMissing('user');

        Mail::to($this->order->user->email)->send(
            new PaymentRejectedMail($this->order, $this->reason),
        );
    }
}