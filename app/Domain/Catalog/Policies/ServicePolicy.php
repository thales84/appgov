<?php

namespace App\Domain\Catalog\Policies;

use App\Domain\Catalog\Enums\ProcedureVersionStatus;
use App\Domain\Catalog\Models\Service;
use App\Models\User;

class ServicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('catalog.view') && $user->hasActiveAgentAssignment();
    }

    public function view(User $user, Service $service): bool
    {
        return $user->can('catalog.view')
            && $user->isAssignedToOrganization($service->organization_id);
    }

    public function create(User $user): bool
    {
        return $user->can('catalog.edit') && $user->hasActiveAgentAssignment();
    }

    public function createVersion(User $user, Service $service): bool
    {
        return $user->can('catalog.edit')
            && $user->isAssignedToOrganization($service->organization_id)
            && $service->procedureVersions()
                ->whereIn('status', [
                    ProcedureVersionStatus::Published->value,
                    ProcedureVersionStatus::Retired->value,
                ])
                ->exists()
            && ! $service->procedureVersions()
                ->whereIn('status', [
                    ProcedureVersionStatus::Draft->value,
                    ProcedureVersionStatus::UnderReview->value,
                ])
                ->exists();
    }
}
