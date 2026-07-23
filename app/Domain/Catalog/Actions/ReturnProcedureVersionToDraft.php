<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Enums\ProcedureVersionStatus;
use App\Domain\Catalog\Models\ProcedureVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReturnProcedureVersionToDraft
{
    public function execute(ProcedureVersion $version, User $actor, string $reason): ProcedureVersion
    {
        return DB::transaction(function () use ($actor, $reason, $version): ProcedureVersion {
            $locked = ProcedureVersion::query()->whereKey($version->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== ProcedureVersionStatus::UnderReview) {
                throw ValidationException::withMessages(['status' => __('catalog.errors.not_under_review')]);
            }

            $locked->returnToDraft($actor, $reason);

            activity('catalog')
                ->causedBy($actor)
                ->performedOn($locked)
                ->event('catalog.procedure.returned_to_draft')
                ->withProperties(['reason' => $reason])
                ->log('Procedure version returned to draft.');

            return $locked->fresh();
        });
    }
}
