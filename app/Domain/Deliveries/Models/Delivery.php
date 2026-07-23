<?php

namespace App\Domain\Deliveries\Models;

use App\Domain\Applications\Models\Application;
use App\Domain\Appointments\Models\Location;
use App\Domain\Deliveries\Enums\DeliveryStatus;
use App\Domain\Production\Models\IssuedDocument;
use App\Domain\Shared\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Delivery extends Model
{
    use HasPublicId;

    protected $fillable = [
        'application_id',
        'issued_document_id',
        'location_id',
        'status',
        'dispatched_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => DeliveryStatus::class,
            'dispatched_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function issuedDocument(): BelongsTo
    {
        return $this->belongsTo(IssuedDocument::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function proof(): HasOne
    {
        return $this->hasOne(DeliveryProof::class);
    }
}
