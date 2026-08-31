<?php

namespace App\Livewire\Checkout;

use App\Enums\PaymentGateway;
use App\Exceptions\CurrencyRateUnavailableException;
use App\Models\Country;
use App\Services\CartService;
use App\Services\CurrencyConverterService;
use App\Services\OrderService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CheckoutPage extends Component
{
    // Passo 1: contacto
    public string $name = '';
    public string $email = '';
    public string $whatsapp = '';

    // País / moeda
    public ?int $countryId = null;
    public bool $countryChecked = false;
    public bool $needsManualCountry = false;

    // Resultado da conversão (calculado depois que o país é conhecido)
    public ?string $resolvedCurrency = null;
    public ?float $convertedTotal = null;
    public ?float $exchangeRate = null;
    public bool $wasConverted = false;

    // Passo 2: pagamento
    public string $paymentMethod = '';

    public int $step = 1;

    public function mount(CartService $cart): void
    {
        if ($cart->isEmpty()) {
            $this->redirect(route('cart.index'));
        }
    }

    #[Computed]
    public function cartItems(): Collection
    {
        return app(CartService::class)->items();
    }

    #[Computed]
    public function totalBaseAoa(): float
    {
        return app(CartService::class)->totalBaseAoa();
    }

    #[Computed]
    public function countries(): Collection
    {
        return Country::query()->active()->orderBy('name')->get();
    }

    /**
     * Disparado quando o campo whatsapp perde o foco (wire:change no input).
     */
    public function detectCountry(CurrencyConverterService $converter): void
    {
        $this->countryChecked = true;
        $this->resetErrorBag();

        if (blank($this->whatsapp)) {
            return;
        }

        $country = $converter->detectCountryFromWhatsapp($this->whatsapp);

        if (! $country) {
            $this->needsManualCountry = true;
            $this->countryId = null;
            $this->resolvedCurrency = null;

            return;
        }

        $this->needsManualCountry = false;
        $this->countryId = $country->id;
        $this->calculateConversion($converter);
    }

    /**
     * Disparado quando o usuário escolhe o país manualmente no dropdown
     * (fallback de quando a detecção automática falha).
     */
    public function updatedCountryId(CurrencyConverterService $converter): void
    {
        if ($this->countryId) {
            $this->needsManualCountry = false;
            $this->calculateConversion($converter);
        }
    }

    protected function calculateConversion(CurrencyConverterService $converter): void
    {
        $country = Country::find($this->countryId);

        if (! $country) {
            return;
        }

        try {
            $result = $converter->convertForCountry($this->totalBaseAoa, $country);
        } catch (CurrencyRateUnavailableException) {
            $this->addError('checkout', 'Não foi possível calcular a conversão de moeda agora. Tenta novamente em instantes.');

            return;
        }

        $this->resolvedCurrency = $result['currency'];
        $this->convertedTotal = $result['amount'];
        $this->exchangeRate = $result['rate'];
        $this->wasConverted = $result['converted'];

        // Pagamento manual (Multicaixa/transferência) só faz sentido em AOA.
        if ($this->wasConverted && $this->paymentMethod === PaymentGateway::Manual->value) {
            $this->paymentMethod = '';
        }
    }

    #[Computed]
    public function availablePaymentMethods(): array
    {
        $methods = [PaymentGateway::Stripe, PaymentGateway::Paypal];

        if ($this->resolvedCurrency === config('currency.base_currency')) {
            array_unshift($methods, PaymentGateway::Manual);
        }

        return $methods;
    }

    public function goToPayment(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'min:3', 'max:150'],
            'email' => ['required', 'email', 'max:255'],
            'whatsapp' => ['required', 'string'],
            'countryId' => ['required', 'exists:countries,id'],
        ], attributes: [
            'name' => 'nome',
            'email' => 'e-mail',
            'whatsapp' => 'whatsapp',
            'countryId' => 'país',
        ]);

        if (! $this->resolvedCurrency) {
            $this->addError('checkout', 'Selecione um país válido para continuar.');

            return;
        }

        $this->step = 2;
    }

    public function backToContact(): void
    {
        $this->step = 1;
    }

    public function placeOrder(OrderService $orderService): void
    {
        $this->validate([
            'paymentMethod' => ['required', 'in:stripe,paypal,manual'],
        ], attributes: [
            'paymentMethod' => 'forma de pagamento',
        ]);

        $country = Country::findOrFail($this->countryId);
        $gateway = PaymentGateway::from($this->paymentMethod);
        $products = $this->cartItems;

        $order = $orderService->createFromCart(
            contact: ['name' => $this->name, 'email' => $this->email, 'whatsapp' => $this->whatsapp],
            products: $products,
            country: $country,
            paymentMethod: $gateway,
        );

        app(CartService::class)->clear();

        match ($gateway) {
            PaymentGateway::Manual => $this->redirect(route('checkout.manual', $order)),
            // Integração real de Stripe/PayPal é a próxima etapa — por ora,
            // essas rotas são placeholders (ver routes/shop.php).
            PaymentGateway::Stripe => $this->redirect(route('checkout.stripe.start', $order)),
            PaymentGateway::Paypal => $this->redirect(route('checkout.paypal.start', $order)),
        };
    }
}