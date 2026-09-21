<?php

namespace App\Services\Payments;

use App\Models\Order;
use Stripe\Checkout\Session;
use Stripe\StripeClient;

class StripeCheckoutService
{
    protected StripeClient $client;

    public function __construct()
    {
        $this->client = new StripeClient(config('services.stripe.secret'));
    }

    public function createCheckoutSession(Order $order): Session
    {
        $order->loadMissing('items.product', 'user');

        $lineItems = $order->items->map(fn ($item) => [
            'quantity' => 1,
            'price_data' => [
                'currency' => strtolower($order->currency),
                'unit_amount' => $this->toMinorUnits((float) $item->price_charged, $order->currency),
                'product_data' => [
                    'name' => $item->product->title,
                ],
            ],
        ])->all();

        return $this->client->checkout->sessions->create([
            'mode' => 'payment',
            'line_items' => $lineItems,
            'customer_email' => $order->user->email,
            'client_reference_id' => $order->order_number,
            'metadata' => [
                'order_number' => $order->order_number,
                'order_id' => (string) $order->id,
            ],
            'success_url' => route('checkout.stripe.success', $order).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.stripe.cancel', $order),
        ]);
    }

    /**
     * Stripe espera o valor na menor unidade da moeda (ex: centavos para USD/EUR),
     * exceto nas moedas "zero-decimal" (ex: JPY), onde o valor já é a unidade inteira.
     */
    protected function toMinorUnits(float $amount, string $currency): int
    {
        $zeroDecimal = config('currency.zero_decimal_currencies', []);

        return in_array($currency, $zeroDecimal, true)
            ? (int) round($amount)
            : (int) round($amount * 100);
    }
}