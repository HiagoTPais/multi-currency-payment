<?php

namespace App\Http\Controllers\Api;

use App\Enums\PaymentRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\PaymentRequest;
use App\Services\ExchangeRateService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, ExchangeRateService $exchangeRateService): JsonResponse
    {
        $user = $request->user('api');
        $exchangeRate = $exchangeRateService->getEuroToLocalRate($user->currency_code);

        return response()->json([
            'data' => [
                ...collect(PaymentRequestStatus::cases())
                    ->mapWithKeys(
                        fn (PaymentRequestStatus $status): array => [
                            $status->value => PaymentRequest::query()
                                ->when(! $user->isFinance(), fn (Builder $query): Builder => $query->whereBelongsTo($user))
                                ->where('status', $status->value)
                                ->count(),
                        ],
                    )
                    ->all(),
                'exchange_rate' => [
                    'base_currency' => $exchangeRate->baseCurrency,
                    'target_currency' => $exchangeRate->targetCurrency,
                    'rate' => $exchangeRate->rate,
                    'source' => $exchangeRate->source,
                    'timestamp' => $exchangeRate->timestamp->toISOString(),
                ],
            ],
        ]);
    }
}
