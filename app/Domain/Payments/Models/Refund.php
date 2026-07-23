<?php

namespace App\Domain\Payments\Models;

use App\Domain\Shared\Concerns\HasPublicId;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    use HasPublicId;

    protected $fillable = [
        'payment_id',
        'authorized_by_user_id',
        'amount_minor',
        'currency',
        'reason',
        'refunded_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_minor' => 'integer',
            'refunded_at' => 'datetime',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function authorizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorized_by_user_id');
    }
}
