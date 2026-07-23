<?php

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Applications\Models\Application;
use App\Domain\Catalog\Models\Service;
use App\Models\User;
use Database\Seeders\CatalogDemoSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(CatalogDemoSeeder::class);
});

it('lists and searches only effective published procedures', function () {
    $this->get(route('services.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Public/Catalog/Index')
            ->has('services.data', 1)
            ->where('services.data.0.isDemo', true)
            ->where('services.data.0.code', 'DEMO-DRIVING-LICENCE')
        );

    $this->get(route('services.index', ['q' => 'permis']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('services.data', 1));

    $this->get(route('services.index', ['q' => 'naturalisation']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('services.data', 0));
});

it('shows the complete bilingual demo procedure without claiming official rules', function () {
    $service = Service::where('code', 'DEMO-DRIVING-LICENCE')->firstOrFail();

    $this->get(route('services.show', $service))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Public/Catalog/Show')
            ->where('service.isDemo', true)
            ->where('service.code', 'DEMO-DRIVING-LICENCE')
            ->has('service.procedure.steps')
            ->has('service.procedure.fields')
            ->has('service.procedure.documents')
            ->has('service.procedure.rules')
            ->has('service.procedure.fees')
        );
});

it('starts one idempotent draft for a verified citizen and published version', function () {
    $citizen = User::factory()->create();
    $service = Service::where('code', 'DEMO-DRIVING-LICENCE')->firstOrFail();

    $this->actingAs($citizen)
        ->get(route('account.services.start', $service))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Account/Applications/Start')
            ->where('service.code', 'DEMO-DRIVING-LICENCE')
        );

    $first = $this->actingAs($citizen)
        ->post(route('account.services.applications.store', $service))
        ->assertRedirect();

    $application = Application::firstOrFail();
    $first->assertRedirect(route('account.applications.show', $application));

    $this->actingAs($citizen)
        ->post(route('account.services.applications.store', $service))
        ->assertRedirect(route('account.applications.show', $application));

    expect(Application::count())->toBe(1)
        ->and($application->status)->toBe(ApplicationStatus::Draft)
        ->and($application->procedureVersion->is_demo)->toBeTrue();

    $this->assertDatabaseHas('activity_log', [
        'event' => 'application.started',
        'subject_id' => $application->id,
    ]);
});

it('protects a draft from another citizen', function () {
    $owner = User::factory()->create();
    $otherCitizen = User::factory()->create();
    $service = Service::where('code', 'DEMO-DRIVING-LICENCE')->firstOrFail();

    $this->actingAs($owner)
        ->post(route('account.services.applications.store', $service))
        ->assertRedirect();

    $application = Application::firstOrFail();

    $this->actingAs($otherCitizen)
        ->get(route('account.applications.show', $application))
        ->assertForbidden();
});

it('removes a retired current version from the public catalogue without falling back', function () {
    $service = Service::where('code', 'DEMO-DRIVING-LICENCE')->firstOrFail();
    $version = $service->procedureVersions()->firstOrFail();

    $version->forceFill([
        'status' => 'retired',
        'retired_at' => now(),
    ])->saveQuietly();

    $this->get(route('services.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('services.data', 0));

    $this->get(route('services.show', $service))->assertNotFound();
});
