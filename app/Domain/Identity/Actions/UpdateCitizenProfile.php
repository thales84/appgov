<?php

namespace App\Domain\Identity\Actions;

use App\Domain\Identity\Enums\IdentityLevel;
use App\Models\User;
use App\Notifications\EmailAddressChanged;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class UpdateCitizenProfile
{
    /**
     * @param  array{first_name: string, last_name: string, email: string, phone: string|null, preferred_locale: string}  $data
     */
    public function handle(User $user, array $data): void
    {
        $emailChanged = $data['email'] !== $user->email;
        $previousEmail = $user->email;

        DB::transaction(function () use ($user, $data, $emailChanged): void {
            $user->forceFill([
                'name' => trim($data['first_name'].' '.$data['last_name']),
                'email' => $data['email'],
                'email_verified_at' => $emailChanged ? null : $user->email_verified_at,
            ])->save();

            $level = match (true) {
                $emailChanged || ! $user->hasVerifiedEmail() => IdentityLevel::Unverified,
                filled($data['phone']) => IdentityLevel::IdentityDeclared,
                default => IdentityLevel::EmailVerified,
            };

            $user->citizenProfile()->updateOrCreate([], [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone'],
                'preferred_locale' => $data['preferred_locale'],
                'identity_level' => $level,
            ]);

            activity('identity')
                ->causedBy($user)
                ->performedOn($user)
                ->event('citizen_profile.updated')
                ->withProperties([
                    'email_changed' => $emailChanged,
                    'identity_level' => $level->value,
                ])
                ->log('citizen_profile.updated');
        });

        if ($emailChanged) {
            Notification::route('mail', $previousEmail)
                ->notify((new EmailAddressChanged)->locale($data['preferred_locale']));
            $user->sendEmailVerificationNotification();
        }

        if (request()->hasSession()) {
            request()->session()->regenerate();
        }
    }
}
