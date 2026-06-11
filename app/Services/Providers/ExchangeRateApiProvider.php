<?php

namespace App\Services\Providers;

use App\DTOs\ExchangeRateDTO;
use App\Exceptions\ExchangeRateUnavailableException;
use App\Services\Contracts\ExchangeRateProviderInterface;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Throwable;

class ExchangeRateApiProvider implements ExchangeRateProviderInterface
{
    public function __construct(
        private readonly string $baseUrl = '',
        private readonly string $sourceName = 'ExchangeRate API',
    ) {
    }

    public function getEuroToLocalRate(string $currencyCode): ExchangeRateDTO
    {
        $currencyCode = strtoupper($currencyCode);

        if ($currencyCode === 'EUR') {
            return new ExchangeRateDTO('EUR', 'EUR', 1.0, $this->sourceName, CarbonImmutable::now());
        }

        try {
            $response = Http::timeout(5)
                ->acceptJson()
                ->get($this->endpoint());

            $response->throw();

            $rate = data_get($response->json(), "rates.$currencyCode");

            if (! is_numeric($rate) || (float) $rate <= 0) {
                throw new ExchangeRateUnavailableException("Exchange rate for {$currencyCode} was not returned.");
            }

            return new ExchangeRateDTO(
                baseCurrency: 'EUR',
                targetCurrency: $currencyCode,
                rate: (float) $rate,
                source: $this->sourceName,
                timestamp: CarbonImmutable::now(),
            );
        } catch (ExchangeRateUnavailableException $exception) {
            throw $exception;
        } catch (ConnectionException|RequestException $exception) {
            throw new ExchangeRateUnavailableException('Exchange rate provider is unavailable.', previous: $exception);
        } catch (Throwable $exception) {
            throw new ExchangeRateUnavailableException('Exchange rate response could not be processed.', previous: $exception);
        }
    }

    private function endpoint(): string
    {
        return rtrim($this->baseUrl ?: config('services.exchange_rate_api.base_url'), '/').'/latest/EUR';
    }
}
