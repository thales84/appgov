<?php

namespace App\Domain\Identity\Listeners;

class RecordTwoFactorSecurityEvent
{
    public function handle(object $event): void
    {
        $eventName = match (class_basename($event)) {
            'TwoFactorAuthenticationEnabled' => 'two_factor.enabled',
            'TwoFactorAuthenticationConfirmed' => 'two_factor.confirmed',
            'TwoFactorAuthenticationDisabled' => 'two_factor.disabled',
            'RecoveryCodesGenerated' => 'two_factor.recovery_codes_regenerated',
            default => 'two_factor.event',
        };

        activity('security')
            ->causedBy($event->user)
            ->performedOn($event->user)
            ->event($eventName)
            ->log($eventName);

        if ($eventName === 'two_factor.confirmed' && request()->hasSession()) {
            request()->session()->regenerate();
        }
    }
}
