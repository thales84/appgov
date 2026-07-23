<?php

use App\Domain\Applications\Models\Application;
use App\Domain\Appointments\Actions\BookAppointment;
use App\Domain\Appointments\Enums\AppointmentStatus;
use App\Domain\Appointments\Models\Location;
use App\Domain\Catalog\Models\Service;
use App\Domain\Examinations\Actions\CorrectExamResult;
use App\Domain\Examinations\Actions\RecordExamResult;
use App\Domain\Examinations\Enums\ExamResult;
use App\Domain\Examinations\Models\ExamSession;
use App\Domain\Examinations\Models\ExamType;
use App\Domain\Organizations\Models\AgentAssignment;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\CatalogDemoSeeder;

beforeEach(function () {
    $this->seed(AccessControlSeeder::class);
    $this->seed(CatalogDemoSeeder::class);
});

function createPhase6Agent(Location $location): User
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
        'organization_id' => $location->organization_id,
        'starts_at' => now()->subDay(),
        'is_active' => true,
    ]);

    return $agent;
}

it('allows a citizen to book an appointment slot and prevents overbooking when slot is full', function () {
    $citizen = User::factory()->create();
    $service = Service::where('code', 'DEMO-DRIVING-LICENCE')->firstOrFail();

    $this->actingAs($citizen)->post(route('account.services.applications.store', $service));
    $application = Application::firstOrFail();

    $location = Location::create([
        'organization_id' => $service->organization_id,
        'code' => 'LOC-PARIS-01',
        'name_fr' => 'Centre d\'Examen de Paris',
        'name_en' => 'Paris Exam Center',
        'daily_capacity' => 10,
        'is_active' => true,
    ]);

    $slot = $location->slots()->create([
        'starts_at' => now()->addDays(2),
        'ends_at' => now()->addDays(2)->addHours(2),
        'max_capacity' => 1,
        'booked_count' => 0,
        'is_active' => true,
    ]);

    // 1. First booking succeeds
    $action = app(BookAppointment::class);
    $appointment = $action->execute($application, $slot);

    expect($appointment->status)->toBe(AppointmentStatus::Scheduled)
        ->and($slot->fresh()->booked_count)->toBe(1);

    // 2. Second booking throws overbooking exception
    $otherApplication = Application::create([
        'citizen_id' => User::factory()->create()->id,
        'procedure_version_id' => $application->procedure_version_id,
        'status' => 'submitted',
        'reference' => 'FR-PDC-2026-TEST99-Z',
        'started_at' => now(),
    ]);

    expect(fn () => $action->execute($otherApplication, $slot))
        ->toThrow(LogicException::class, 'This appointment slot is fully booked.');
});

it('allows an examiner to record exam attempt results', function () {
    $service = Service::where('code', 'DEMO-DRIVING-LICENCE')->firstOrFail();
    $citizen = User::factory()->create();

    $this->actingAs($citizen)->post(route('account.services.applications.store', $service));
    $application = Application::firstOrFail();

    $location = Location::create([
        'organization_id' => $service->organization_id,
        'code' => 'LOC-LYON-01',
        'name_fr' => 'Centre d\'Examen de Lyon',
        'name_en' => 'Lyon Exam Center',
        'is_active' => true,
    ]);

    $examiner = createPhase6Agent($location);

    $examType = ExamType::create([
        'code' => 'THEORY',
        'name_fr' => 'Code de la route',
        'name_en' => 'Highway Code',
        'passing_score' => 70,
    ]);

    $examSession = ExamSession::create([
        'location_id' => $location->id,
        'exam_type_id' => $examType->id,
        'session_date' => now()->addDays(3),
        'capacity' => 20,
        'is_active' => true,
    ]);

    $action = app(RecordExamResult::class);
    $attempt = $action->execute($application, $examSession, $examiner, ExamResult::Passed, 85, 'Bonne maîtrise');

    expect($attempt->result)->toBe(ExamResult::Passed)
        ->and($attempt->score)->toBe(85)
        ->and($attempt->attempt_number)->toBe(1);

    $this->assertDatabaseHas('activity_log', [
        'event' => 'exam.result_recorded',
    ]);
});

it('executes an audited correction of an exam result saving previous result', function () {
    $service = Service::where('code', 'DEMO-DRIVING-LICENCE')->firstOrFail();
    $citizen = User::factory()->create();

    $this->actingAs($citizen)->post(route('account.services.applications.store', $service));
    $application = Application::firstOrFail();

    $location = Location::create([
        'organization_id' => $service->organization_id,
        'code' => 'LOC-MARSEILLE-01',
        'name_fr' => 'Centre d\'Examen de Marseille',
        'name_en' => 'Marseille Exam Center',
        'is_active' => true,
    ]);

    $supervisor = createPhase6Agent($location);

    $examType = ExamType::create([
        'code' => 'PRACTICE',
        'name_fr' => 'Conduite pratique',
        'name_en' => 'Practical Driving',
        'passing_score' => 60,
    ]);

    $examSession = ExamSession::create([
        'location_id' => $location->id,
        'exam_type_id' => $examType->id,
        'session_date' => now()->addDays(1),
        'capacity' => 15,
        'is_active' => true,
    ]);

    $attempt = app(RecordExamResult::class)->execute($application, $examSession, $supervisor, ExamResult::Failed, 45, 'Faute éliminatoire');

    // Correct result
    $corrected = app(CorrectExamResult::class)->execute(
        $attempt,
        $supervisor,
        ExamResult::Passed,
        75,
        'Erreur matérielle de transcription sur la grille d\'évaluation'
    );

    expect($corrected->result)->toBe(ExamResult::Passed)
        ->and($corrected->previous_result)->toBe('failed')
        ->and($corrected->score)->toBe(75);

    $this->assertDatabaseHas('activity_log', [
        'event' => 'exam.result_corrected',
    ]);
});
