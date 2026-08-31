<?php

namespace App\Services;

use App\Exceptions\CurrencyRateUnavailableException;
use App\Models\Country;
use App\Models\ExchangeRate;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;

class CurrencyConverterService
{
    protected PhoneNumberUtil $phoneUtil;

    public function __construct()
    {
        $this->phoneUtil = PhoneNumberUtil::getInstance();
    }

    /**
     * Tenta identificar o país a partir de um número de WhatsApp (formato E.164, ex: +244923456789).
     * Retorna null se o número for inválido ou o país não existir/estiver inativo na tabela `countries`
     * — nesses casos, o front-end deve pedir para o usuário selecionar o país manualmente.
     */
    public function detectCountryFromWhatsapp(string $whatsapp): ?Country
    {
        try {
            $parsed = $this->phoneUtil->parse($whatsapp, null);
        } catch (NumberParseException) {
            return null;
        }

        if (! $this->phoneUtil->isValidNumber($parsed)) {
            return null;
        }

        $isoCode = $this->phoneUtil->getRegionCodeForNumber($parsed); // ex: "AO", "PT", "BR"

        if (! $isoCode) {
            return null;
        }

        return Country::active()->where('iso_code', $isoCode)->first();
    }

    /**
     * Decide qual moeda cobrar para um determinado país:
     * - Angola -> AOA (moeda-base, sem conversão)
     * - País com moeda na lista de suportadas -> moeda do país
     * - País com moeda não suportada -> fallback (USD/EUR, definido em config/currency.php)
     */
    public function resolveCurrencyForCountry(Country $country): string
    {
        $base = config('currency.base_currency');

        if ($country->currency_code === $base) {
            return $base;
        }

        $supported = config('currency.supported_currencies');

        return in_array($country->currency_code, $supported, true)
            ? $country->currency_code
            : config('currency.fallback_currency');
    }

    /**
     * Converte um valor em AOA para a moeda de destino, usando a taxa em cache
     * (nunca chama a API externa aqui — isso é feito pelo UpdateExchangeRatesJob).
     *
     * @return array{currency: string, amount: float, rate: float|null, converted: bool}
     *
     * @throws CurrencyRateUnavailableException se a moeda não for AOA e não houver taxa em cache
     */
    public function convert(float $amountBaseAoa, string $targetCurrency): array
    {
        $base = config('currency.base_currency');

        if ($targetCurrency === $base) {
            return [
                'currency' => $base,
                'amount' => round($amountBaseAoa, 2),
                'rate' => null,
                'converted' => false,
            ];
        }

        $exchangeRate = ExchangeRate::forPair($base, $targetCurrency);

        if (! $exchangeRate) {
            throw CurrencyRateUnavailableException::forPair($base, $targetCurrency);
        }

        $rate = (float) $exchangeRate->rate;
        $decimals = $this->decimalsFor($targetCurrency);

        return [
            'currency' => $targetCurrency,
            'amount' => round($amountBaseAoa * $rate, $decimals),
            'rate' => $rate,
            'converted' => true,
        ];
    }

    /**
     * Atalho que junta resolveCurrencyForCountry() + convert() — é isso que o
     * componente de checkout vai chamar depois de saber o país (detectado ou
     * selecionado manualmente).
     *
     * @return array{currency: string, amount: float, rate: float|null, converted: bool}
     */
    public function convertForCountry(float $amountBaseAoa, Country $country): array
    {
        $currency = $this->resolveCurrencyForCountry($country);

        return $this->convert($amountBaseAoa, $currency);
    }

    protected function decimalsFor(string $currency): int
    {
        $zeroDecimal = config('currency.zero_decimal_currencies', []);

        return in_array($currency, $zeroDecimal, true) ? 0 : 2;
    }
}