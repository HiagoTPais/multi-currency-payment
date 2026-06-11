<?php

namespace App\Actions;

use App\Enums\PaymentRequestStatus;
use App\Models\PaymentRequest;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class RejectPaymentRequestAction
{
    public function execute(PaymentRequest $paymentRequest, User $rejector, ?string $notes = null): PaymentRequest
    {
        if (! $paymentRequest->isPending()) {
            throw ValidationException::withMessages([
                'status' => 'Only pending payment requests can be rejected.',
            ]);
        }

        $paymentRequest->forceFill([
            'status' => PaymentRequestStatus::Rejected,
            'approved_by' => $rejector->id,
            'approved_at' => now(),
            'notes' => $notes ?? $paymentRequest->notes,
        ])->save();

        return $paymentRequest->refresh()->load(['user:id,name,email,currency_code', 'approver:id,name,email']);
    }
}
