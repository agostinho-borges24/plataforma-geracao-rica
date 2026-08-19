<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'base_currency',
        'target_currency',
        'rate',
        'fetched_at',
    ];

    protected $casts = [
        'rate' => 'decimal:8',
        'fetched_at' => 'datetime',
    ];

    /**
     * Busca (ou cria) a taxa em cache para um par de moedas.
     * Usado pelo CurrencyConverterService — nunca chama API externa aqui.
     */
    public static function forPair(string $base, string $target): ?self
    {
        return static::where('base_currency', $base)
            ->where('target_currency', $target)
            ->first();
    }
}