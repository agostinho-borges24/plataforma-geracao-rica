<?php

use App\Jobs\UpdateExchangeRatesJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Atualiza as taxas de câmbio a cada hora. O free tier da ExchangeRate-API
// permite 1.500 req/mês, então de hora em hora (~720/mês) sobra bastante margem.
Schedule::job(new UpdateExchangeRatesJob)->hourly();