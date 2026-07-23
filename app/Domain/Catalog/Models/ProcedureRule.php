<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Catalog\Enums\ProcedureRuleType;
use App\Domain\Catalog\Models\Concerns\BelongsToImmutableProcedureVersion;
use App\Domain\Shared\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcedureRule extends Model
{
    use BelongsToImmutableProcedureVersion;
    use HasPublicId;

    protected $fillable = [
        'procedure_version_id',
        'code',
        'position',
        'rule_type',
        'name_fr',
        'name_en',
        'description_fr',
        'description_en',
        'configuration',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'rule_type' => ProcedureRuleType::class,
            'configuration' => 'array',
        ];
    }

    public function procedureVersion(): BelongsTo
    {
        return $this->belongsTo(ProcedureVersion::class);
    }
}
