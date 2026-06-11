<?php

namespace App\Actions;

use App\Enums\PaymentRequestStatus;
use App\Models\PaymentRequest;

class ExpirePendingPaymentRequestsAction
{
    public function execute(): int
    {
        return PaymentRequest::query()
            ->where('status', PaymentRequestStatus::Pending->value)
            ->where('created_at', '<=', now()->subHours(48))
            ->update([
                'status' => PaymentRequestStatus::Expired->value,
            ]);
    }
}
