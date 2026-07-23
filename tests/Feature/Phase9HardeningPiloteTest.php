<?php

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Applications\Models\Application;
use App\Domain\Appointments\Actions\BookAppointment;
use App\Domain\Appointments\Models\Location;
use App\Domain\Archiving\Actions\BuildApplicationArchivePackage;
use App\Domain\Catalog\Models\Service;
use App\Domain\Decisions\Actions\RecordApplicationDecision;
use App\Domain\Decisions\Enums\DecisionType;
use App\Domain\Deliveries\Actions\DeliverDocumentWithProof;
use App\Domain\Examinations\Actions\RecordExamResult;
use App\Domain\Examinations\Enums\ExamResult;
use App\Domain\Examinations\Models\ExamSession;
use App\Domain\Examinations\Models\ExamType;
use App\Domain\Organizations\Models\AgentAssignment;
use App\Domain\Payments\Actions\InitiatePaymentTransaction;
use App\Domain\Payments\Actions\IssueApplicationInvoice;
use App\Domain\Payments\Actions\ProcessPaymentCallback;
use App\Domain\Payments\Enums\PaymentProvider;
use App\Domain\Production\Actions\CompleteProductionAndIssueDocument;
use App\Domain\Production\Actions\CreateProductionOrder;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\CatalogDemoSeeder;
use Database\Seeders\PiloteDemoSeeder;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(AccessControlSeeder::class);
    $this->seed(CatalogDemoSeeder::class);
    Storage::fake('private');
});

it('returns health status 200 ok for system diagnostics', function () {
    $this->get(route('health'))
        ->assertOk()
        ->assertJson([
            'status' => 'ok',
            'checks' => [
                'database' => 'healthy',
                'storage' => 'healthy',
            ],
        ]);
});

it('seeds the pilote demonstration environment cleanly', function () {
    $this->seed(PiloteDemoSeeder::class);

    $this->assertDatabaseHas('users', [
        'email' => 'citoyen@appgov.cm',
    ]);

    $this->assertDatabaseHas('citizen_profiles', [
        'first_name' => 'Jean-Paul',
        'last_name' => 'Mbarga',
    ]);
});

it('executes full end-to-end application lifecycle from submission to sealed archive', function () {
    $service = Service::where('code', 'DEMO-DRIVING-LICENCE')->firstOrFail();
    $citizen = User::factory()->create();

    // 1. Citizen starts and submits application
    $this->actingAs($citizen)->post(route('account.services.applications.store', $service));
    $application = Application::firstOrFail();
    $application->update(['status' => ApplicationStatus::UnderReview, 'reference' => 'CM-PDC-2026-E2E001-Z']);

    // 2. Caseworker approves application
    $agent = User::factory()->agent()->create();
    $agent->assignRole('agent_caseworker');
    AgentAssignment::create([
        'user_id' => $agent->id,
        'organization_id' => $service->organization_id,
        'starts_at' => now()->subDay(),
        'is_active' => true,
    ]);

    app(RecordApplicationDecision::class)->execute(
        $application,
        $agent,
        DecisionType::Approved,
        'Dossier conforme E2E',
        'Compliant E2E'
    );
    expect($application->fresh()->status)->toBe(ApplicationStatus::Approved);

    // 3. Payment
    $invoice = app(IssueApplicationInvoice::class)->execute($application);
    $initResult = app(InitiatePaymentTransaction::class)->execute($invoice, PaymentProvider::LocalMock);
    $transaction = $initResult['transaction'];

    $payment = app(ProcessPaymentCallback::class)->execute($transaction->idempotency_key, 'successful');
    expect($invoice->fresh()->status->value)->toBe('paid');

    // 4. Appointment & Exam
    $location = Location::create([
        'organization_id' => $service->organization_id,
        'code' => 'LOC-E2E',
        'name_fr' => 'Centre E2E',
        'name_en' => 'Center E2E',
        'is_active' => true,
    ]);
    $slot = $location->slots()->create([
        'starts_at' => now()->addDay(),
        'ends_at' => now()->addDay()->addHour(),
        'max_capacity' => 10,
        'booked_count' => 0,
        'is_active' => true,
    ]);
    app(BookAppointment::class)->execute($application, $slot);

    $examType = ExamType::create(['code' => 'E2E_TYPE', 'name_fr' => 'Théorie', 'name_en' => 'Theory']);
    $session = ExamSession::create([
        'location_id' => $location->id,
        'exam_type_id' => $examType->id,
        'session_date' => now()->addDay(),
        'capacity' => 10,
        'is_active' => true,
    ]);
    app(RecordExamResult::class)->execute($application, $session, $agent, ExamResult::Passed, 90);

    // 5. Production & Delivery
    app(CreateProductionOrder::class)->execute($application, $agent);
    app(CompleteProductionAndIssueDocument::class)->execute($application, $agent);

    $delivery = $application->delivery;
    app(DeliverDocumentWithProof::class)->execute($delivery, $agent, 'Titulaire E2E', 'CNI-E2E-999');

    expect($application->fresh()->status)->toBe(ApplicationStatus::Closed);

    // 6. Sealed Archiving
    $package = app(BuildApplicationArchivePackage::class)->execute($application, $agent);
    expect($package->package_hash)->toHaveLength(64);
});
