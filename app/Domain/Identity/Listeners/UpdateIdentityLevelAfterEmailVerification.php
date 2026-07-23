<?php

namespace App\Domain\Identity\Listeners;

use App\Domain\Identity\Enums\IdentityLevel;
use Illuminate\Auth\Events\Verified;

class UpdateIdentityLevelAfterEmailVerification
{
    public function handle(Verified $event): void
    {
        $profile = $event->user->citizenProfile;

        if (! $profile) {
            return;
        }

        $profile->update([
            'identity_level' => filled($profile->phone)
                ? IdentityLevel::IdentityDeclared
                : IdentityLevel::EmailVerified,
        ]);

        activity('identity')
            ->causedBy($event->user)
            ->performedOn($event->user)
            ->event('email.verified')
            ->log('email.verified');
    }
}
