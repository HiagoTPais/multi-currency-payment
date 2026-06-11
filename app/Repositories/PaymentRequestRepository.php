<?php

namespace App\Repositories;

use App\Enums\PaymentRequestStatus;
use App\Models\PaymentRequest;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PaymentRequestRepository
{
    public function paginatedForUser(User $user, ?PaymentRequestStatus $status = null): LengthAwarePaginator
    {
        return PaymentRequest::query()
            ->with(['user:id,name,email,currency_code', 'approver:id,name,email'])
            ->when(! $user->isFinance(), fn (Builder $query): Builder => $query->whereBelongsTo($user))
            ->status($status)
            ->latest()
            ->paginate(10);
    }
}
