<x-mail::message>
# A tua compra foi confirmada!

Olá {{ $order->user->name }},

Obrigado pela tua compra ({{ $order->order_number }}). Aqui estão os teus produtos e os respetivos links de acesso:

@foreach ($order->items as $item)
## {{ $item->product->title }}

@forelse ($item->product->accessLinks as $link)
<x-mail::button :url="$link->url">
{{ $link->label }}
</x-mail::button>
@empty
_Link de acesso ainda não disponível — vamos enviar em breve._
@endforelse

@endforeach

Guarda este e-mail para aceder aos teus conteúdos sempre que precisares.

Qualquer dúvida, basta responder a este e-mail.

Obrigado,<br>
{{ config('app.name') }}
</x-mail::message>