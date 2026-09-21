<x-app-layout>
    <div class="checkout-result">
        <h1>Recebemos o teu pagamento!</h1>
        <p>Estamos a confirmar o pagamento do pedido <strong>{{ $order->order_number }}</strong>.</p>
        <p>Em instantes vais receber um e-mail com os links de acesso. Se não chegar em alguns minutos, verifica o spam ou contacta-nos.</p>
    </div>
</x-app-layout>