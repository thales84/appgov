<?php

namespace App\Domain\Identity\Actions;

use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Identity\Enums\AccountType;
use App\Domain\Organizations\Models\AdministrativeUnit;
use App\Domain\Organizations\Models\Organization;
use App\Domain\Organizations\Models\Territory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProvisionAgent
{
    /**
     * @param  list<string>  $roles
     */
    public function handle(
        string $name,
        string $email,
        Organization $organization,
        ?AdministrativeUnit $unit,
        ?Territory $territory,
        array $roles
    ): User {
        return DB::transaction(function () use ($name, $email, $organization, $unit, $territory, $roles): User {
            $user = User::create([
                'name' => $name,
                'email' => Str::lower($email),
                'account_type' => AccountType::Agent,
                'status' => AccountStatus::Active,
                'password' => Hash::make(Str::random(64)),
            ]);

            $user->syncRoles($roles);
            $user->agentAssignments()->create([
                'organization_id' => $organization->id,
                'administrative_unit_id' => $unit?->id,
                'territory_id' => $territory?->id,
                'starts_at' => now(),
                'is_active' => true,
            ]);

            activity('identity')
                ->performedOn($user)
                ->event('agent.provisioned')
                ->withProperties([
                    'organization_public_id' => $organization->public_id,
                    'unit_public_id' => $unit?->public_id,
                    'territory_public_id' => $territory?->public_id,
                    'roles' => $roles,
                ])
                ->log('agent.provisioned');

            return $user;
        });
    }
}
