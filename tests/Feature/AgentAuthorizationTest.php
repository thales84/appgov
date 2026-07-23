<?php

use App\Domain\Organizations\Enums\OrganizationType;
use App\Domain\Organizations\Enums\TerritoryType;
use App\Domain\Organizations\Models\AgentAssignment;
use App\Domain\Organizations\Models\Organization;
use App\Domain\Organizations\Models\Territory;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Fortify;
use PragmaRX\Google2FA\Google2FA;

beforeEach(function () {
    $this->seed(AccessControlSeeder::class);
});

function createAssignedAgent(string $role = 'agent_caseworker'): User
{
    $agent = User::factory()->agent()->create();
    $agent->assignRole($role);

    $organization = Organization::create([
        'code' => 'DEMO-ORG-'.str()->random(6),
        'type' => OrganizationType::PublicAgency,
        'name_fr' => 'Organisme DEMO',
        'name_en' => 'DEMO agency',
        'is_active' => true,
    ]);

    $territory = Territory::create([
        'code' => 'DEMO-TERR-'.str()->random(6),
        'type' => TerritoryType::Region,
        'name_fr' => 'Territoire DEMO',
        'name_en' => 'DEMO territory',
        'is_active' => true,
    ]);

    AgentAssignment::create([
        'user_id' => $agent->id,
        'organization_id' => $organization->id,
        'territory_id' => $territory->id,
        'starts_at' => now()->subDay(),
        'is_active' => true,
    ]);

    return $agent;
}

it('forbids citizens from the agent workspace and agents from citizen accounts', function () {
    $citizen = User::factory()->create();
    $agent = createAssignedAgent();
    $agent->forceFill([
        'two_factor_secret' => 'configured',
        'two_factor_recovery_codes' => 'configured',
        'two_factor_confirmed_at' => now(),
    ])->save();

    $this->actingAs($citizen)->get(route('agent.dashboard'))->assertForbidden();
    $this->actingAs($agent)->get(route('account.dashboard'))->assertForbidden();
});

it('requires an active assignment and an authorised role', function () {
    $unassigned = User::factory()->agent()->create();
    $unassigned->assignRole('agent_caseworker');

    $this->actingAs($unassigned)
        ->get(route('agent.security'))
        ->assertForbidden();

    $assignedWithoutRole = createAssignedAgent();
    $assignedWithoutRole->syncRoles([]);

    $this->actingAs($assignedWithoutRole)
        ->get(route('agent.security'))
        ->assertForbidden();
});

it('forces two-factor activation before an agent can open the workspace', function () {
    $agent = createAssignedAgent();

    $this->actingAs($agent)
        ->get(route('agent.dashboard'))
        ->assertRedirect(route('agent.security'));

    $this->actingAs($agent)
        ->get(route('agent.security'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Agent/Security')
            ->where('twoFactor.confirmed', false)
        );
});

it('confirms two-factor activation with a valid TOTP code and audits it', function () {
    $agent = createAssignedAgent();
    app(EnableTwoFactorAuthentication::class)($agent);
    $secret = Fortify::currentEncrypter()->decrypt($agent->fresh()->two_factor_secret);
    $code = app(Google2FA::class)->getCurrentOtp($secret);

    $this->actingAs($agent)
        ->post(route('two-factor.confirm'), ['code' => $code])
        ->assertRedirect();

    expect($agent->fresh()->hasEnabledTwoFactorAuthentication())->toBeTrue();
    $this->assertDatabaseHas('activity_log', [
        'event' => 'two_factor.confirmed',
        'subject_id' => $agent->id,
    ]);
});

it('challenges an agent with a valid TOTP code at sign in', function () {
    $password = 'SecurePassword123';
    $agent = createAssignedAgent();
    $agent->forceFill(['password' => Hash::make($password)])->save();

    app(EnableTwoFactorAuthentication::class)($agent);
    $secret = Fortify::currentEncrypter()->decrypt($agent->fresh()->two_factor_secret);
    $agent->forceFill(['two_factor_confirmed_at' => now()])->save();

    $this->post(route('agent.login.store'), [
        'email' => $agent->email,
        'password' => $password,
        'portal' => 'agent',
    ])->assertRedirect(route('two-factor.login'));

    $this->assertGuest();
    expect(session('login.portal'))->toBe('agent');

    $freshCode = app(Google2FA::class)->getCurrentOtp($secret);
    $this->post(route('two-factor.login.store'), [
        'code' => $freshCode,
    ])->assertRedirect(route('agent.dashboard'));

    $this->assertAuthenticatedAs($agent);
    $this->get(route('agent.dashboard'))->assertOk();
});

it('allows only a platform administrator into the admin shell', function () {
    $caseworker = createAssignedAgent();
    $administrator = createAssignedAgent('platform_administrator');

    foreach ([$caseworker, $administrator] as $agent) {
        $agent->forceFill([
            'two_factor_secret' => 'configured',
            'two_factor_recovery_codes' => 'configured',
            'two_factor_confirmed_at' => now(),
        ])->save();
    }

    $this->actingAs($caseworker)->get(route('admin.dashboard'))->assertForbidden();
    $this->actingAs($administrator)->get(route('admin.dashboard'))->assertOk();
});

it('stores the organization and territory scope independently from roles', function () {
    $agent = createAssignedAgent('agent_examiner');
    $assignment = $agent->agentAssignments()->with(['organization', 'territory'])->firstOrFail();

    expect($agent->can('exams.record'))->toBeTrue()
        ->and($agent->can('applications.review'))->toBeFalse()
        ->and($assignment->organization->code)->toStartWith('DEMO-ORG-')
        ->and($assignment->territory->code)->toStartWith('DEMO-TERR-');
});

it('provisions an agent without exposing an initial password', function () {
    Notification::fake();
    $organization = Organization::create([
        'code' => 'DEMO-PROVISION',
        'type' => OrganizationType::PublicAgency,
        'name_fr' => 'Organisme DEMO',
        'name_en' => 'DEMO agency',
        'is_active' => true,
    ]);

    $this->artisan('appgov:provision-agent', [
        'name' => 'Agent DEMO',
        'email' => 'agent.demo@example.cm',
        'organization' => $organization->code,
        '--role' => ['agent_caseworker'],
    ])->assertSuccessful();

    $agent = User::where('email', 'agent.demo@example.cm')->firstOrFail();

    expect($agent->isAgent())->toBeTrue()
        ->and($agent->hasRole('agent_caseworker'))->toBeTrue()
        ->and($agent->hasActiveAgentAssignment())->toBeTrue();

    Notification::assertSentTo($agent, VerifyEmail::class);
    Notification::assertSentTo($agent, ResetPassword::class);
});
