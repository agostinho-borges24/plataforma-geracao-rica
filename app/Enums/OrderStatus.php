<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case AwaitingConfirmation = 'awaiting_confirmation'; // aguardando revisão do comprovativo (manual)
    case Paid = 'paid';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendente',
            self::AwaitingConfirmation => 'Aguardando confirmação',
            self::Paid => 'Pago',
            self::Rejected => 'Rejeitado',
            self::Cancelled => 'Cancelado',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Paid, self::Rejected, self::Cancelled], true);
    }
}