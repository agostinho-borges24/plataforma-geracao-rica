<?php

namespace App\Http\Controllers\Checkout;

use App\Enums\PaymentGateway;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderFulfillmentService;
use App\Services\Payments\PaypalCheckoutService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaypalCheckoutController extends Controller
{
    public function start(Order $order, PaypalCheckoutService $paypal): RedirectResponse
    {
        abort_unless($order->payment_method === PaymentGateway::Paypal, 404);
        abort_if($order->isPaid(), 400, 'Este pedido já foi pago.');

        $result = $paypal->createOrder($order);

        abort_if(! $result['approve_url'], 502, 'Não foi possível iniciar o pagamento PayPal.');

        // Guarda o id da order PayPal para conseguirmos capturar no retorno.
        $order->payments()->latest()->first()?->update(['transaction_id' => $result['id']]);

        return redirect($result['approve_url']);
    }

    /**
     * PayPal redireciona o cliente de volta pra cá após aprovação, com
     * ?token={paypal_order_id}. Capturamos aqui como confirmação rápida,
     * mas o webhook (ProcessPaypalWebhookJob) é o backup/fonte da verdade
     * caso o cliente feche a aba antes de completar o retorno.
     */
    public function return(
        Order $order,
        Request $request,
        PaypalCheckoutService $paypal,
        OrderFulfillmentService $fulfillment,
    ): View {
        $paypalOrderId = $request->query('token');

        abort_unless($paypalOrderId, 400);

        $capture = $paypal->captureOrder($paypalOrderId);
        $status = $capture['status'] ?? null;

        if ($status === 'COMPLETED' && ! $order->isPaid()) {
            $order->payments()->latest()->first()?->update([
                'transaction_id' => $paypalOrderId,
                'status' => PaymentStatus::Completed,
                'raw_payload' => $capture,
            ]);

            $fulfillment->fulfill($order);
        }

        return view('checkout.paypal-return', ['order' => $order->fresh(), 'status' => $status]);
    }

    public function cancel(Order $order): View
    {
        return view('checkout.paypal-cancel', ['order' => $order]);
    }
}