<?php

return [

    // Dados exibidos ao cliente no pagamento manual (transferência / Multicaixa Express).
    // Preencher via .env — nunca commitar dados bancários reais direto no código.
    'manual' => [
        'bank_name' => env('PAYMENT_BANK_NAME'),
        'account_holder' => env('PAYMENT_ACCOUNT_HOLDER'),
        'iban' => env('PAYMENT_IBAN'),
        'account_number' => env('PAYMENT_ACCOUNT_NUMBER'),
        'multicaixa_express_number' => env('PAYMENT_MCX_NUMBER'),
    ],

];