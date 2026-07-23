<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Catalog\Models\Concerns\BelongsToImmutableProcedureVersion;
use App\Domain\Shared\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeSchedule extends Model
{
    use BelongsToImmutableProcedureVersion;
    use HasPublicId;

    protected $fillable = [
        'procedure_version_id',
        'procedure_step_id',
        'code',
        'position',
        'label_fr',
        'label_en',
        'description_fr',
        'description_en',
        'amount_minor',
        'currency',
        'minor_unit_exponent',
        'is_mandatory',
        'due_when_fr',
        'due_when_en',
        'legal_basis_fr',
        'legal_basis_en',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'amount_minor' => 'integer',
            'minor_unit_exponent' => 'integer',
            'is_mandatory' => 'boolean',
        ];
    }

    public function procedureVersion(): BelongsTo
    {
        return $this->belongsTo(ProcedureVersion::class);
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(ProcedureStep::class, 'procedure_step_id');
    }
}
