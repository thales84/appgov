<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Enums\ProcedureVersionStatus;
use App\Domain\Catalog\Models\ProcedureVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RetireProcedureVersion
{
    public function execute(ProcedureVersion $version, User $actor, string $reason): ProcedureVersion
    {
        return DB::transaction(function () use ($actor, $reason, $version): ProcedureVersion {
            $locked = ProcedureVersion::query()->whereKey($version->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== ProcedureVersionStatus::Published) {
                throw ValidationException::withMessages(['status' => __('catalog.errors.not_published')]);
            }

            $locked->retire($actor);

            activity('catalog')
                ->causedBy($actor)
                ->performedOn($locked)
                ->event('catalog.procedure.retired')
                ->withProperties(['reason' => $reason])
                ->log('Procedure version retired.');

            return $locked->fresh();
        });
    }
}
