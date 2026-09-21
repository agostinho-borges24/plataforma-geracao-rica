<?php

namespace App\Http\Controllers\Checkout;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessPaypalWebhookJob;
use App\Services\Payments\PaypalCheckoutService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaypalWebhookController extends Controller
{
    public function handle(Request $request, PaypalCheckoutService $paypal): Response
    {
        $event = $request->json()->all();

        $headers = [
            'paypal-auth-algo' => $request->header('paypal-auth-algo'),
            'paypal-cert-url' => $request->header('paypal-cert-url'),
            'paypal-transmission-id' => $request->header('paypal-transmission-id'),
            'paypal-transmission-sig' => $request->header('paypal-transmission-sig'),
            'paypal-transmission-time' => $request->header('paypal-transmission-time'),
        ];

        if (! $paypal->verifyWebhookSignature($headers, $event)) {
            return response('Assinatura inválida.', 400);
        }

        ProcessPaypalWebhookJob::dispatch($event);

        return response('OK', 200);
    }
}