<?php

namespace App\Domain\Applications\Actions;

use App\Domain\Applications\Enums\AssignmentStatus;
use App\Domain\Applications\Models\Application;
use App\Domain\Applications\Models\ApplicationAssignment;
use App\Domain\Organizations\Models\AdministrativeUnit;
use App\Domain\Organizations\Models\Organization;
use App\Domain\Organizations\Models\Territory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AssignApplication
{
    public function execute(
        Application $application,
        Organization $organization,
        ?AdministrativeUnit $unit,
        ?Territory $territory,
        ?User $assignedToAgent,
        User $assignedByAgent
    ): ApplicationAssignment {
        return DB::transaction(function () use ($application, $organization, $unit, $territory, $assignedToAgent, $assignedByAgent) {
            // Close active assignments for this application
            ApplicationAssignment::query()
                ->where('application_id', $application->id)
                ->where('status', AssignmentStatus::Active)
                ->update(['status' => AssignmentStatus::Transferred]);

            $assignment = ApplicationAssignment::create([
                'application_id' => $application->id,
                'organization_id' => $organization->id,
                'administrative_unit_id' => $unit?->id,
                'territory_id' => $territory?->id,
                'assigned_by_user_id' => $assignedByAgent->id,
                'assigned_to_user_id' => $assignedToAgent?->id,
                'status' => AssignmentStatus::Active,
                'assigned_at' => now(),
            ]);

            $application->update([
                'assigned_unit_id' => $unit?->id,
                'assigned_agent_id' => $assignedToAgent?->id,
            ]);

            activity('applications')
                ->causedBy($assignedByAgent)
                ->performedOn($application)
                ->event('application.assigned')
                ->withProperties([
                    'application_public_id' => $application->public_id,
                    'organization_id' => $organization->id,
                    'unit_id' => $unit?->id,
                    'agent_id' => $assignedToAgent?->id,
                ])
                ->log('Application assigned to organizational scope.');

            return $assignment;
        });
    }
}
