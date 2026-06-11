<?php

namespace App\Policies;

use App\Models\PaymentRequest;
use App\Models\User;

class PaymentRequestPolicy
{
    public function view(User $user, PaymentRequest $paymentRequest): bool
    {
        return $user->isFinance() || $paymentRequest->user_id === $user->id;
    }

    public function approve(User $user, PaymentRequest $paymentRequest): bool
    {
        return $user->isFinance();
    }

    public function reject(User $user, PaymentRequest $paymentRequest): bool
    {
        return $user->isFinance();
    }
}
