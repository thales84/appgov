<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Catalog\Enums\ProcedureStepType;
use App\Domain\Catalog\Models\Concerns\BelongsToImmutableProcedureVersion;
use App\Domain\Shared\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProcedureStep extends Model
{
    use BelongsToImmutableProcedureVersion;
    use HasPublicId;

    protected $fillable = [
        'procedure_version_id',
        'code',
        'position',
        'name_fr',
        'name_en',
        'description_fr',
        'description_en',
        'step_type',
        'is_required',
        'transition_rules',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'step_type' => ProcedureStepType::class,
            'is_required' => 'boolean',
            'transition_rules' => 'array',
        ];
    }

    public function procedureVersion(): BelongsTo
    {
        return $this->belongsTo(ProcedureVersion::class);
    }

    public function formFields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('position');
    }
}
