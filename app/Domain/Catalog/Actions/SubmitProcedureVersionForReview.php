<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Enums\FormFieldType;
use App\Domain\Catalog\Enums\ProcedureVersionStatus;
use App\Domain\Catalog\Models\ProcedureVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubmitProcedureVersionForReview
{
    public function execute(ProcedureVersion $version, User $actor): ProcedureVersion
    {
        return DB::transaction(function () use ($actor, $version): ProcedureVersion {
            $locked = ProcedureVersion::query()->whereKey($version->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== ProcedureVersionStatus::Draft) {
                throw ValidationException::withMessages(['status' => __('catalog.errors.not_draft')]);
            }

            $hasIncompleteChoiceField = $locked->formFields()
                ->get()
                ->contains(function ($field): bool {
                    if (! in_array($field->field_type, [FormFieldType::Select, FormFieldType::Radio], true)) {
                        return false;
                    }

                    return empty($field->configuration['options']['fr'])
                        || empty($field->configuration['options']['en']);
                });

            if (! $locked->effective_from || ! $locked->steps()->exists() || $hasIncompleteChoiceField) {
                throw ValidationException::withMessages([
                    'definition' => __('catalog.errors.incomplete_definition'),
                ]);
            }

            $locked->submitForReview($actor);

            activity('catalog')
                ->causedBy($actor)
                ->performedOn($locked)
                ->event('catalog.procedure.submitted_for_review')
                ->log('Procedure version submitted for independent review.');

            return $locked->fresh();
        });
    }
}
