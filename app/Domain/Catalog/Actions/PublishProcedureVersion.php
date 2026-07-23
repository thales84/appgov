<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Enums\ProcedureVersionStatus;
use App\Domain\Catalog\Models\ProcedureVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PublishProcedureVersion
{
    public function execute(ProcedureVersion $version, User $actor): ProcedureVersion
    {
        return DB::transaction(function () use ($actor, $version): ProcedureVersion {
            $locked = ProcedureVersion::query()
                ->with('service')
                ->whereKey($version->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== ProcedureVersionStatus::UnderReview) {
                throw ValidationException::withMessages(['status' => __('catalog.errors.not_under_review')]);
            }

            $latestEffectiveDate = $locked->service
                ->procedureVersions()
                ->whereKeyNot($locked->id)
                ->whereIn('status', [
                    ProcedureVersionStatus::Published->value,
                    ProcedureVersionStatus::Retired->value,
                ])
                ->max('effective_from');

            if ($latestEffectiveDate && $locked->effective_from->lte($latestEffectiveDate)) {
                throw ValidationException::withMessages([
                    'effective_from' => __('catalog.errors.effective_date_order'),
                ]);
            }

            $locked->publish($actor);

            activity('catalog')
                ->causedBy($actor)
                ->performedOn($locked)
                ->event('catalog.procedure.published')
                ->withProperties([
                    'version_number' => $locked->version_number,
                    'effective_from' => $locked->effective_from->toIso8601String(),
                ])
                ->log('Procedure version published.');

            return $locked->fresh();
        });
    }
}
