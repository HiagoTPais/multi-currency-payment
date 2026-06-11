<?php

namespace App\DTOs;

use Carbon\CarbonImmutable;

readonly class ExchangeRateDTO
{
    public function __construct(
        public string $baseCurrency,
        public string $targetCurrency,
        public float $rate,
        public string $source,
        public CarbonImmutable $timestamp,
    ) {
    }
}
