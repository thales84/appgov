<?php

namespace App\Domain\Applications\Actions;

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Applications\Enums\DocumentStatus;
use App\Domain\Applications\Models\Application;
use App\Domain\Applications\Models\ApplicationEvent;
use App\Domain\Applications\Services\GenerateApplicationReference;
use App\Domain\Applications\Services\ValidateFormResponses;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use LogicException;

class SubmitApplication
{
    public function __construct(
        private readonly ValidateFormResponses $validateFormResponses,
        private readonly GenerateApplicationReference $generateApplicationReference,
        private readonly GenerateSubmissionReceiptPdf $generateSubmissionReceiptPdf
    ) {}

    public function execute(Application $application): Application
    {
        if (! in_array($application->status, [ApplicationStatus::Draft, ApplicationStatus::CorrectionRequested])) {
            throw new LogicException('Only draft applications or applications with requested corrections can be submitted.');
        }

        $version = $application->procedureVersion()->with(['formFields', 'documentRequirements', 'steps', 'feeSchedules'])->firstOrFail();

        // 1. Validate Form Fields
        $responses = $application->form_responses ?? [];
        $this->validateFormResponses->execute($version, $responses, isSubmitting: true);

        // 2. Validate Document Requirements
        $submittedDocReqIds = $application->submittedDocuments()
            ->whereIn('status', [DocumentStatus::Pending, DocumentStatus::Valid])
            ->pluck('document_requirement_id')
            ->all();

        $missingRequirements = [];
        foreach ($version->documentRequirements as $req) {
            if ($req->is_required && ! in_array($req->id, $submittedDocReqIds)) {
                $missingRequirements[] = $req->name_fr;
            }
        }

        if (! empty($missingRequirements)) {
            throw ValidationException::withMessages([
                'documents' => [__('The following required documents are missing: :list', ['list' => implode(', ', $missingRequirements)])],
            ]);
        }

        return DB::transaction(function () use ($application, $version) {
            // Generate Reference
            $reference = $this->generateApplicationReference->execute($application);

            // Create Procedure Snapshot
            $snapshot = [
                'procedure_title_fr' => $version->title_fr,
                'procedure_title_en' => $version->title_en,
                'version_number' => $version->version_number,
                'steps' => $version->steps->map(fn ($step) => [
                    'code' => $step->code,
                    'position' => $step->position,
                    'name_fr' => $step->name_fr,
                    'name_en' => $step->name_en,
                ])->all(),
                'fees' => $version->feeSchedules->map(fn ($fee) => [
                    'code' => $fee->code,
                    'label_fr' => $fee->label_fr,
                    'amount_minor' => $fee->amount_minor,
                    'currency' => $fee->currency,
                ])->all(),
            ];

            $application->update([
                'status' => ApplicationStatus::Submitted,
                'reference' => $reference,
                'draft_key' => null, // Clear draft_key on submission
                'snapshot' => $snapshot,
                'submitted_at' => now(),
            ]);

            // Event log
            ApplicationEvent::create([
                'application_id' => $application->id,
                'user_id' => $application->citizen_id,
                'event_type' => 'application.submitted',
                'label_fr' => 'Dossier déposé',
                'label_en' => 'Application submitted',
                'payload' => [
                    'reference' => $reference,
                    'submitted_at' => $application->submitted_at->toIso8601String(),
                ],
            ]);

            activity('applications')
                ->causedBy($application->citizen)
                ->performedOn($application)
                ->event('application.submitted')
                ->withProperties([
                    'application_public_id' => $application->public_id,
                    'reference' => $reference,
                ])
                ->log('Citizen submitted application.');

            // Generate Receipt PDF
            $this->generateSubmissionReceiptPdf->execute($application);

            return $application->fresh(['submittedDocuments.requirement', 'events', 'procedureVersion']);
        });
    }
}
