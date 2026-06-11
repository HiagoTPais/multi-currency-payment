<?php

namespace App\Actions;

use App\Enums\PaymentRequestStatus;
use App\Models\PaymentRequest;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ApprovePaymentRequestAction
{
    public function execute(PaymentRequest $paymentRequest, User $approver): PaymentRequest
    {
        if (! $paymentRequest->isPending()) {
            throw ValidationException::withMessages([
                'status' => 'Only pending payment requests can be approved.',
            ]);
        }

        $paymentRequest->forceFill([
            'status' => PaymentRequestStatus::Approved,
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ])->save();

        return $paymentRequest->refresh()->load(['user:id,name,email,currency_code', 'approver:id,name,email']);
    }
}
