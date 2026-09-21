<x-app-layout>
    <div class="checkout-result">
        @if ($status === 'COMPLETED')
            <h1>Recebemos o teu pagamento!</h1>
            <p>Pedido <strong>{{ $order->order_number }}</strong> confirmado. Em instantes vais receber um e-mail com os links de acesso.</p>
        @else
            <h1>Ainda a processar…</h1>
            <p>O pagamento do pedido <strong>{{ $order->order_number }}</strong> está a ser confirmado. Isto pode levar alguns instantes.</p>
        @endif
    </div>
</x-app-layout>