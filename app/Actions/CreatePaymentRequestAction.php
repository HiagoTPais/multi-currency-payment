<?php

namespace App\Actions;

use App\Enums\PaymentRequestStatus;
use App\Models\PaymentRequest;
use App\Models\User;
use App\Services\ExchangeRateService;

class CreatePaymentRequestAction
{
    public function __construct(
        private readonly ExchangeRateService $exchangeRateService,
    ) {
    }

    public function execute(User $user, float $amountLocal, ?string $notes = null): PaymentRequest
    {
        $currencyCode = strtoupper($user->currency_code);
        $rate = $this->exchangeRateService->getEuroToLocalRate($currencyCode);

        return PaymentRequest::query()->create([
            'user_id' => $user->id,
            'amount_local' => $amountLocal,
            'currency_code' => $currencyCode,
            'exchange_rate' => $rate->rate,
            'exchange_rate_source' => $rate->source,
            'exchange_rate_timestamp' => $rate->timestamp,
            'amount_eur' => $this->exchangeRateService->convertLocalToEuro($amountLocal, $rate),
            'status' => PaymentRequestStatus::Pending,
            'expires_at' => now()->addHours(48),
            'notes' => $notes,
        ])->load(['user:id,name,email,currency_code', 'approver:id,name,email']);
    }
}
