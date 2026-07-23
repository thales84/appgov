<?php

use App\Domain\Analytics\Actions\LogAuditExport;
use App\Domain\Analytics\Services\GenerateOperationalReports;
use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Applications\Models\Application;
use App\Domain\Archiving\Actions\BuildApplicationArchivePackage;
use App\Domain\Catalog\Models\Service;
use App\Domain\Organizations\Models\AgentAssignment;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\CatalogDemoSeeder;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(AccessControlSeeder::class);
    $this->seed(CatalogDemoSeeder::class);
    Storage::fake('private');
});

function createPhase8Admin(Service $service): User
{
    $admin = User::factory()->agent()->create();
    $admin->assignRole('platform_administrator');
    $admin->forceFill([
        'two_factor_secret' => 'configured',
        'two_factor_recovery_codes' => 'configured',
        'two_factor_confirmed_at' => now(),
    ])->save();

    AgentAssignment::create([
        'user_id' => $admin->id,
        'organization_id' => $service->organization_id,
        'starts_at' => now()->subDay(),
        'is_active' => true,
    ]);

    return $admin;
}

it('builds and seals a complete archive package with SHA-256 checksum for closed applications', function () {
    $service = Service::where('code', 'DEMO-DRIVING-LICENCE')->firstOrFail();
    $citizen = User::factory()->create();
    $admin = createPhase8Admin($service);

    $this->actingAs($citizen)->post(route('account.services.applications.store', $service));
    $application = Application::firstOrFail();
    $application->update(['status' => ApplicationStatus::Closed]);

    $action = app(BuildApplicationArchivePackage::class);
    $package = $action->execute($application, $admin);

    expect($package->package_hash)->toHaveLength(64)
        ->and($package->manifest)->toHaveKey('application')
        ->and($package->manifest['application']['reference'])->toBe($application->reference);

    Storage::disk('private')->assertExists($package->storage_path);

    $this->assertDatabaseHas('activity_log', [
        'event' => 'archive.package_sealed',
    ]);
});

it('calculates operational KPIs and reports accurately', function () {
    $service = Service::where('code', 'DEMO-DRIVING-LICENCE')->firstOrFail();
    $citizen = User::factory()->create();

    $this->actingAs($citizen)->post(route('account.services.applications.store', $service));

    $reportService = app(GenerateOperationalReports::class);
    $kpis = $reportService->execute();

    expect($kpis['totalApplications'])->toBe(1)
        ->and($kpis['currency'])->toBe('EUR')
        ->and($kpis)->toHaveKey('statusCounts');
});

it('logs administrative data exports into audit_exports table', function () {
    $service = Service::where('code', 'DEMO-DRIVING-LICENCE')->firstOrFail();
    $admin = createPhase8Admin($service);

    $action = app(LogAuditExport::class);
    $export = $action->execute($admin, 'operational_kpis', ['range' => 'month']);

    expect($export->export_type)->toBe('operational_kpis')
        ->and($export->user_id)->toBe($admin->id);

    $this->assertDatabaseHas('audit_exports', [
        'export_type' => 'operational_kpis',
        'user_id' => $admin->id,
    ]);

    $this->assertDatabaseHas('activity_log', [
        'event' => 'analytics.export_logged',
    ]);
});

it('allows platform administrator to view report dashboard and export reports', function () {
    $service = Service::where('code', 'DEMO-DRIVING-LICENCE')->firstOrFail();
    $admin = createPhase8Admin($service);

    $this->actingAs($admin)
        ->get(route('admin.reports.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Reports/Index')
            ->has('reports.totalApplications')
        );

    $this->actingAs($admin)
        ->post(route('admin.reports.export'))
        ->assertOk();
});
