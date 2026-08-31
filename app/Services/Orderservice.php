<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentGateway;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Country;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        protected CurrencyConverterService $currencyConverter,
    ) {}

    /**
     * @param  array{name: string, email: string, whatsapp: string}  $contact
     * @param  Collection<int, Product>  $products
     */
    public function createFromCart(
        array $contact,
        Collection $products,
        Country $country,
        PaymentGateway $paymentMethod,
    ): Order {
        return DB::transaction(function () use ($contact, $products, $country, $paymentMethod) {
            $user = $this->findOrCreateGuestUser($contact, $country);
            $currency = $this->currencyConverter->resolveCurrencyForCountry($country);

            $totalBaseAoa = 0.0;
            $totalCharged = 0.0;
            $rateUsed = null;
            $itemsData = [];

            foreach ($products as $product) {
                // Converte cada item individualmente (não o total) para manter
                // consistência entre o que aparece por item e a soma final.
                $conversion = $this->currencyConverter->convert((float) $product->price, $currency);

                $totalBaseAoa += (float) $product->price;
                $totalCharged += $conversion['amount'];
                $rateUsed = $conversion['rate'];

                $itemsData[] = [
                    'product_id' => $product->id,
                    'price_base_aoa' => $product->price,
                    'price_charged' => $conversion['amount'],
                ];
            }

            $order = Order::create([
                'user_id' => $user->id,
                'status' => OrderStatus::Pending,
                'payment_method' => $paymentMethod,
                'whatsapp' => $contact['whatsapp'],
                'country_id' => $country->id,
                'currency' => $currency,
                'exchange_rate_used' => $rateUsed,
                'total_base_aoa' => round($totalBaseAoa, 2),
                'total_charged' => round($totalCharged, 2),
            ]);

            $order->items()->createMany($itemsData);

            // Pagamento nasce "pending" nos 3 casos. Para stripe/paypal, o
            // transaction_id e o status real chegam via webhook (próxima etapa).
            // Para manual, fica pending até o comprovativo ser revisado.
            $order->payments()->create([
                'gateway' => $paymentMethod,
                'status' => PaymentStatus::Pending,
                'amount' => $order->total_charged,
                'currency' => $order->currency,
            ]);

            return $order->fresh(['items.product', 'payments']);
        });
    }

    /**
     * Checkout "convidado": encontra o usuário pelo e-mail ou cria uma conta
     * nova com senha aleatória (o cliente pode definir uma senha depois via
     * "esqueci minha senha", ou o fluxo de primeiro acesso que criarmos).
     *
     * @param  array{name: string, email: string, whatsapp: string}  $contact
     */
    protected function findOrCreateGuestUser(array $contact, Country $country): User
    {
        return User::firstOrCreate(
            ['email' => $contact['email']],
            [
                'name' => $contact['name'],
                'password' => Hash::make(Str::random(40)),
                'role' => UserRole::Customer,
                'whatsapp' => $contact['whatsapp'],
                'country_id' => $country->id,
            ],
        );
    }
}