<?php

namespace App\Console\Commands;

use App\Domain\Identity\Actions\ProvisionAgent as ProvisionAgentAction;
use App\Domain\Organizations\Models\AdministrativeUnit;
use App\Domain\Organizations\Models\Organization;
use App\Domain\Organizations\Models\Territory;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class ProvisionAgent extends Command
{
    protected $signature = 'appgov:provision-agent
        {name : Agent display name}
        {email : Agent professional email}
        {organization : Existing organization code}
        {--unit= : Existing administrative unit code}
        {--territory= : Existing territory code}
        {--role=* : One or more existing agent role names}';

    protected $description = 'Provision an agent assignment and send secure account activation messages';

    public function handle(ProvisionAgentAction $action): int
    {
        $roles = array_values(array_unique($this->option('role')));
        $email = Str::lower(trim($this->argument('email')));

        $validator = Validator::make([
            'name' => $this->argument('name'),
            'email' => $email,
            'organization' => $this->argument('organization'),
            'roles' => $roles,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class)],
            'organization' => ['required', Rule::exists(Organization::class, 'code')->where('is_active', true)],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => [Rule::exists(Role::class, 'name')->where('guard_name', 'web')],
        ]);

        if ($validator->fails()) {
            $this->components->error($validator->errors()->first());

            return self::FAILURE;
        }

        $organization = Organization::where('code', $this->argument('organization'))->firstOrFail();
        $unit = $this->resolveUnit($organization);
        $territory = $this->resolveTerritory();

        if ($unit === false || $territory === false) {
            return self::FAILURE;
        }

        $user = $action->handle(
            $this->argument('name'),
            $email,
            $organization,
            $unit,
            $territory,
            $roles
        );

        $user->sendEmailVerificationNotification();
        Password::broker()->sendResetLink(['email' => $user->email]);

        $this->components->info('Agent provisioned. Verification and password setup messages were queued.');

        return self::SUCCESS;
    }

    private function resolveUnit(Organization $organization): AdministrativeUnit|false|null
    {
        if (! $this->option('unit')) {
            return null;
        }

        $unit = AdministrativeUnit::query()
            ->whereBelongsTo($organization)
            ->where('code', $this->option('unit'))
            ->where('is_active', true)
            ->first();

        if (! $unit) {
            $this->components->error('The active administrative unit was not found in this organization.');

            return false;
        }

        return $unit;
    }

    private function resolveTerritory(): Territory|false|null
    {
        if (! $this->option('territory')) {
            return null;
        }

        $territory = Territory::query()
            ->where('code', $this->option('territory'))
            ->where('is_active', true)
            ->first();

        if (! $territory) {
            $this->components->error('The active territory was not found.');

            return false;
        }

        return $territory;
    }
}
