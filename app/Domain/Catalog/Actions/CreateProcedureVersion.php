<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Enums\ProcedureVersionStatus;
use App\Domain\Catalog\Models\ProcedureVersion;
use App\Domain\Catalog\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateProcedureVersion
{
    public function execute(Service $service, User $actor): ProcedureVersion
    {
        return DB::transaction(function () use ($actor, $service): ProcedureVersion {
            $lockedService = Service::query()
                ->whereKey($service->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedService->procedureVersions()->whereIn('status', [
                ProcedureVersionStatus::Draft->value,
                ProcedureVersionStatus::UnderReview->value,
            ])->exists()) {
                throw ValidationException::withMessages([
                    'version' => __('catalog.errors.draft_already_exists'),
                ]);
            }

            $source = $lockedService->procedureVersions()
                ->with(['steps', 'formFields', 'documentRequirements', 'rules', 'feeSchedules'])
                ->whereIn('status', [
                    ProcedureVersionStatus::Published->value,
                    ProcedureVersionStatus::Retired->value,
                ])
                ->orderByDesc('version_number')
                ->firstOrFail();

            $version = $lockedService->procedureVersions()->create([
                'version_number' => $source->version_number + 1,
                'status' => ProcedureVersionStatus::Draft,
                'title_fr' => $source->title_fr,
                'title_en' => $source->title_en,
                'summary_fr' => $source->summary_fr,
                'summary_en' => $source->summary_en,
                'description_fr' => $source->description_fr,
                'description_en' => $source->description_en,
                'eligibility_fr' => $source->eligibility_fr,
                'eligibility_en' => $source->eligibility_en,
                'legal_basis_fr' => $source->legal_basis_fr,
                'legal_basis_en' => $source->legal_basis_en,
                'is_demo' => $source->is_demo,
                'effective_from' => null,
                'created_by_user_id' => $actor->id,
            ]);

            $stepMap = [];
            foreach ($source->steps as $sourceStep) {
                $step = $version->steps()->create($sourceStep->only([
                    'code',
                    'position',
                    'name_fr',
                    'name_en',
                    'description_fr',
                    'description_en',
                    'step_type',
                    'is_required',
                    'transition_rules',
                ]));
                $stepMap[$sourceStep->id] = $step->id;
            }

            foreach ($source->formFields as $field) {
                $version->formFields()->create([
                    ...$field->only([
                        'code',
                        'position',
                        'field_type',
                        'label_fr',
                        'label_en',
                        'help_fr',
                        'help_en',
                        'is_required',
                        'configuration',
                    ]),
                    'procedure_step_id' => $stepMap[$field->procedure_step_id],
                ]);
            }

            foreach ($source->documentRequirements as $document) {
                $version->documentRequirements()->create([
                    ...$document->only([
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
                    ]),
                    'procedure_step_id' => $document->procedure_step_id
                        ? $stepMap[$document->procedure_step_id]
                        : null,
                ]);
            }

            foreach ($source->rules as $rule) {
                $version->rules()->create($rule->only([
                    'code',
                    'position',
                    'rule_type',
                    'name_fr',
                    'name_en',
                    'description_fr',
                    'description_en',
                    'configuration',
                ]));
            }

            foreach ($source->feeSchedules as $fee) {
                $version->feeSchedules()->create([
                    ...$fee->only([
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
                    ]),
                    'procedure_step_id' => $fee->procedure_step_id
                        ? $stepMap[$fee->procedure_step_id]
                        : null,
                ]);
            }

            activity('catalog')
                ->causedBy($actor)
                ->performedOn($version)
                ->event('catalog.procedure.version_created')
                ->withProperties([
                    'source_version_public_id' => $source->public_id,
                    'version_number' => $version->version_number,
                ])
                ->log('New draft procedure version created.');

            return $version;
        });
    }
}
