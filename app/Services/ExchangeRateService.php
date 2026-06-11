<?php

namespace App\Services;

use App\DTOs\ExchangeRateDTO;
use App\Services\Contracts\ExchangeRateProviderInterface;

class ExchangeRateService
{
    public function __construct(
        private readonly ExchangeRateProviderInterface $provider,
    ) {
    }

    public function getEuroToLocalRate(string $currencyCode): ExchangeRateDTO
    {
        $currencyCode = strtoupper($currencyCode);

        return $this->provider->getEuroToLocalRate($currencyCode);
    }

    public function convertLocalToEuro(float $amountLocal, ExchangeRateDTO $rate): float
    {
        return round($amountLocal / $rate->rate, 2);
    }
}
