<?php

namespace App\Http\Controllers\Agent;

use App\Domain\Applications\Models\Application;
use App\Domain\Examinations\Actions\CorrectExamResult;
use App\Domain\Examinations\Actions\RecordExamResult;
use App\Domain\Examinations\Enums\ExamResult;
use App\Domain\Examinations\Models\ExamAttempt;
use App\Domain\Examinations\Models\ExamSession;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AgentExaminationController extends Controller
{
    public function index(Request $request): Response
    {
        $agent = $request->user();
        $assignment = $agent->activeAssignment();

        abort_unless($assignment, 403, 'No active organizational assignment.');

        $sessions = ExamSession::query()
            ->with(['location', 'examType', 'attempts.application.citizen', 'attempts.examiner'])
            ->whereHas('location', function ($q) use ($assignment) {
                $q->where('organization_id', $assignment->organization_id);
            })
            ->where('is_active', true)
            ->orderBy('session_date', 'desc')
            ->paginate(10);

        return Inertia::render('Agent/Examinations/Index', [
            'sessions' => $sessions->through(fn ($session) => [
                'publicId' => $session->public_id,
                'sessionDate' => $session->session_date->format('Y-m-d'),
                'locationName' => ['fr' => $session->location->name_fr, 'en' => $session->location->name_en],
                'examTypeName' => ['fr' => $session->examType->name_fr, 'en' => $session->examType->name_en],
                'attempts' => $session->attempts->map(fn ($att) => [
                    'publicId' => $att->public_id,
                    'applicationPublicId' => $att->application->public_id,
                    'reference' => $att->application->reference,
                    'citizenName' => $att->application->citizen->name,
                    'examinerName' => $att->examiner->name,
                    'attemptNumber' => $att->attempt_number,
                    'score' => $att->score,
                    'result' => $att->result->value,
                    'resultLabel' => $att->result->label(app()->getLocale()),
                    'previousResult' => $att->previous_result,
                    'notes' => $att->notes,
                    'recordedAt' => $att->recorded_at->toIso8601String(),
                ]),
            ]),
        ]);
    }

    public function recordResult(
        Request $request,
        Application $application,
        ExamSession $session,
        RecordExamResult $action,
    ): RedirectResponse {
        $agent = $request->user();
        $assignment = $agent->activeAssignment();

        abort_unless(
            $assignment && $session->location->organization_id === $assignment->organization_id,
            403
        );

        $request->validate([
            'result' => ['required', Rule::enum(ExamResult::class)],
            'score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $action->execute(
            $application,
            $session,
            $agent,
            ExamResult::from($request->input('result')),
            $request->input('score'),
            $request->input('notes')
        );

        return back()->with('status', 'exam.result_recorded');
    }

    public function correctResult(
        Request $request,
        ExamAttempt $attempt,
        CorrectExamResult $action,
    ): RedirectResponse {
        $agent = $request->user();
        $assignment = $agent->activeAssignment();

        abort_unless(
            $assignment && $attempt->session->location->organization_id === $assignment->organization_id,
            403
        );

        $request->validate([
            'result' => ['required', Rule::enum(ExamResult::class)],
            'score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $action->execute(
            $attempt,
            $agent,
            ExamResult::from($request->input('result')),
            $request->input('score'),
            $request->input('reason')
        );

        return back()->with('status', 'exam.result_corrected');
    }
}
