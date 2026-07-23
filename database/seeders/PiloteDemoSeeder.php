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

        // Create Demo Citizen
        $citizen = User::firstOrCreate(
            ['email' => 'citizen.demo@appgov.cm'],
            [
                'public_id' => (string) Str::ulid(),
                'name' => 'Jean-Paul DEMO (Usager Pilote)',
                'account_type' => AccountType::Citizen,
                'status' => AccountStatus::Active,
                'password' => 'Password123!',
                'email_verified_at' => now(),
            ]
        );

        CitizenProfile::firstOrCreate(
            ['user_id' => $citizen->id],
            [
                'first_name' => 'Jean-Paul',
                'last_name' => 'DEMO',
                'phone' => '+237690000000',
                'preferred_locale' => 'fr',
            ]
        );

        // Create Demo Agent
        $org = Organization::where('code', 'MINTRANSPORT')->first();
        if ($org) {
            $agent = User::firstOrCreate(
                ['email' => 'agent.demo@appgov.cm'],
                [
                    'public_id' => (string) Str::ulid(),
                    'name' => 'Instructeur DEMO',
                    'account_type' => AccountType::Agent,
                    'status' => AccountStatus::Active,
                    'password' => 'Password123!',
                    'email_verified_at' => now(),
                    'two_factor_secret' => 'configured',
                    'two_factor_recovery_codes' => 'configured',
                    'two_factor_confirmed_at' => now(),
                ]
            );

            $agent->assignRole('agent_caseworker');

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
