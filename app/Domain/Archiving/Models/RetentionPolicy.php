<?php

namespace App\Domain\Archiving\Models;

use App\Domain\Organizations\Models\Organization;
use App\Domain\Shared\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetentionPolicy extends Model
{
    use HasPublicId;

    protected $fillable = [
        'organization_id',
        'category',
        'retention_years',
        'legal_basis',
    ];

    protected function casts(): array
    {
        return [
            'retention_years' => 'integer',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
