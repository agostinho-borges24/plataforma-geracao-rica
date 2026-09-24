<?php

namespace App\Enums;

enum PaymentGateway: string
{
    case Stripe = 'stripe';
    case Paypal = 'paypal';
    case Manual = 'manual'; // transferência bancária / Multicaixa Express

    public function label(): string
    {
        return match ($this) {
            self::Stripe => 'Stripe',
            self::Paypal => 'PayPal',
            self::Manual => 'Transferência / Multicaixa Express',
        };
    }

    public function isAutomatic(): bool
    {
        return $this !== self::Manual;
    }
}