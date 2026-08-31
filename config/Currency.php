<?php

return [

    // Moeda em que todos os preços são cadastrados (products.price)
    'base_currency' => 'AOA',

    // Moeda usada quando o país detectado/selecionado não tem moeda suportada
    // pelos gateways (Stripe/PayPal). Definir USD ou EUR mais tarde.
    'fallback_currency' => env('CURRENCY_FALLBACK', 'USD'),

    // Moedas que aceitamos cobrar diretamente (além da base AOA).
    // Se a moeda do país não estiver nesta lista, cai no fallback acima.
    // Ajustar conforme os países que o gateway escolhido realmente suporta.
    'supported_currencies' => [
        'USD', 'EUR', 'GBP', 'BRL', 'CAD', 'AUD', 'ZAR', 'CHF',
    ],

    // Moedas sem casas decimais (ex: Yen não tem "centavos").
    // Lista baseada nas zero-decimal currencies da Stripe.
    'zero_decimal_currencies' => [
        'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA',
        'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF',
    ],

    // Provider de taxas de câmbio (ExchangeRate-API — free tier)
    'exchange_rate_provider' => [
        'base_url' => env('EXCHANGERATE_API_BASE_URL', 'https://v6.exchangerate-api.com/v6'),
        'key' => env('EXCHANGERATE_API_KEY'),
    ],

];