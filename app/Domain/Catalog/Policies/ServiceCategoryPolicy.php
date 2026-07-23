<?php

namespace App\Domain\Catalog\Policies;

use App\Models\User;

class ServiceCategoryPolicy
{
    public function create(User $user): bool
    {
        return $user->can('catalog.categories.manage')
            && $user->hasActiveAgentAssignment();
    }
}
