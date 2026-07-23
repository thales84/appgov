<?php

namespace App\Domain\Catalog\Policies;

use App\Domain\Catalog\Enums\ProcedureVersionStatus;
use App\Domain\Catalog\Models\ProcedureVersion;
use App\Models\User;

class ProcedureVersionPolicy
{
    public function view(User $user, ProcedureVersion $version): bool
    {
        return $user->can('catalog.view')
            && $this->isInScope($user, $version);
    }

    public function update(User $user, ProcedureVersion $version): bool
    {
        return $user->can('catalog.edit')
            && $version->status === ProcedureVersionStatus::Draft
            && $this->isInScope($user, $version);
    }

    public function submitForReview(User $user, ProcedureVersion $version): bool
    {
        return $user->can('catalog.submit_review')
            && $version->status === ProcedureVersionStatus::Draft
            && $this->isInScope($user, $version);
    }

    public function returnToDraft(User $user, ProcedureVersion $version): bool
    {
        return $user->can('catalog.publish')
            && $version->status === ProcedureVersionStatus::UnderReview
            && $version->created_by_user_id !== $user->id
            && $this->isInScope($user, $version);
    }

    public function publish(User $user, ProcedureVersion $version): bool
    {
        return $user->can('catalog.publish')
            && $version->status === ProcedureVersionStatus::UnderReview
            && $version->created_by_user_id !== $user->id
            && $this->isInScope($user, $version);
    }

    public function retire(User $user, ProcedureVersion $version): bool
    {
        return $user->can('catalog.retire')
            && $version->status === ProcedureVersionStatus::Published
            && $this->isInScope($user, $version);
    }

    private function isInScope(User $user, ProcedureVersion $version): bool
    {
        $version->loadMissing('service:id,organization_id');

        return $user->isAssignedToOrganization($version->service->organization_id);
    }
}
