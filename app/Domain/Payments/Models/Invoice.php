<?php

namespace App\Domain\Payments\Models;

use App\Domain\Applications\Models\Application;
use App\Domain\Payments\Enums\InvoiceStatus;
use App\Domain\Shared\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invoice extends Model
{
    use HasPublicId;

    protected $fillable = [
        'application_id',
        'invoice_number',
        'status',
        'total_amount_minor',
        'currency',
        'due_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => InvoiceStatus::class,
            'total_amount_minor' => 'integer',
            'due_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class)->orderBy('created_at', 'desc');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}
