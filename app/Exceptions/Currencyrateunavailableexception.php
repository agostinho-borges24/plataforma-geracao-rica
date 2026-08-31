<?php

namespace App\Exceptions;

use RuntimeException;

class CurrencyRateUnavailableException extends RuntimeException
{
    public static function forPair(string $base, string $target): self
    {
        return new self("Taxa de câmbio indisponível para o par {$base} -> {$target}. Verifique se o UpdateExchangeRatesJob já rodou.");
    }
}