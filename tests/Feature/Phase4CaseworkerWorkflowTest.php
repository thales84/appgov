<?php

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Applications\Enums\DocumentStatus;
use App\Domain\Applications\Models\Application;
use App\Domain\Catalog\Models\DocumentRequirement;
use App\Domain\Catalog\Models\Service;
use App\Domain\Decisions\Enums\DecisionType;
use App\Domain\Organizations\Enums\OrganizationType;
use App\Domain\Organizations\Models\AgentAssignment;
use App\Domain\Organizations\Models\Organization;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\CatalogDemoSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(AccessControlSeeder::class);
    $this->seed(CatalogDemoSeeder::class);
    Storage::fake('private');
});

function createPhase4Agent(Organization $organization, string $role = 'agent_caseworker'): User
{
    $agent = User::factory()->agent()->create();
    $agent->assignRole($role);
    $agent->forceFill([
        'two_factor_secret' => 'configured',
        'two_factor_recovery_codes' => 'configured',
        'two_factor_confirmed_at' => now(),
    ])->save();

    AgentAssignment::create([
        'user_id' => $agent->id,
        'organization_id' => $organization->id,
        'starts_at' => now()->subDay(),
        'is_active' => true,
    ]);

    return $agent;
}

it('scopes the agent application queue strictly to active organization assignments', function () {
    $demoService = Service::where('code', 'DEMO-DRIVING-LICENCE')->firstOrFail();
    $demoOrg = $demoService->organization;

    $otherOrg = Organization::create([
        'code' => 'OTHER-ORG',
        'type' => OrganizationType::PublicAgency,
        'name_fr' => 'Autre Organisme',
        'name_en' => 'Other Agency',
        'is_active' => true,
    ]);

    $citizen = User::factory()->create();

    // Create application for Demo Org
    $this->actingAs($citizen)->post(route('account.services.applications.store', $demoService));
    $application = Application::firstOrFail();
    $application->update(['status' => ApplicationStatus::Submitted, 'reference' => 'CM-PDC-2026-TEST11-A']);

    // Agent for Demo Org
    $demoAgent = createPhase4Agent($demoOrg);
    // Agent for Other Org
    $otherAgent = createPhase4Agent($otherOrg);

    // Demo agent sees the application in queue
    $this->actingAs($demoAgent)
        ->get(route('agent.applications.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Agent/Applications/Index')
            ->has('applications.data', 1)
            ->where('applications.data.0.reference', 'CM-PDC-2026-TEST11-A')
        );

    // Other agent sees 0 applications
    $this->actingAs($otherAgent)
        ->get(route('agent.applications.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Agent/Applications/Index')
            ->has('applications.data', 0)
        );

    // Other agent cannot view details of Demo Org application
    $this->actingAs($otherAgent)
        ->get(route('agent.applications.show', $application))
        ->assertForbidden();
});

it('allows an agent to start review and review document compliance', function () {
    $service = Service::where('code', 'DEMO-DRIVING-LICENCE')->firstOrFail();
    $agent = createPhase4Agent($service->organization);
    $citizen = User::factory()->create();

    $this->actingAs($citizen)->post(route('account.services.applications.store', $service));
    $application = Application::firstOrFail();

    $requirement = DocumentRequirement::where('procedure_version_id', $application->procedure_version_id)->first();
    $file = UploadedFile::fake()->create('cni.pdf', 200, 'application/pdf');

    // Upload document while in draft mode
    $this->actingAs($citizen)->post(route('account.applications.documents.store', [$application, $requirement]), ['file' => $file]);
    $submittedDoc = $application->submittedDocuments()->firstOrFail();

    // Now mark application as submitted
    $application->update(['status' => ApplicationStatus::Submitted, 'reference' => 'CM-PDC-2026-TEST22-B']);

    // 1. Agent starts review
    $this->actingAs($agent)
        ->post(route('agent.applications.start-review', $application))
        ->assertRedirect();

    expect($application->fresh()->status)->toBe(ApplicationStatus::UnderReview);

    // 2. Agent marks document valid
    $this->actingAs($agent)
        ->post(route('agent.applications.documents.review', [$application, $submittedDoc]), [
            'is_valid' => true,
            'notes' => 'Document conforme et lisible',
        ])
        ->assertRedirect();

    expect($submittedDoc->fresh()->status)->toBe(DocumentStatus::Valid);

    $this->assertDatabaseHas('activity_log', [
        'event' => 'document.reviewed',
        'subject_id' => $submittedDoc->id,
    ]);
});

it('allows requesting a correction and sending message to citizen', function () {
    $service = Service::where('code', 'DEMO-DRIVING-LICENCE')->firstOrFail();
    $agent = createPhase4Agent($service->organization);
    $citizen = User::factory()->create();

    $this->actingAs($citizen)->post(route('account.services.applications.store', $service));
    $application = Application::firstOrFail();
    $application->update(['status' => ApplicationStatus::UnderReview, 'reference' => 'CM-PDC-2026-TEST33-C']);

    $this->actingAs($agent)
        ->post(route('agent.applications.correction.store', $application), [
            'reason' => 'Veuillez joindre une pièce d\'identité plus lisible.',
        ])
        ->assertRedirect();

    expect($application->fresh()->status)->toBe(ApplicationStatus::CorrectionRequested);

    $this->assertDatabaseHas('application_messages', [
        'application_id' => $application->id,
        'message' => 'Veuillez joindre une pièce d\'identité plus lisible.',
        'is_internal' => false,
    ]);
});

it('allows recording an approved decision and transitions application status', function () {
    $service = Service::where('code', 'DEMO-DRIVING-LICENCE')->firstOrFail();
    $agent = createPhase4Agent($service->organization);
    $citizen = User::factory()->create();

    $this->actingAs($citizen)->post(route('account.services.applications.store', $service));
    $application = Application::firstOrFail();
    $application->update(['status' => ApplicationStatus::UnderReview, 'reference' => 'CM-PDC-2026-TEST44-D']);

    $this->actingAs($agent)
        ->post(route('agent.applications.decisions.store', $application), [
            'decision_type' => DecisionType::Approved->value,
            'reason_fr' => 'Dossier conforme et complet.',
            'reason_en' => 'Application compliant and complete.',
        ])
        ->assertRedirect();

    expect($application->fresh()->status)->toBe(ApplicationStatus::Approved);

    $this->assertDatabaseHas('decisions', [
        'application_id' => $application->id,
        'decision_type' => DecisionType::Approved->value,
        'reason_fr' => 'Dossier conforme et complet.',
    ]);

    $this->assertDatabaseHas('activity_log', [
        'event' => 'decision.recorded',
    ]);
});
