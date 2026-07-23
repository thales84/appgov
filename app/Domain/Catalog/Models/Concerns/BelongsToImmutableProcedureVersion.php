<?php

namespace App\Domain\Catalog\Models\Concerns;

use App\Domain\Catalog\Enums\ProcedureVersionStatus;
use App\Domain\Catalog\Models\ProcedureVersion;
use LogicException;

trait BelongsToImmutableProcedureVersion
{
    protected static function bootBelongsToImmutableProcedureVersion(): void
    {
        static::creating(fn ($model) => $model->assertProcedureVersionIsEditable());
        static::updating(fn ($model) => $model->assertProcedureVersionIsEditable());
        static::deleting(fn ($model) => $model->assertProcedureVersionIsEditable());
    }

    private function assertProcedureVersionIsEditable(): void
    {
        $version = $this->relationLoaded('procedureVersion')
            ? $this->getRelation('procedureVersion')
            : ProcedureVersion::query()->find($this->procedure_version_id);

        if (! $version || $version->status !== ProcedureVersionStatus::Draft) {
            throw new LogicException('Only a draft procedure version may be edited.');
        }
    }
}
