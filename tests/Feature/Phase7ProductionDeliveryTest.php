<?php

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Applications\Models\Application;
use App\Domain\Catalog\Models\Service;
use App\Domain\Deliveries\Actions\DeliverDocumentWithProof;
use App\Domain\Deliveries\Enums\DeliveryStatus;
use App\Domain\Deliveries\Models\Delivery;
use App\Domain\Organizations\Models\AgentAssignment;
use App\Domain\Production\Actions\CompleteProductionAndIssueDocument;
use App\Domain\Production\Actions\CreateProductionOrder;
use App\Domain\Production\Enums\ProductionOrderStatus;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\CatalogDemoSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(AccessControlSeeder::class);
    $this->seed(CatalogDemoSeeder::class);
});

function createPhase7Agent(Service $service): User
{
    $agent = User::factory()->agent()->create();
    $agent->assignRole('agent_caseworker');
    $agent->forceFill([
        'two_factor_secret' => 'configured',
        'two_factor_recovery_codes' => 'configured',
        'two_factor_confirmed_at' => now(),
    ])->save();

    AgentAssignment::create([
        'user_id' => $agent->id,
        'organization_id' => $service->organization_id,
        'starts_at' => now()->subDay(),
        'is_active' => true,
    ]);

    return $agent;
}

it('creates a production order and transitions approved application to in_production', function () {
    $service = Service::where('code', 'DEMO-DRIVING-LICENCE')->firstOrFail();
    $citizen = User::factory()->create();
    $agent = createPhase7Agent($service);

    $this->actingAs($citizen)->post(route('account.services.applications.store', $service));
    $application = Application::firstOrFail();
    $application->update(['status' => ApplicationStatus::Approved]);

    $action = app(CreateProductionOrder::class);
    $order = $action->execute($application, $agent);

    expect($order->status)->toBe(ProductionOrderStatus::InProduction)
        ->and($application->fresh()->status)->toBe(ApplicationStatus::InProduction);

    $this->assertDatabaseHas('activity_log', [
        'event' => 'production.order_created',
    ]);
});

it('completes production with quality check, issues document number and transitions application to available', function () {
    $service = Service::where('code', 'DEMO-DRIVING-LICENCE')->firstOrFail();
    $citizen = User::factory()->create();
    $agent = createPhase7Agent($service);

    $this->actingAs($citizen)->post(route('account.services.applications.store', $service));
    $application = Application::firstOrFail();
    $application->update(['status' => ApplicationStatus::Approved]);

    app(CreateProductionOrder::class)->execute($application, $agent);

    $action = app(CompleteProductionAndIssueDocument::class);
    $issuedDoc = $action->execute($application, $agent, 'Impression et puce RFID vérifiées.');

    expect($application->fresh()->status)->toBe(ApplicationStatus::Available)
        ->and($issuedDoc->document_number)->toStartWith('CM-DOC-')
        ->and(Delivery::count())->toBe(1);

    $this->assertDatabaseHas('activity_log', [
        'event' => 'document.issued',
    ]);
});

it('delivers document with recipient proof of identity and closes the application', function () {
    $service = Service::where('code', 'DEMO-DRIVING-LICENCE')->firstOrFail();
    $citizen = User::factory()->create();
    $agent = createPhase7Agent($service);

    $this->actingAs($citizen)->post(route('account.services.applications.store', $service));
    $application = Application::firstOrFail();
    $application->update(['status' => ApplicationStatus::Approved]);

    app(CreateProductionOrder::class)->execute($application, $agent);
    app(CompleteProductionAndIssueDocument::class)->execute($application, $agent);

    $delivery = Delivery::firstOrFail();

    $action = app(DeliverDocumentWithProof::class);
    $proof = $action->execute($delivery, $agent, 'Jean Dupont', 'CNI-102938475', 'Conforme');

    expect($delivery->fresh()->status)->toBe(DeliveryStatus::Delivered)
        ->and($application->fresh()->status)->toBe(ApplicationStatus::Closed)
        ->and($proof->recipient_name)->toBe('Jean Dupont');

    $this->assertDatabaseHas('activity_log', [
        'event' => 'delivery.proof_recorded',
    ]);
});

it('allows authorized citizen to consult tracking timeline and forbids unauthorized citizens', function () {
    $owner = User::factory()->create();
    $attacker = User::factory()->create();
    $service = Service::where('code', 'DEMO-DRIVING-LICENCE')->firstOrFail();

    $this->actingAs($owner)->post(route('account.services.applications.store', $service));
    $application = Application::firstOrFail();

    // Attacker forbidden
    $this->actingAs($attacker)
        ->get(route('account.applications.tracking.show', $application))
        ->assertForbidden();

    // Owner allowed
    $this->actingAs($owner)
        ->get(route('account.applications.tracking.show', $application))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Account/Applications/TrackingShow')
            ->has('application.events')
        );
});
