<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Catalog\Enums\FormFieldType;
use App\Domain\Catalog\Models\Concerns\BelongsToImmutableProcedureVersion;
use App\Domain\Shared\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormField extends Model
{
    use BelongsToImmutableProcedureVersion;
    use HasPublicId;

    protected $fillable = [
        'procedure_version_id',
        'procedure_step_id',
        'code',
        'position',
        'field_type',
        'label_fr',
        'label_en',
        'help_fr',
        'help_en',
        'is_required',
        'configuration',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'field_type' => FormFieldType::class,
            'is_required' => 'boolean',
            'configuration' => 'array',
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
