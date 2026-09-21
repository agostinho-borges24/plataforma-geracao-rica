<?php

namespace App\Services\Payments;

use App\Models\Order;
use Illuminate\Support\Str;

class PaypalCheckoutService
{
    public function __construct(
        protected PaypalClientService $client,
    ) {}

    /**
     * @return array{id: string, approve_url: string|null}
     */
    public function createOrder(Order $order): array
    {
        $order->loadMissing('items.product');

        $total = number_format((float) $order->total_charged, 2, '.', '');

        $response = $this->client->client()->post('/v2/checkout/orders', [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => $order->order_number,
                'custom_id' => $order->order_number,
                'amount' => [
                    'currency_code' => $order->currency,
                    'value' => $total,
                    'breakdown' => [
                        'item_total' => [
                            'currency_code' => $order->currency,
                            'value' => $total,
                        ],
                    ],
                ],
                'items' => $order->items->map(fn ($item) => [
                    'name' => Str::limit($item->product->title, 120, ''),
                    'quantity' => '1',
                    'unit_amount' => [
                        'currency_code' => $order->currency,
                        'value' => number_format((float) $item->price_charged, 2, '.', ''),
                    ],
                ])->all(),
            ]],
            'application_context' => [
                'brand_name' => config('app.name'),
                'return_url' => route('checkout.paypal.return', $order),
                'cancel_url' => route('checkout.paypal.cancel', $order),
                'user_action' => 'PAY_NOW',
            ],
        ])->throw()->json();

        $approveUrl = collect($response['links'] ?? [])->firstWhere('rel', 'approve')['href'] ?? null;

        return [
            'id' => $response['id'],
            'approve_url' => $approveUrl,
        ];
    }

    public function captureOrder(string $paypalOrderId): array
    {
        return $this->client->client()
            ->post("/v2/checkout/orders/{$paypalOrderId}/capture")
            ->throw()
            ->json();
    }

    /**
     * @param  array<string, string|null>  $headers  cabeçalhos paypal-* da requisição do webhook
     */
    public function verifyWebhookSignature(array $headers, array $event): bool
    {
        if (! config('services.paypal.webhook_id')) {
            return false;
        }

        $response = $this->client->client()->post('/v1/notifications/verify-webhook-signature', [
            'auth_algo' => $headers['paypal-auth-algo'] ?? null,
            'cert_url' => $headers['paypal-cert-url'] ?? null,
            'transmission_id' => $headers['paypal-transmission-id'] ?? null,
            'transmission_sig' => $headers['paypal-transmission-sig'] ?? null,
            'transmission_time' => $headers['paypal-transmission-time'] ?? null,
            'webhook_id' => config('services.paypal.webhook_id'),
            'webhook_event' => $event,
        ]);

        return $response->successful() && $response->json('verification_status') === 'SUCCESS';
    }
}