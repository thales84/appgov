<?php

namespace App\Domain\Applications\Data;

use App\Domain\Applications\Models\Application;

class ApplicationViewData
{
    /**
     * @return array<string, mixed>
     */
    public static function fromApplication(Application $application): array
    {
        $version = $application->procedureVersion;

        return [
            'publicId' => $application->public_id,
            'status' => $application->status->value,
            'statusLabel' => $application->status->label(app()->getLocale()),
            'reference' => $application->reference,
            'formResponses' => $application->form_responses ?? [],
            'snapshot' => $application->snapshot,
            'startedAt' => $application->started_at->toIso8601String(),
            'submittedAt' => $application->submitted_at?->toIso8601String(),
            'procedure' => [
                'publicId' => $version->public_id,
                'versionNumber' => $version->version_number,
                'title' => ['fr' => $version->title_fr, 'en' => $version->title_en],
                'summary' => ['fr' => $version->summary_fr, 'en' => $version->summary_en],
                'description' => ['fr' => $version->description_fr, 'en' => $version->description_en],
                'isDemo' => $version->is_demo,
                'service' => [
                    'publicId' => $version->service->public_id,
                    'name' => ['fr' => $version->service->name_fr, 'en' => $version->service->name_en],
                    'organization' => [
                        'name' => ['fr' => $version->service->organization->name_fr ?? '', 'en' => $version->service->organization->name_en ?? ''],
                    ],
                ],
                'steps' => $version->steps->map(fn ($step) => [
                    'publicId' => $step->public_id,
                    'code' => $step->code,
                    'position' => $step->position,
                    'name' => ['fr' => $step->name_fr, 'en' => $step->name_en],
                    'description' => ['fr' => $step->description_fr, 'en' => $step->description_en],
                    'stepType' => $step->step_type,
                    'isRequired' => $step->is_required,
                ])->values()->all(),
                'formFields' => $version->formFields->map(fn ($field) => [
                    'publicId' => $field->public_id,
                    'stepPublicId' => $field->step?->public_id,
                    'code' => $field->code,
                    'position' => $field->position,
                    'fieldType' => $field->field_type,
                    'label' => ['fr' => $field->label_fr, 'en' => $field->label_en],
                    'help' => ['fr' => $field->help_fr, 'en' => $field->help_en],
                    'isRequired' => $field->is_required,
                    'configuration' => $field->configuration,
                ])->values()->all(),
                'documentRequirements' => $version->documentRequirements->map(fn ($req) => [
                    'publicId' => $req->public_id,
                    'code' => $req->code,
                    'position' => $req->position,
                    'name' => ['fr' => $req->name_fr, 'en' => $req->name_en],
                    'description' => ['fr' => $req->description_fr, 'en' => $req->description_en],
                    'isRequired' => $req->is_required,
                    'allowedMimeTypes' => $req->allowed_mime_types,
                    'maxFileSizeKb' => $req->max_file_size_kb,
                ])->values()->all(),
                'feeSchedules' => $version->feeSchedules->map(fn ($fee) => [
                    'publicId' => $fee->public_id,
                    'code' => $fee->code,
                    'label' => ['fr' => $fee->label_fr, 'en' => $fee->label_en],
                    'amountMinor' => $fee->amount_minor,
                    'currency' => $fee->currency,
                    'isMandatory' => $fee->is_mandatory,
                ])->values()->all(),
            ],
            'participants' => $application->participants->map(fn ($p) => [
                'publicId' => $p->public_id,
                'participantType' => $p->participant_type->value,
                'identityData' => $p->identity_data,
            ])->values()->all(),
            'submittedDocuments' => $application->submittedDocuments->map(fn ($doc) => [
                'publicId' => $doc->public_id,
                'requirementPublicId' => $doc->requirement->public_id ?? null,
                'originalFilename' => $doc->original_filename,
                'mimeType' => $doc->mime_type,
                'fileSizeBytes' => $doc->file_size_bytes,
                'fileHash' => $doc->file_hash,
                'status' => $doc->status->value,
                'statusLabel' => $doc->status->label(app()->getLocale()),
                'uploadedAt' => $doc->uploaded_at->toIso8601String(),
                'downloadUrl' => route('account.applications.documents.download', [$application, $doc]),
            ])->values()->all(),
            'events' => $application->events->map(fn ($e) => [
                'publicId' => $e->public_id,
                'eventType' => $e->event_type,
                'label' => ['fr' => $e->label_fr, 'en' => $e->label_en],
                'payload' => $e->payload,
                'createdAt' => $e->created_at->toIso8601String(),
            ])->values()->all(),
        ];
    }
}
