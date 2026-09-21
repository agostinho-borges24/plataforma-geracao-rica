<x-app-layout>
    <div class="checkout-result">
        <h1>Pagamento cancelado</h1>
        <p>O pagamento do pedido <strong>{{ $order->order_number }}</strong> foi cancelado. Nada foi cobrado.</p>
        <a href="{{ route('checkout.paypal.start', $order) }}" class="btn btn--primary">Tentar novamente</a>
    </div>
</x-app-layout>