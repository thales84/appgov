<?php

namespace App\Domain\Production\Models;

use App\Domain\Applications\Models\Application;
use App\Domain\Deliveries\Models\Delivery;
use App\Domain\Shared\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class IssuedDocument extends Model
{
    use HasPublicId;

    protected $fillable = [
        'application_id',
        'document_number',
        'document_type',
        'issued_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function delivery(): HasOne
    {
        return $this->hasOne(Delivery::class);
    }
}
