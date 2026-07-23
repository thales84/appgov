<?php

namespace App\Domain\Payments\Models;

use App\Domain\Catalog\Models\FeeSchedule;
use App\Domain\Shared\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceLine extends Model
{
    use HasPublicId;

    protected $fillable = [
        'invoice_id',
        'fee_schedule_id',
        'label_fr',
        'label_en',
        'amount_minor',
        'currency',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'amount_minor' => 'integer',
            'quantity' => 'integer',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function feeSchedule(): BelongsTo
    {
        return $this->belongsTo(FeeSchedule::class);
    }
}
