<?php

namespace App\Domain\Decisions\Actions;

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Applications\Models\Application;
use App\Domain\Applications\Services\TransitionApplicationWorkflow;
use App\Domain\Decisions\Enums\DecisionType;
use App\Domain\Decisions\Models\Decision;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RecordApplicationDecision
{
    public function __construct(
        private readonly TransitionApplicationWorkflow $transitionApplicationWorkflow
    ) {}

    public function execute(
        Application $application,
        User $decisionMaker,
        DecisionType $type,
        string $reasonFr,
        string $reasonEn
    ): Decision {
        return DB::transaction(function () use ($application, $decisionMaker, $type, $reasonFr, $reasonEn) {
            $decision = Decision::create([
                'application_id' => $application->id,
                'decision_maker_id' => $decisionMaker->id,
                'decision_type' => $type,
                'reason_fr' => $reasonFr,
                'reason_en' => $reasonEn,
                'decided_at' => now(),
            ]);

            $targetStatus = match ($type) {
                DecisionType::Approved => ApplicationStatus::Approved,
                DecisionType::Rejected => ApplicationStatus::Rejected,
                DecisionType::Adjourned => ApplicationStatus::UnderReview,
            };

            $this->transitionApplicationWorkflow->execute(
                $application,
                $targetStatus,
                $decisionMaker,
                $type === DecisionType::Approved ? 'Dossier approuvé' : ($type === DecisionType::Rejected ? 'Dossier rejeté' : 'Dossier ajourné'),
                $type === DecisionType::Approved ? 'Application approved' : ($type === DecisionType::Rejected ? 'Application rejected' : 'Application adjourned'),
                ['decision_public_id' => $decision->public_id, 'type' => $type->value]
            );

            activity('decisions')
                ->causedBy($decisionMaker)
                ->performedOn($decision)
                ->event('decision.recorded')
                ->withProperties([
                    'application_public_id' => $application->public_id,
                    'decision_type' => $type->value,
                ])
                ->log("Decision {$type->value} recorded for application.");

            return $decision;
        });
    }
}
