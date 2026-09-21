<div class="checkout">
    <h1>Finalizar compra</h1>

    <div class="checkout__summary">
        <ul>
            @foreach ($this->cartItems as $product)
                <li wire:key="checkout-item-{{ $product->id }}">
                    {{ $product->title }} — {{ number_format((float) $product->price, 2, ',', '.') }} Kz
                </li>
            @endforeach
        </ul>
        <strong>Total (Kz): {{ number_format($this->totalBaseAoa, 2, ',', '.') }} Kz</strong>
    </div>

    @error('checkout')
        <div class="alert alert--error">{{ $message }}</div>
    @enderror

    {{-- ==================== PASSO 1: CONTACTO E MOEDA ==================== --}}
    @if ($step === 1)
        <form wire:submit="goToPayment" class="checkout__step">
            <h2>1. Os seus dados</h2>

            <label for="name">Nome completo</label>
            <input type="text" id="name" wire:model="name">
            @error('name') <span class="field-error">{{ $message }}</span> @enderror

            <label for="email">E-mail</label>
            <input type="email" id="email" wire:model="email">
            @error('email') <span class="field-error">{{ $message }}</span> @enderror

            <label for="whatsapp">WhatsApp (com indicativo, ex: +244923456789)</label>
            <input type="text" id="whatsapp" wire:model="whatsapp" wire:change="detectCountry" placeholder="+244...">
            @error('whatsapp') <span class="field-error">{{ $message }}</span> @enderror

            @if ($countryChecked)
                @if ($needsManualCountry)
                    <div class="checkout__manual-country">
                        <p>Não conseguimos identificar o país automaticamente. Selecione manualmente:</p>
                        <label for="countryId">País</label>
                        <select id="countryId" wire:model="countryId">
                            <option value="">Selecione…</option>
                            @foreach ($this->countries as $country)
                                <option value="{{ $country->id }}">{{ $country->name }}</option>
                            @endforeach
                        </select>
                        @error('countryId') <span class="field-error">{{ $message }}</span> @enderror
                    </div>
                @elseif ($resolvedCurrency)
                    <div class="checkout__price-preview">
                        @if ($wasConverted)
                            <p>
                                Valor convertido para <strong>{{ $resolvedCurrency }}</strong>:
                                <strong>{{ number_format($convertedTotal, 2, ',', '.') }} {{ $resolvedCurrency }}</strong>
                                <br>
                                <small>(taxa aplicada: 1 AOA = {{ number_format($exchangeRate, 6, ',', '.') }} {{ $resolvedCurrency }})</small>
                            </p>
                        @else
                            <p>Pagamento em Kwanza — sem conversão.</p>
                        @endif
                    </div>
                @endif
            @endif

            <button type="submit" class="btn btn--primary">Continuar para pagamento</button>
        </form>

    {{-- ==================== PASSO 2: FORMA DE PAGAMENTO ==================== --}}
    @else
        <form wire:submit="placeOrder" class="checkout__step">
            <h2>2. Forma de pagamento</h2>

            <p>
                Total a pagar:
                <strong>
                    {{ number_format($convertedTotal ?? $this->totalBaseAoa, 2, ',', '.') }}
                    {{ $resolvedCurrency ?? 'AOA' }}
                </strong>
            </p>

            <div class="checkout__payment-methods">
                @foreach ($this->availablePaymentMethods as $method)
                    <label class="payment-method-option">
                        <input type="radio" wire:model="paymentMethod" value="{{ $method->value }}">
                        {{ $method->label() }}
                    </label>
                @endforeach
            </div>
            @error('paymentMethod') <span class="field-error">{{ $message }}</span> @enderror

            <div class="checkout__actions">
                <button type="button" wire:click="backToContact" class="btn btn--secondary">Voltar</button>
                <button type="submit" class="btn btn--primary" wire:loading.attr="disabled" wire:target="placeOrder">
                    <span wire:loading.remove wire:target="placeOrder">Confirmar pedido</span>
                    <span wire:loading wire:target="placeOrder">A processar…</span>
                </button>
            </div>
        </form>
    @endif
</div>