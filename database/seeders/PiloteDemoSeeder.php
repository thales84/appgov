<?php

namespace Database\Seeders;

use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Identity\Enums\AccountType;
use App\Domain\Identity\Models\CitizenProfile;
use App\Domain\Organizations\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PiloteDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(AccessControlSeeder::class);
        $this->call(CatalogDemoSeeder::class);

        // Citizen Accounts (French Republic)
        $citizenEmails = ['citoyen@appgov.fr', 'citoyen@appgov.cm', 'citizen.demo@appgov.cm'];
        foreach ($citizenEmails as $email) {
            $citizen = User::firstOrCreate(
                ['email' => $email],
                [
                    'public_id' => (string) Str::ulid(),
                    'name' => 'Jean Dupont',
                    'account_type' => AccountType::Citizen,
                    'status' => AccountStatus::Active,
                    'password' => 'Password123!',
                    'email_verified_at' => now(),
                ]
            );

            CitizenProfile::firstOrCreate(
                ['user_id' => $citizen->id],
                [
                    'first_name' => 'Jean',
                    'last_name' => 'Dupont',
                    'phone' => '+33612345678',
                    'preferred_locale' => 'fr',
                ]
            );
        }

        // Agent Accounts (ANTS / Ministry of Interior)
        $orgs = Organization::whereIn('code', ['MINTRANSPORT', 'DEMO-TRANSPORT-AUTHORITY'])->get();
        $agentEmails = ['agent@appgov.fr', 'agent@appgov.cm', 'agent.demo@appgov.cm'];

        foreach ($agentEmails as $email) {
            $agent = User::firstOrCreate(
                ['email' => $email],
                [
                    'public_id' => (string) Str::ulid(),
                    'name' => 'Instructeur ANTS — Préfecture de Police',
                    'account_type' => AccountType::Agent,
                    'status' => AccountStatus::Active,
                    'password' => 'Password123!',
                    'email_verified_at' => now(),
                    'two_factor_secret' => null,
                    'two_factor_recovery_codes' => null,
                    'two_factor_confirmed_at' => null,
                ]
            );

            $agent->assignRole('agent_caseworker');

            foreach ($orgs as $org) {
                $org->agentAssignments()->firstOrCreate(
                    ['user_id' => $agent->id],
                    [
                        'starts_at' => now()->subDays(30),
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
