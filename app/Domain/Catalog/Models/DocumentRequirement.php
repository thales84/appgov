<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Catalog\Models\Concerns\BelongsToImmutableProcedureVersion;
use App\Domain\Shared\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentRequirement extends Model
{
    use BelongsToImmutableProcedureVersion;
    use HasPublicId;

    protected $fillable = [
        'procedure_version_id',
        'procedure_step_id',
        'code',
        'position',
        'name_fr',
        'name_en',
        'description_fr',
        'description_en',
        'is_required',
        'condition_rules',
        'allowed_mime_types',
        'max_file_size_kb',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'is_required' => 'boolean',
            'condition_rules' => 'array',
            'allowed_mime_types' => 'array',
            'max_file_size_kb' => 'integer',
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
