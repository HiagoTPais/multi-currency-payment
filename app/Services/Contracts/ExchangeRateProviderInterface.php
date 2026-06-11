<?php

namespace App\Services\Contracts;

use App\DTOs\ExchangeRateDTO;

interface ExchangeRateProviderInterface
{
    public function getEuroToLocalRate(string $currencyCode): ExchangeRateDTO;
}
