<?php

namespace App\Http\Controllers\Checkout;

use App\Enums\PaymentGateway;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Payments\StripeCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StripeCheckoutController extends Controller
{
    public function start(Order $order, StripeCheckoutService $stripe): RedirectResponse
    {
        abort_unless($order->payment_method === PaymentGateway::Stripe, 404);
        abort_if($order->isPaid(), 400, 'Este pedido já foi pago.');

        $session = $stripe->createCheckoutSession($order);

        return redirect($session->url);
    }

    /**
     * A confirmação real acontece via webhook (fonte da verdade). Esta
     * página só dá feedback ao usuário enquanto o webhook é processado.
     */
    public function success(Order $order): View
    {
        return view('checkout.stripe-success', ['order' => $order]);
    }

    public function cancel(Order $order): View
    {
        return view('checkout.stripe-cancel', ['order' => $order]);
    }
}