<?php

namespace App\Domain\Applications\Services;

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Applications\Models\Application;
use App\Domain\Applications\Models\ApplicationEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use LogicException;

class TransitionApplicationWorkflow
{
    /**
     * Allowed transition map according to docs/04-workflows.md.
     */
    private const ALLOWED_TRANSITIONS = [
        'draft' => ['submitted'],
        'submitted' => ['under_review', 'cancelled'],
        'under_review' => ['correction_requested', 'processing', 'decision_pending', 'approved', 'rejected'],
        'correction_requested' => ['submitted', 'under_review', 'cancelled'],
        'processing' => ['decision_pending', 'correction_requested', 'approved', 'rejected'],
        'decision_pending' => ['approved', 'rejected', 'under_review'],
        'approved' => ['in_production'],
        'rejected' => ['closed'],
        'in_production' => ['available'],
        'available' => ['delivered'],
        'delivered' => ['closed'],
        'closed' => [],
    ];

    public function execute(
        Application $application,
        ApplicationStatus $targetStatus,
        User $actor,
        string $labelFr,
        string $labelEn,
        ?array $payload = null
    ): Application {
        $current = $application->status->value;
        $target = $targetStatus->value;

        if ($current === $target) {
            return $application;
        }

        $allowedTargets = self::ALLOWED_TRANSITIONS[$current] ?? [];
        if (! in_array($target, $allowedTargets)) {
            throw new LogicException("Transition from status '{$current}' to '{$target}' is not allowed.");
        }

        return DB::transaction(function () use ($application, $targetStatus, $actor, $labelFr, $labelEn, $payload) {
            $previousStatus = $application->status;

            $application->update([
                'status' => $targetStatus,
            ]);

            ApplicationEvent::create([
                'application_id' => $application->id,
                'user_id' => $actor->id,
                'event_type' => "status.{$targetStatus->value}",
                'label_fr' => $labelFr,
                'label_en' => $labelEn,
                'payload' => array_merge($payload ?? [], [
                    'from' => $previousStatus->value,
                    'to' => $targetStatus->value,
                ]),
            ]);

            activity('applications')
                ->causedBy($actor)
                ->performedOn($application)
                ->event('application.status_changed')
                ->withProperties([
                    'application_public_id' => $application->public_id,
                    'from' => $previousStatus->value,
                    'to' => $targetStatus->value,
                ])
                ->log("Application status transitioned from {$previousStatus->value} to {$targetStatus->value}.");

            return $application->fresh();
        });
    }
}
