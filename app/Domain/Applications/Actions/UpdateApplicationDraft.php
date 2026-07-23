<?php

namespace App\Domain\Applications\Actions;

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Applications\Models\Application;
use App\Domain\Applications\Services\ValidateFormResponses;
use Illuminate\Support\Facades\DB;
use LogicException;

class UpdateApplicationDraft
{
    public function __construct(
        private readonly ValidateFormResponses $validateFormResponses
    ) {}

    public function execute(Application $application, array $responses, ?array $participantData = null): Application
    {
        if ($application->status !== ApplicationStatus::Draft) {
            throw new LogicException('Only draft applications can be updated.');
        }

        $validatedResponses = $this->validateFormResponses->execute(
            $application->procedureVersion,
            $responses,
            isSubmitting: false
        );

        return DB::transaction(function () use ($application, $validatedResponses, $participantData) {
            $application->update([
                'form_responses' => array_merge($application->form_responses ?? [], $validatedResponses),
            ]);

            if ($participantData !== null) {
                $application->participants()->updateOrCreate(
                    ['participant_type' => 'applicant'],
                    [
                        'user_id' => $application->citizen_id,
                        'identity_data' => $participantData,
                    ]
                );
            }

            activity('applications')
                ->causedBy($application->citizen)
                ->performedOn($application)
                ->event('application.draft_updated')
                ->withProperties([
                    'application_public_id' => $application->public_id,
                ])
                ->log('Citizen updated draft form responses.');

            return $application->fresh(['participants', 'submittedDocuments']);
        });
    }
}
