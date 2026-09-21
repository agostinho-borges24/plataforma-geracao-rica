<?php

namespace App\Services\Payments;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PaypalClientService
{
    public function baseUrl(): string
    {
        return config('services.paypal.mode') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    /**
     * Token de acesso OAuth, com cache de ~50min (o PayPal emite por 9h,
     * mas cacheamos por menos tempo por segurança).
     */
    public function accessToken(): string
    {
        return Cache::remember('paypal_access_token', now()->addMinutes(50), function () {
            $response = Http::asForm()
                ->withBasicAuth(
                    config('services.paypal.client_id'),
                    config('services.paypal.client_secret'),
                )
                ->post($this->baseUrl().'/v1/oauth2/token', [
                    'grant_type' => 'client_credentials',
                ])
                ->throw();

            return $response->json('access_token');
        });
    }

    public function client(): PendingRequest
    {
        return Http::withToken($this->accessToken())->baseUrl($this->baseUrl());
    }
}