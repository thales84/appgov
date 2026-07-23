<?php

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Applications\Enums\DocumentStatus;
use App\Domain\Applications\Models\Application;
use App\Domain\Catalog\Models\DocumentRequirement;
use App\Domain\Catalog\Models\Service;
use App\Models\User;
use Database\Seeders\CatalogDemoSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(CatalogDemoSeeder::class);
    Storage::fake('private');
});

it('allows a citizen to update application draft form responses', function () {
    $citizen = User::factory()->create();
    $service = Service::where('code', 'DEMO-DRIVING-LICENCE')->firstOrFail();

    $this->actingAs($citizen)
        ->post(route('account.services.applications.store', $service));

    $application = Application::firstOrFail();

    $this->actingAs($citizen)
        ->put(route('account.applications.update', $application), [
            'responses' => [
                'REQUESTED_CATEGORY' => 'B',
            ],
        ])
        ->assertRedirect();

    $application->refresh();
    expect($application->form_responses['REQUESTED_CATEGORY'])->toBe('B');

    $this->assertDatabaseHas('activity_log', [
        'event' => 'application.draft_updated',
        'subject_id' => $application->id,
    ]);
});

it('allows uploading a document for a required document specification on private storage', function () {
    $citizen = User::factory()->create();
    $service = Service::where('code', 'DEMO-DRIVING-LICENCE')->firstOrFail();

    $this->actingAs($citizen)
        ->post(route('account.services.applications.store', $service));

    $application = Application::firstOrFail();
    $requirement = DocumentRequirement::where('procedure_version_id', $application->procedure_version_id)->firstOrFail();

    $file = UploadedFile::fake()->create('cni_scan.pdf', 500, 'application/pdf');

    $this->actingAs($citizen)
        ->post(route('account.applications.documents.store', [$application, $requirement]), [
            'file' => $file,
        ])
        ->assertRedirect();

    $submittedDoc = $application->submittedDocuments()->firstOrFail();

    expect($submittedDoc->original_filename)->toBe('cni_scan.pdf')
        ->and($submittedDoc->mime_type)->toBe('application/pdf')
        ->and($submittedDoc->disk)->toBe('private')
        ->and($submittedDoc->status)->toBe(DocumentStatus::Pending);

    Storage::disk('private')->assertExists($submittedDoc->file_path);

    $this->assertDatabaseHas('activity_log', [
        'event' => 'document.uploaded',
        'subject_id' => $submittedDoc->id,
    ]);
});

it('allows submitting an application when all required documents and form fields are completed', function () {
    $citizen = User::factory()->create();
    $service = Service::where('code', 'DEMO-DRIVING-LICENCE')->firstOrFail();

    $this->actingAs($citizen)
        ->post(route('account.services.applications.store', $service));

    $application = Application::firstOrFail();

    // 1. Fill draft form with correct code
    $this->actingAs($citizen)
        ->put(route('account.applications.update', $application), [
            'responses' => [
                'REQUESTED_CATEGORY' => 'B',
            ],
        ]);

    // 2. Upload all required documents
    $requirements = DocumentRequirement::where('procedure_version_id', $application->procedure_version_id)
        ->where('is_required', true)
        ->get();

    foreach ($requirements as $req) {
        $file = UploadedFile::fake()->create("doc_{$req->id}.pdf", 100, 'application/pdf');
        $this->actingAs($citizen)
            ->post(route('account.applications.documents.store', [$application, $req]), [
                'file' => $file,
            ]);
    }

    // 3. Submit Application
    $this->actingAs($citizen)
        ->post(route('account.applications.submit', $application))
        ->assertRedirect(route('account.applications.show', $application));

    $application->refresh();

    expect($application->status)->toBe(ApplicationStatus::Submitted)
        ->and($application->reference)->toStartWith('CM-')
        ->and($application->draft_key)->toBeNull()
        ->and($application->submitted_at)->not->toBeNull()
        ->and($application->snapshot)->not->toBeEmpty();

    // PDF receipt asserts
    Storage::disk('private')->assertExists("documents/{$application->public_id}/receipt_{$application->reference}.pdf");

    // Event and audit asserts
    $this->assertDatabaseHas('application_events', [
        'application_id' => $application->id,
        'event_type' => 'application.submitted',
    ]);

    $this->assertDatabaseHas('activity_log', [
        'event' => 'application.submitted',
        'subject_id' => $application->id,
    ]);
});

it('forbids unauthorized citizens from updating or downloading documents of another citizen application', function () {
    $owner = User::factory()->create();
    $attacker = User::factory()->create();
    $service = Service::where('code', 'DEMO-DRIVING-LICENCE')->firstOrFail();

    $this->actingAs($owner)
        ->post(route('account.services.applications.store', $service));

    $application = Application::firstOrFail();
    $requirement = DocumentRequirement::where('procedure_version_id', $application->procedure_version_id)->first();

    // Attacker tries to update draft
    $this->actingAs($attacker)
        ->put(route('account.applications.update', $application), ['responses' => []])
        ->assertForbidden();

    // Attacker tries to upload doc
    $file = UploadedFile::fake()->create('hack.pdf', 100, 'application/pdf');
    $this->actingAs($attacker)
        ->post(route('account.applications.documents.store', [$application, $requirement]), ['file' => $file])
        ->assertForbidden();

    // Owner uploads doc
    $this->actingAs($owner)
        ->post(route('account.applications.documents.store', [$application, $requirement]), ['file' => $file]);

    $doc = $application->submittedDocuments()->firstOrFail();

    // Attacker tries to download owner doc
    $this->actingAs($attacker)
        ->get(route('account.applications.documents.download', [$application, $doc]))
        ->assertForbidden();

    // Owner can download owner doc
    $this->actingAs($owner)
        ->get(route('account.applications.documents.download', [$application, $doc]))
        ->assertOk();
});
