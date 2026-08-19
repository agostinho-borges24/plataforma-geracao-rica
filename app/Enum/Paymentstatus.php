<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Failed = 'failed';
    case Rejected = 'rejected'; // usado quando o admin rejeita o comprovativo manual

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendente',
            self::Completed => 'Concluído',
            self::Failed => 'Falhou',
            self::Rejected => 'Rejeitado',
        };
    }
}