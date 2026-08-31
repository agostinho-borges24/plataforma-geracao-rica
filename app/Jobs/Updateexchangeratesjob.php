<?php

namespace App\Jobs;

use App\Models\ExchangeRate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateExchangeRatesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60; // segundos entre tentativas

    public function handle(): void
    {
        $base = config('currency.base_currency');
        $apiKey = config('currency.exchange_rate_provider.key');
        $baseUrl = config('currency.exchange_rate_provider.base_url');

        if (! $apiKey) {
            Log::warning('UpdateExchangeRatesJob: EXCHANGERATE_API_KEY não configurada, job ignorado.');

            return;
        }

        // Moedas que realmente nos interessam: as suportadas + o fallback.
        // Não precisamos guardar as ~150 moedas que a API devolve.
        $targets = array_unique([
            ...config('currency.supported_currencies', []),
            config('currency.fallback_currency'),
        ]);

        $response = Http::timeout(15)
            ->retry(2, 500)
            ->get("{$baseUrl}/{$apiKey}/latest/{$base}");

        if ($response->failed() || $response->json('result') !== 'success') {
            Log::error('UpdateExchangeRatesJob: falha ao buscar taxas.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            // Deixa o job falhar de verdade para o Horizon registrar e tentar de novo
            $response->throw();

            return;
        }

        $rates = $response->json('conversion_rates', []);
        $fetchedAt = now();

        foreach ($targets as $currency) {
            if (! isset($rates[$currency])) {
                Log::warning("UpdateExchangeRatesJob: moeda {$currency} não veio na resposta da API.");

                continue;
            }

            ExchangeRate::updateOrCreate(
                ['base_currency' => $base, 'target_currency' => $currency],
                ['rate' => $rates[$currency], 'fetched_at' => $fetchedAt],
            );
        }

        Log::info('UpdateExchangeRatesJob: taxas atualizadas.', ['currencies' => $targets]);
    }
}