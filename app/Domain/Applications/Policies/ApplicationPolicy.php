<?php

namespace App\Domain\Applications\Policies;

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Applications\Models\Application;
use App\Domain\Applications\Models\SubmittedDocument;
use App\Models\User;

class ApplicationPolicy
{
    public function view(User $user, Application $application): bool
    {
        if ($user->isCitizen() && $application->citizen_id === $user->id) {
            return true;
        }

        if ($user->isAgent()) {
            return $user->hasPermissionTo('applications.view');
        }

        return false;
    }

    public function update(User $user, Application $application): bool
    {
        return $user->isCitizen()
            && $application->citizen_id === $user->id
            && in_array($application->status, [ApplicationStatus::Draft, ApplicationStatus::CorrectionRequested]);
    }

    public function submit(User $user, Application $application): bool
    {
        return $this->update($user, $application);
    }

    public function downloadDocument(User $user, Application $application, SubmittedDocument $document): bool
    {
        if ($document->application_id !== $application->id) {
            return false;
        }

        return $this->view($user, $application);
    }
}
