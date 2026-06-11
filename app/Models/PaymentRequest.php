<?php

namespace App\Models;

use App\Enums\PaymentRequestStatus;
use Database\Factories\PaymentRequestFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentRequest extends Model
{
    /** @use HasFactory<PaymentRequestFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'amount_local',
        'currency_code',
        'exchange_rate',
        'exchange_rate_source',
        'exchange_rate_timestamp',
        'amount_eur',
        'status',
        'approved_by',
        'approved_at',
        'expires_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount_local' => 'decimal:2',
            'exchange_rate' => 'decimal:8',
            'amount_eur' => 'decimal:2',
            'status' => PaymentRequestStatus::class,
            'exchange_rate_timestamp' => 'datetime',
            'approved_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeStatus(Builder $query, ?PaymentRequestStatus $status): Builder
    {
        return $status ? $query->where('status', $status->value) : $query;
    }

    public function isPending(): bool
    {
        return $this->status === PaymentRequestStatus::Pending;
    }
}
