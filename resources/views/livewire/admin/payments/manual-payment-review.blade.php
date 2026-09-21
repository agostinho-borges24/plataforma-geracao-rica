<div class="admin-payment-review">
    <x-admin-nav />

    <h1>Pedido {{ $order->order_number }}</h1>

    <section class="payment-review__details">
        <h2>Cliente</h2>
        <p>{{ $order->user->name }} — {{ $order->user->email }}</p>
        <p>WhatsApp: {{ $order->whatsapp }} @if ($order->country) ({{ $order->country->name }}) @endif</p>

        <h2>Produtos</h2>
        <ul>
            @foreach ($order->items as $item)
                <li>
                    {{ $item->product->title }} —
                    {{ number_format((float) $item->price_charged, 2, ',', '.') }} {{ $order->currency }}
                </li>
            @endforeach
        </ul>

        <p><strong>Total: {{ number_format((float) $order->total_charged, 2, ',', '.') }} {{ $order->currency }}</strong></p>
    </section>

    <section class="payment-review__proof">
        <h2>Comprovativo</h2>
        @if ($this->proofUrl)
            <a href="{{ $this->proofUrl }}" target="_blank" class="btn btn--secondary">Ver / descarregar comprovativo</a>
        @else
            <p>Nenhum comprovativo anexado ainda.</p>
        @endif
    </section>

    <section class="payment-review__actions">
        <button type="button"
                wire:click="approve"
                wire:confirm="Confirmar aprovação? Isto liga o acesso e envia o e-mail ao cliente."
                class="btn btn--primary">
            Aprovar pagamento
        </button>

        <button type="button" wire:click="$set('showRejectForm', true)" class="btn btn--danger">
            Rejeitar pagamento
        </button>

        @if ($showRejectForm)
            <div class="reject-form">
                <label for="rejectReason">Motivo (opcional, enviado ao cliente por e-mail)</label>
                <textarea id="rejectReason" wire:model="rejectReason" rows="3"></textarea>
                @error('rejectReason') <span class="field-error">{{ $message }}</span> @enderror

                <button type="button" wire:click="reject" class="btn btn--danger">Confirmar rejeição</button>
                <button type="button" wire:click="$set('showRejectForm', false)">Cancelar</button>
            </div>
        @endif
    </section>
</div>