<div class="manual-payment">
    <h1>Pedido {{ $order->order_number }} — Pagamento por transferência / Multicaixa Express</h1>

    <p>
        Total a pagar: <strong>{{ number_format((float) $order->total_charged, 2, ',', '.') }} Kz</strong>
    </p>

    <div class="manual-payment__bank-details">
        <h2>Dados para pagamento</h2>
        <ul>
            <li><strong>Banco:</strong> {{ $bank['bank_name'] }}</li>
            <li><strong>Titular:</strong> {{ $bank['account_holder'] }}</li>
            <li><strong>IBAN:</strong> {{ $bank['iban'] }}</li>
            <li><strong>Nº de conta:</strong> {{ $bank['account_number'] }}</li>
            <li><strong>Multicaixa Express:</strong> {{ $bank['multicaixa_express_number'] }}</li>
        </ul>
        <p><small>Use o número do pedido ({{ $order->order_number }}) como referência, se possível.</small></p>
    </div>

    @if ($submitted)
        <div class="alert alert--success">
            <p>Comprovativo recebido! O seu pedido está aguardando confirmação — normalmente revisamos em até 24h.</p>
            <p>Assim que for aprovado, enviamos os links de acesso para o seu e-mail.</p>
        </div>
    @else
        <form wire:submit="submitProof" class="manual-payment__upload">
            <h2>Enviar comprovativo</h2>

            <label for="proof">Comprovativo (imagem ou PDF, até 5MB)</label>
            <input type="file" id="proof" wire:model="proof" accept=".jpg,.jpeg,.png,.pdf">
            @error('proof') <span class="field-error">{{ $message }}</span> @enderror

            <div wire:loading wire:target="proof">A carregar ficheiro…</div>

            <button type="submit" class="btn btn--primary" wire:loading.attr="disabled" wire:target="submitProof">
                <span wire:loading.remove wire:target="submitProof">Enviar comprovativo</span>
                <span wire:loading wire:target="submitProof">A enviar…</span>
            </button>
        </form>
    @endif
</div>