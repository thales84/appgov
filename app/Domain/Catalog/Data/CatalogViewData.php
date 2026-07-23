<?php

namespace App\Domain\Catalog\Data;

use App\Domain\Catalog\Models\ProcedureVersion;
use App\Domain\Catalog\Models\Service;
use App\Models\User;

class CatalogViewData
{
    /**
     * @return array<string, mixed>
     */
    public static function publicServiceSummary(Service $service): array
    {
        $version = $service->currentProcedureVersion;

        return [
            'publicId' => $service->public_id,
            'code' => $service->code,
            'name' => ['fr' => $service->name_fr, 'en' => $service->name_en],
            'description' => ['fr' => $service->description_fr, 'en' => $service->description_en],
            'category' => [
                'publicId' => $service->category->public_id,
                'code' => $service->category->code,
                'name' => ['fr' => $service->category->name_fr, 'en' => $service->category->name_en],
                'colorKey' => $service->category->color_key->value,
            ],
            'organization' => [
                'publicId' => $service->organization->public_id,
                'name' => ['fr' => $service->organization->name_fr, 'en' => $service->organization->name_en],
            ],
            'isDemo' => $version->is_demo,
            'procedure' => [
                'publicId' => $version->public_id,
                'versionNumber' => $version->version_number,
                'title' => ['fr' => $version->title_fr, 'en' => $version->title_en],
                'summary' => ['fr' => $version->summary_fr, 'en' => $version->summary_en],
                'effectiveFrom' => $version->effective_from?->toIso8601String(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function publicServiceDetail(Service $service): array
    {
        $summary = self::publicServiceSummary($service);
        $version = $service->currentProcedureVersion;

        $summary['procedure'] = [
            ...$summary['procedure'],
            'description' => ['fr' => $version->description_fr, 'en' => $version->description_en],
            'eligibility' => ['fr' => $version->eligibility_fr, 'en' => $version->eligibility_en],
            'legalBasis' => ['fr' => $version->legal_basis_fr, 'en' => $version->legal_basis_en],
            'steps' => $version->steps->map(fn ($step) => [
                'publicId' => $step->public_id,
                'code' => $step->code,
                'position' => $step->position,
                'name' => ['fr' => $step->name_fr, 'en' => $step->name_en],
                'description' => ['fr' => $step->description_fr, 'en' => $step->description_en],
                'type' => $step->step_type->value,
                'isRequired' => $step->is_required,
            ])->values(),
            'fields' => $version->formFields->map(fn ($field) => [
                'publicId' => $field->public_id,
                'code' => $field->code,
                'stepCode' => $field->step->code,
                'label' => ['fr' => $field->label_fr, 'en' => $field->label_en],
                'help' => ['fr' => $field->help_fr, 'en' => $field->help_en],
                'type' => $field->field_type->value,
                'isRequired' => $field->is_required,
                'options' => $field->configuration['options'] ?? ['fr' => [], 'en' => []],
            ])->values(),
            'documents' => $version->documentRequirements->map(fn ($document) => [
                'publicId' => $document->public_id,
                'code' => $document->code,
                'stepCode' => $document->step?->code,
                'name' => ['fr' => $document->name_fr, 'en' => $document->name_en],
                'description' => ['fr' => $document->description_fr, 'en' => $document->description_en],
                'isRequired' => $document->is_required,
            ])->values(),
            'rules' => $version->rules->map(fn ($rule) => [
                'publicId' => $rule->public_id,
                'code' => $rule->code,
                'name' => ['fr' => $rule->name_fr, 'en' => $rule->name_en],
                'description' => ['fr' => $rule->description_fr, 'en' => $rule->description_en],
                'type' => $rule->rule_type->value,
            ])->values(),
            'fees' => $version->feeSchedules->map(fn ($fee) => [
                'publicId' => $fee->public_id,
                'code' => $fee->code,
                'stepCode' => $fee->step?->code,
                'label' => ['fr' => $fee->label_fr, 'en' => $fee->label_en],
                'description' => ['fr' => $fee->description_fr, 'en' => $fee->description_en],
                'amountMinor' => $fee->amount_minor,
                'currency' => $fee->currency,
                'minorUnitExponent' => $fee->minor_unit_exponent,
                'isMandatory' => $fee->is_mandatory,
                'dueWhen' => ['fr' => $fee->due_when_fr, 'en' => $fee->due_when_en],
                'legalBasis' => ['fr' => $fee->legal_basis_fr, 'en' => $fee->legal_basis_en],
            ])->values(),
        ];

        return $summary;
    }

    /**
     * @return array<string, mixed>
     */
    public static function adminServiceSummary(Service $service): array
    {
        $version = $service->latestProcedureVersion;

        return [
            'publicId' => $service->public_id,
            'code' => $service->code,
            'name' => ['fr' => $service->name_fr, 'en' => $service->name_en],
            'category' => [
                'name' => ['fr' => $service->category->name_fr, 'en' => $service->category->name_en],
                'colorKey' => $service->category->color_key->value,
            ],
            'organization' => [
                'publicId' => $service->organization->public_id,
                'name' => ['fr' => $service->organization->name_fr, 'en' => $service->organization->name_en],
            ],
            'isActive' => $service->is_active,
            'latestVersion' => $version ? [
                'publicId' => $version->public_id,
                'number' => $version->version_number,
                'status' => $version->status->value,
                'isDemo' => $version->is_demo,
            ] : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function versionEditor(ProcedureVersion $version, User $actor): array
    {
        return [
            'publicId' => $version->public_id,
            'versionNumber' => $version->version_number,
            'status' => $version->status->value,
            'title_fr' => $version->title_fr,
            'title_en' => $version->title_en,
            'summary_fr' => $version->summary_fr,
            'summary_en' => $version->summary_en,
            'description_fr' => $version->description_fr,
            'description_en' => $version->description_en,
            'eligibility_fr' => $version->eligibility_fr,
            'eligibility_en' => $version->eligibility_en,
            'legal_basis_fr' => $version->legal_basis_fr,
            'legal_basis_en' => $version->legal_basis_en,
            'effective_from' => $version->effective_from
                ? $version->effective_from->timezone(config('appgov.display_timezone'))->format('Y-m-d')
                : null,
            'is_demo' => $version->is_demo,
            'reviewNote' => $version->review_note,
            'service' => [
                'publicId' => $version->service->public_id,
                'code' => $version->service->code,
                'name' => ['fr' => $version->service->name_fr, 'en' => $version->service->name_en],
                'category' => [
                    'name' => ['fr' => $version->service->category->name_fr, 'en' => $version->service->category->name_en],
                    'colorKey' => $version->service->category->color_key->value,
                ],
                'organization' => [
                    'name' => ['fr' => $version->service->organization->name_fr, 'en' => $version->service->organization->name_en],
                ],
                'versions' => $version->service->procedureVersions
                    ->sortByDesc('version_number')
                    ->map(fn ($item) => [
                        'publicId' => $item->public_id,
                        'number' => $item->version_number,
                        'status' => $item->status->value,
                    ])->values(),
            ],
            'steps' => $version->steps->map(fn ($step) => [
                'public_id' => $step->public_id,
                'code' => $step->code,
                'name_fr' => $step->name_fr,
                'name_en' => $step->name_en,
                'description_fr' => $step->description_fr,
                'description_en' => $step->description_en,
                'step_type' => $step->step_type->value,
                'is_required' => $step->is_required,
            ])->values(),
            'fields' => $version->formFields->map(fn ($field) => [
                'public_id' => $field->public_id,
                'step_code' => $field->step->code,
                'code' => $field->code,
                'label_fr' => $field->label_fr,
                'label_en' => $field->label_en,
                'help_fr' => $field->help_fr,
                'help_en' => $field->help_en,
                'field_type' => $field->field_type->value,
                'is_required' => $field->is_required,
                'options_fr' => implode("\n", $field->configuration['options']['fr'] ?? []),
                'options_en' => implode("\n", $field->configuration['options']['en'] ?? []),
            ])->values(),
            'documents' => $version->documentRequirements->map(fn ($document) => [
                'public_id' => $document->public_id,
                'step_code' => $document->step?->code,
                'code' => $document->code,
                'name_fr' => $document->name_fr,
                'name_en' => $document->name_en,
                'description_fr' => $document->description_fr,
                'description_en' => $document->description_en,
                'is_required' => $document->is_required,
            ])->values(),
            'rules' => $version->rules->map(fn ($rule) => [
                'public_id' => $rule->public_id,
                'code' => $rule->code,
                'name_fr' => $rule->name_fr,
                'name_en' => $rule->name_en,
                'description_fr' => $rule->description_fr,
                'description_en' => $rule->description_en,
                'rule_type' => $rule->rule_type->value,
            ])->values(),
            'fees' => $version->feeSchedules->map(fn ($fee) => [
                'public_id' => $fee->public_id,
                'step_code' => $fee->step?->code,
                'code' => $fee->code,
                'label_fr' => $fee->label_fr,
                'label_en' => $fee->label_en,
                'description_fr' => $fee->description_fr,
                'description_en' => $fee->description_en,
                'amount_minor' => $fee->amount_minor,
                'currency' => $fee->currency,
                'minor_unit_exponent' => $fee->minor_unit_exponent,
                'is_mandatory' => $fee->is_mandatory,
                'due_when_fr' => $fee->due_when_fr,
                'due_when_en' => $fee->due_when_en,
                'legal_basis_fr' => $fee->legal_basis_fr,
                'legal_basis_en' => $fee->legal_basis_en,
            ])->values(),
            'permissions' => [
                'update' => $actor->can('update', $version),
                'submitForReview' => $actor->can('submitForReview', $version),
                'returnToDraft' => $actor->can('returnToDraft', $version),
                'publish' => $actor->can('publish', $version),
                'retire' => $actor->can('retire', $version),
                'createVersion' => $actor->can('createVersion', $version->service),
            ],
        ];
    }
}
