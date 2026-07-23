<?php

namespace App\Domain\Payments\Models;

use App\Domain\Shared\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasPublicId;

    protected $fillable = [
        'invoice_id',
        'payment_transaction_id',
        'payment_reference',
        'amount_minor',
        'currency',
        'reconciled_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_minor' => 'integer',
            'reconciled_at' => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'payment_transaction_id');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }
}
