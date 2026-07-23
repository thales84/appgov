<?php

namespace App\Domain\Payments\Models;

use App\Domain\Payments\Enums\PaymentProvider;
use App\Domain\Payments\Enums\PaymentTransactionStatus;
use App\Domain\Shared\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PaymentTransaction extends Model
{
    use HasPublicId;

    protected $fillable = [
        'invoice_id',
        'provider_name',
        'provider_transaction_id',
        'idempotency_key',
        'status',
        'amount_minor',
        'currency',
        'raw_payload',
        'initiated_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'provider_name' => PaymentProvider::class,
            'status' => PaymentTransactionStatus::class,
            'amount_minor' => 'integer',
            'raw_payload' => 'array',
            'initiated_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}
