<?php

namespace App\Domain\Applications\Actions;

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Applications\Models\Application;
use App\Domain\Applications\Models\ApplicationMessage;
use App\Domain\Applications\Services\TransitionApplicationWorkflow;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RequestApplicationCorrection
{
    public function __construct(
        private readonly TransitionApplicationWorkflow $transitionApplicationWorkflow
    ) {}

    public function execute(Application $application, User $agent, string $reason): Application
    {
        return DB::transaction(function () use ($application, $agent, $reason) {
            // Send public message explaining requested correction
            ApplicationMessage::create([
                'application_id' => $application->id,
                'sender_id' => $agent->id,
                'is_internal' => false,
                'message' => $reason,
            ]);

            // Transition application status
            return $this->transitionApplicationWorkflow->execute(
                $application,
                ApplicationStatus::CorrectionRequested,
                $agent,
                'Correction demandée par l\'instructeur',
                'Correction requested by caseworker',
                ['reason' => $reason]
            );
        });
    }
}
