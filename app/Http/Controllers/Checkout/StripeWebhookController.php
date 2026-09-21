<?php

namespace App\Http\Controllers\Checkout;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessStripeWebhookJob;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent($payload, $signature, config('services.stripe.webhook_secret'));
        } catch (UnexpectedValueException|SignatureVerificationException) {
            return response('Assinatura inválida.', 400);
        }

        // Processa em fila: o Stripe exige resposta rápida (2xx) e queremos
        // retry automático via Horizon se o processamento falhar.
        ProcessStripeWebhookJob::dispatch($event->toArray());

        return response('OK', 200);
    }
}