<x-mail::message>
# Não conseguimos confirmar o teu pagamento

Olá {{ $order->user->name }},

Infelizmente não conseguimos validar o comprovativo enviado para o pedido **{{ $order->order_number }}**.

@if ($reason)
**Motivo:** {{ $reason }}
@endif

Por favor, verifica os dados do comprovativo e tenta novamente. Se achares que isto é um engano, responde a este e-mail que ajudamos a resolver.

<x-mail::button :url="$manualPaymentUrl">
Reenviar comprovativo
</x-mail::button>

Obrigado,<br>
{{ config('app.name') }}
</x-mail::message>